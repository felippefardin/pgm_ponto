<?php
session_start();
include 'db.php';

if (!isset($_SESSION['adm_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ponto_id = filter_input(INPUT_POST, 'ponto_id', FILTER_VALIDATE_INT);
    $nova_data = trim($_POST['nova_data'] ?? '');
    $justificativa = trim($_POST['justificativa'] ?? '');
    $adm_id = (int) $_SESSION['adm_id'];

    $data = DateTime::createFromFormat('Y-m-d\TH:i', $nova_data);
    if (!$ponto_id || !$data || $justificativa === '') {
        header("Location: auditoria.php?msg=dados_invalidos");
        exit;
    }
    $nova_data = $data->format('Y-m-d H:i:s');

    // 1. Pegar valor antigo para o log
    $stmt = $pdo->prepare("SELECT p.* FROM pontos p JOIN servidores s ON s.id = p.servidor_id WHERE p.id = ? AND s.instituicao_id = ?");
    $stmt->execute([$ponto_id, $adm_id]);
    $ponto_antigo = $stmt->fetch();

    if (!$ponto_antigo) {
        header("Location: auditoria.php?msg=registro_nao_encontrado");
        exit;
    }

    $pdo->beginTransaction();
    try {
        // 2. Atualizar o ponto
        $sql = "UPDATE pontos SET data_hora = ?, registro_manual = 1, justificativa = ? WHERE id = ?";
        $pdo->prepare($sql)->execute([$nova_data, $justificativa, $ponto_id]);

    // 3. Gravar na Auditoria
    $log_sql = "INSERT INTO logs_auditoria (adm_id, servidor_afetado_id, ponto_id, acao, valor_antigo, valor_novo) 
                VALUES (?, ?, ?, 'editar', ?, ?)";
    
        $pdo->prepare($log_sql)->execute([
            $adm_id,
            $ponto_antigo['servidor_id'],
            $ponto_id,
            json_encode($ponto_antigo, JSON_UNESCAPED_UNICODE),
            json_encode(['nova_data' => $nova_data, 'motivo' => $justificativa], JSON_UNESCAPED_UNICODE)
        ]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    header("Location: auditoria.php?msg=sucesso");
    exit;
}

header("Location: auditoria.php");
exit;
