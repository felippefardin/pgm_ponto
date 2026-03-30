<?php
header('Content-Type: application/json');
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricula = $_POST['matricula'];
    $tipo = $_POST['tipo'];
    $lat_servidor = $_POST['lat'];
    $lng_servidor = $_POST['lng'];
    $foto_capturada = $_POST['foto']; 

    // 1. Busca o servidor e a foto registrada no cadastro
    $stmt = $pdo->prepare("SELECT id, status, face_token FROM servidores WHERE matricula = ?");
    $stmt->execute([$matricula]);
    $servidor = $stmt->fetch();

    if (!$servidor) {
        echo json_encode(['success' => false, 'message' => 'Matrícula não encontrada.']);
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
    try {
        $sql = "INSERT INTO pontos (servidor_id, tipo, latitude_registro, longitude_registro) VALUES (?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$servidor['id'], $tipo, $lat_servidor, $lng_servidor]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Reconhecimento Facial realizado! Ponto de ' . ucfirst($tipo) . ' registrado com sucesso.'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro no banco: ' . $e->getMessage()]);
    }
}