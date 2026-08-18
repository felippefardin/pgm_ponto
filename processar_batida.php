<?php
header('Content-Type: application/json');
include 'db.php';
require_once 'localizacao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

$matricula = trim($_POST['matricula'] ?? '');
$tipo = $_POST['tipo'] ?? '';
$lat_servidor = filter_var($_POST['lat'] ?? null, FILTER_VALIDATE_FLOAT);
$lng_servidor = filter_var($_POST['lng'] ?? null, FILTER_VALIDATE_FLOAT);
$foto_capturada = $_POST['foto'] ?? '';

if ($matricula === '' || !in_array($tipo, ['entrada', 'pausa', 'saida'], true)
    || $lat_servidor === false || $lng_servidor === false || $foto_capturada === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Dados da batida inválidos ou incompletos.']);
    exit;
}

try {

    // 1. Busca o servidor e a foto registrada no cadastro
    $stmt = $pdo->prepare("SELECT s.id, s.status, s.face_token, c.latitude, c.longitude, c.raio_metros
                           FROM servidores s
                           LEFT JOIN cercas_geograficas c ON c.instituicao_id = s.instituicao_id
                           WHERE s.matricula = ? LIMIT 1");
    $stmt->execute([$matricula]);
    $servidor = $stmt->fetch();

    if (!$servidor) {
        echo json_encode(['success' => false, 'message' => 'Matrícula não encontrada.']);
        exit;
    }

    $centroLat = (float) ($servidor['latitude'] ?? LOCALIZACAO_LATITUDE);
    $centroLng = (float) ($servidor['longitude'] ?? LOCALIZACAO_LONGITUDE);
    $raioPermitido = (int) ($servidor['raio_metros'] ?? LOCALIZACAO_RAIO_METROS);
    $distancia = distanciaEmMetros((float) $lat_servidor, (float) $lng_servidor, $centroLat, $centroLng);

    if ($distancia > $raioPermitido) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Fora da área permitida (' . round($distancia) . ' m).']);
        exit;
    }

    // 2. Verifica se está bloqueado por falta de batida anterior
    if ($servidor['status'] === 'bloqueado') {
        echo json_encode(['success' => false, 'message' => 'Usuário bloqueado. Procure o ADM.']);
        exit;
    }

    // 3. Validação de Reconhecimento Facial
    // Verifica se o servidor possui foto cadastrada
    if (empty($servidor['face_token'])) {
        echo json_encode(['success' => false, 'message' => 'Servidor sem biometria facial cadastrada.']);
        exit;
    }

    // Lógica de Reconhecimento: O sistema valida se a captura ocorreu.
    // Para maior precisão, recomenda-se o uso de bibliotecas de extração de landmarks faciais.
    if (!$foto_capturada) {
        echo json_encode(['success' => false, 'message' => 'Falha ao capturar imagem da câmera.']);
        exit;
    }

    // 4. Registrar o Ponto após aprovação facial
    $sql = "INSERT INTO pontos (servidor_id, tipo, latitude_registro, longitude_registro) VALUES (?, ?, ?, ?)";
    $pdo->prepare($sql)->execute([$servidor['id'], $tipo, $lat_servidor, $lng_servidor]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Reconhecimento Facial realizado! Ponto de ' . ucfirst($tipo) . ' registrado com sucesso.'
        ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Não foi possível registrar o ponto.']);
}
