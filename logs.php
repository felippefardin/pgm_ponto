<?php
session_start();
include 'db.php'; // Conecta ao banco pgm_ponto

// Busca todos os logs de auditoria cruzando com dados do ADM e do Servidor
$stmt = $pdo->query("
    SELECT l.*, i.nome_instituicao as adm_nome, s.nome_completo as servidor_nome 
    FROM logs_auditoria l
    JOIN instituicoes i ON l.adm_id = i.id
    JOIN servidores s ON l.servidor_afetado_id = s.id
    ORDER BY l.data_alteracao DESC
");
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Logs de Auditoria - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-clipboard-list"></i> Histórico de Alterações (Logs)</span>
        <a href="auditoria.php" class="btn btn-outline-light btn-sm">Voltar para Auditoria</a>
    </div>
</nav>

<div class="container-fluid">
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover">
                    <thead class="table-secondary">
                        <tr>
                            <th>Data da Alteração</th>
                            <th>Administrador</th>
                            <th>Servidor Afetado</th>
                            <th>Ação</th>
                            <th>Dados Anteriores</th>
                            <th>Novos Dados</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i:s', strtotime($log['data_alteracao'])) ?></td>
                            <td><?= htmlspecialchars($log['adm_nome']) ?></td>
                            <td><?= htmlspecialchars($log['servidor_nome']) ?></td>
                            <td>
                                <span class="badge <?= $log['acao'] == 'editar' ? 'bg-primary' : 'bg-danger' ?>">
                                    <?= ucfirst($log['acao']) ?>
                                </span>
                            </td>
                            <td class="small"><pre><?= json_encode(json_decode($log['valor_antigo']), JSON_PRETTY_PRINT) ?></pre></td>
                            <td class="small"><pre><?= json_encode(json_decode($log['valor_novo']), JSON_PRETTY_PRINT) ?></pre></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">Nenhuma alteração registrada até o momento.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>