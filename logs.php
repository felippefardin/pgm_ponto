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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs de Auditoria - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
    <style>
        /* Estrutura para o Footer colar no fim da página */
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
        /* Melhoria na visualização do JSON */
        pre {
            background: #f8f9fa;
            padding: 5px;
            border-radius: 4px;
            font-size: 0.75rem;
            max-width: 250px;
            max-height: 150px;
            overflow: auto;
            margin-bottom: 0;
        }
    </style>
</head>
<body class="bg-light">

<div class="main-content">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fa-solid fa-clock-rotate-left"></i> Histórico de Alterações (Logs)
            </span>
            <a href="auditoria.php" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Voltar para Auditoria
            </a>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 text-uppercase fw-bold text-secondary">
                    <i class="fa-solid fa-database me-2"></i>Registros Internos de Segurança
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>Data da Alteração</th>
                                <th>Administrador</th>
                                <th>Servidor Afetado</th>
                                <th class="text-center">Ação</th>
                                <th>Dados Anteriores</th>
                                <th>Novos Dados</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="text-nowrap small fw-bold"><?= date('d/m/Y H:i:s', strtotime($log['data_alteracao'])) ?></td>
                                <td><i class="fa-solid fa-user-shield text-muted me-1"></i> <?= htmlspecialchars($log['adm_nome']) ?></td>
                                <td><i class="fa-solid fa-user text-muted me-1"></i> <?= htmlspecialchars($log['servidor_nome']) ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $log['acao'] == 'editar' ? 'bg-primary' : 'bg-danger' ?> text-uppercase">
                                        <i class="fa-solid <?= $log['acao'] == 'editar' ? 'fa-pen' : 'fa-triangle-exclamation' ?> me-1"></i>
                                        <?= $log['acao'] ?>
                                    </span>
                                </td>
                                <td><pre><?= json_encode(json_decode($log['valor_antigo']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre></td>
                                <td><pre><?= json_encode(json_decode($log['valor_novo']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre></td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-folder-open fa-2x mb-2 d-block"></i>
                                    Nenhuma alteração registrada até o momento.
                                </td>
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
                <small class="fw-bold text-secondary">Módulo de Rastreabilidade</small>
            </div>
        </div>
        
        <hr class="my-4 opacity-25">
        
        <div class="row">
            <div class="col">
                <p class="mb-0 small text-muted">
                    <i class="fa-solid fa-lock text-success"></i> Conexão Segura | 
                    <i class="fa-solid fa-terminal text-primary"></i> Logs de Sistema Auditáveis
                </p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>