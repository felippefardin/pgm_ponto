<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ponto_id = $_POST['ponto_id'];
    $nova_data = $_POST['nova_data'];
    $justificativa = $_POST['justificativa'];
    $adm_id = 1; // ID do ADM logado

    // 1. Pegar valor antigo para o log
    $stmt = $pdo->prepare("SELECT * FROM pontos WHERE id = ?");
    $stmt->execute([$ponto_id]);
    $ponto_antigo = $stmt->fetch();

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
        json_encode($ponto_antigo), 
        json_encode(['nova_data' => $nova_data, 'motivo' => $justificativa])
    ]);

    header("Location: auditoria.php?msg=sucesso");
}