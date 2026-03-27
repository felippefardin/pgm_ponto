<?php
header('Content-Type: application/json');
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricula = $_POST['matricula'];
    $pin_digitado = $_POST['pin'];
    $tipo = $_POST['tipo'];
    $lat_servidor = $_POST['lat'];
    $lng_servidor = $_POST['lng'];
    $foto_atual = $_POST['foto']; // Foto tirada na hora (Base64)

    // 1. Buscar o servidor no banco
    $stmt = $pdo->prepare("SELECT * FROM servidores WHERE matricula = ?");
    $stmt->execute([$matricula]);
    $servidor = $stmt->fetch();

    if (!$servidor) {
        echo json_encode(['success' => false, 'message' => 'Matrícula não encontrada.']);
        exit;
    }

    // 2. Verificar se o servidor está bloqueado
    if ($servidor['status'] === 'bloqueado') {
        echo json_encode(['success' => false, 'message' => 'Você está bloqueado! Procure o ADM para liberar seu ponto.']);
        exit;
    }

    // 3. Validar o PIN
    if ($pin_digitado !== $servidor['pin']) {
        echo json_encode(['success' => false, 'message' => 'PIN incorreto.']);
        exit;
    }

    // 4. Lógica de Pausa Obrigatória (Se for Saída e não tiver pausa)
    if ($tipo === 'saida') {
        $hoje = date('Y-m-d');
        $checkPausa = $pdo->prepare("SELECT id FROM pontos WHERE servidor_id = ? AND tipo = 'pausa' AND DATE(data_hora) = ?");
        $checkPausa->execute([$servidor['id'], $hoje]);
        
        if (!$checkPausa->fetch()) {
            // Aqui o sistema avisaria para preencher o modal manual (simulado no retorno)
            echo json_encode(['success' => false, 'message' => 'Pausa não registrada. Por favor, informe o horário de pausa manualmente no sistema.']);
            exit;
        }
    }

    // 5. Registrar o Ponto
    try {
        $sql = "INSERT INTO pontos (servidor_id, tipo, latitude_registro, longitude_registro) VALUES (?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$servidor['id'], $tipo, $lat_servidor, $lng_servidor]);
        
        echo json_encode(['success' => true, 'message' => 'Ponto de ' . ucfirst($tipo) . ' registrado com sucesso!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco: ' . $e->getMessage()]);
    }
}