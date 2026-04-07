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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ponto ao Vivo - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
    <meta http-equiv="refresh" content="30"> 
    <style>
        /* Garante que o footer fique no fim da página */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;
        }
        footer {
            background: #ffffff;
            border-top: 1px solid #dee2e6;
            padding: 2rem 0;
            margin-top: 3rem;
        }
    </style>
</head>
<body class="bg-light">

<div class="main-content">
    <nav class="navbar navbar-dark bg-success mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-tower-broadcast"></i> Monitoramento ao Vivo</span>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel
            </a>
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
</div>

<footer>
    <div class="container text-center">
        <div class="row align-items-center">
            <div class="col-md-4 text-md-start mb-3 mb-md-0">
                <span class="fw-bold text-dark"><i class="fa-solid fa-clock-rotate-left"></i> PMG_PONTO</span>
                <br>
                <small class="text-muted">Gestão de Frequência Inteligente</small>
            </div>
            
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="d-flex justify-content-center gap-3">
                    <a href="#" class="text-muted text-decoration-none"><i class="fa-solid fa-shield-halved fa-lg"></i></a>
                    <a href="#" class="text-muted text-decoration-none"><i class="fa-solid fa-circle-question fa-lg"></i></a>
                    <a href="#" class="text-muted text-decoration-none"><i class="fa-solid fa-envelope fa-lg"></i></a>
                </div>
            </div>

            <div class="col-md-4 text-md-end">
                <small class="text-muted">&copy; 2026 Todos os direitos reservados</small>
                <br>
                <small class="fw-bold" style="color: #6c757d;">Desenvolvido para PMG</small>
            </div>
        </div>
        
        <hr class="my-4 opacity-25">
        
        <div class="row">
            <div class="col">
                <p class="mb-0 small text-muted">
                    <i class="fa-solid fa-lock"></i> Conexão Segura | 
                    <i class="fa-solid fa-location-dot"></i> Unidade: Procuradoria Geral
                </p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>