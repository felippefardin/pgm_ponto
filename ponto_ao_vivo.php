<?php
session_start();
include 'db.php';

// Busca todos os pontos registrados no dia de hoje
$hoje = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT p.*, s.nome_completo, s.matricula 
    FROM pontos p 
    JOIN servidores s ON p.servidor_id = s.id 
    WHERE DATE(p.data_hora) = ? 
    ORDER BY p.data_hora DESC
");
$stmt->execute([$hoje]);
$pontos_hoje = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Ponto ao Vivo - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <meta http-equiv="refresh" content="30"> 
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-success mb-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-tower-broadcast"></i> Monitoramento ao Vivo</span>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">Voltar ao Painel</a>
    </div>
</nav>

<div class="container">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Registros de Hoje (<?= date('d/m/Y') ?>)</h5>
            <span class="badge bg-primary">Atualiza em 30s</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Servidor</th>
                            <th>Matrícula</th>
                            <th>Tipo</th>
                            <th>Localização</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pontos_hoje) > 0): ?>
                            <?php foreach ($pontos_hoje as $p): ?>
                            <tr>
                                <td><strong><?= date('H:i:s', strtotime($p['data_hora'])) ?></strong></td>
                                <td><?= htmlspecialchars($p['nome_completo']) ?></td>
                                <td><?= htmlspecialchars($p['matricula']) ?></td>
                                <td>
                                    <?php 
                                    $classe = ($p['tipo'] == 'entrada') ? 'success' : (($p['tipo'] == 'saida') ? 'danger' : 'warning');
                                    ?>
                                    <span class="badge bg-<?= $classe ?>"><?= ucfirst($p['tipo']) ?></span>
                                </td>
                                <td>
                                    <small>Lat: <?= $p['latitude_registro'] ?>, Lng: <?= $p['longitude_registro'] ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Nenhum ponto batido hoje até o momento.</td>
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