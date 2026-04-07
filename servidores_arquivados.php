<?php
session_start();
include 'db.php';

// Lógica para Restaurar ou Excluir Definitivamente
if (isset($_GET['acao']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    if ($_GET['acao'] == 'restaurar') {
        // Volta o status para ativo
        $pdo->prepare("UPDATE servidores SET status = 'ativo' WHERE id = ?")->execute([$id]);
        echo "<script>alert('Servidor restaurado!'); window.location='servidores_arquivados.php';</script>";
    } elseif ($_GET['acao'] == 'excluir_total') {
        // Remove permanentemente do banco de dados
        $pdo->prepare("DELETE FROM servidores WHERE id = ?")->execute([$id]);
        echo "<script>alert('Removido permanentemente!'); window.location='servidores_arquivados.php';</script>";
    }
}

// Busca apenas os servidores arquivados
$stmt = $pdo->query("SELECT * FROM servidores WHERE status = 'arquivado' ORDER BY nome_completo ASC");
$arquivados = $stmt->fetchAll();    
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servidores Arquivados - PMG PONTO</title>
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
        .table-v-align td {
            vertical-align: middle;
        }
    </style>
</head>
<body class="bg-light">

<div class="main-content">
    <nav class="navbar navbar-dark bg-secondary mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fa-solid fa-file-zipper"></i> ARQUIVO DE SERVIDORES (INATIVOS)
            </span>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-secondary">
                    <i class="fa-solid fa-box-archive me-2"></i>Registros Inativos
                </h5>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-4">Atenção: Servidores nesta lista não podem bater ponto. Você pode restaurá-los para a lista ativa ou removê-los permanentemente.</p>
                
                <div class="table-responsive">
                    <table class="table table-hover table-v-align">
                        <thead class="table-light">
                            <tr>
                                <th>Nome do Servidor</th>
                                <th>Matrícula</th>
                                <th>Status Atual</th>
                                <th class="text-end">Ações Disponíveis</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($arquivados) > 0): ?>
                                <?php foreach ($arquivados as $s): ?>
                                <tr>
                                    <td class="fw-bold text-dark">
                                        <i class="fa-solid fa-user-ghost text-muted me-2"></i>
                                        <?= htmlspecialchars($s['nome_completo']) ?>
                                    </td>
                                    <td><code class="text-secondary fw-bold"><?= htmlspecialchars($s['matricula']) ?></code></td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3">
                                            <i class="fa-solid fa-archive me-1"></i> Arquivado
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="?acao=restaurar&id=<?= $s['id'] ?>" class="btn btn-sm btn-success">
                                                <i class="fa-solid fa-trash-arrow-up"></i> Restaurar
                                            </a>
                                            <a href="?acao=excluir_total&id=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('ATENÇÃO: Isso apagará todos os dados e fotos deste servidor para sempre. Confirmar?')">
                                                <i class="fa-solid fa-circle-xmark"></i> Excluir Permanente
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <i class="fa-solid fa-folder-open fa-3x text-muted mb-3 d-block opacity-25"></i>
                                        <span class="text-muted">Nenhum servidor arquivado no momento.</span>
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
                    <a href="#" class="text-muted text-decoration-none" title="Segurança"><i class="fa-solid fa-shield-halved fa-lg"></i></a>
                    <a href="#" class="text-muted text-decoration-none" title="Ajuda"><i class="fa-solid fa-circle-question fa-lg"></i></a>
                    <a href="#" class="text-muted text-decoration-none" title="Suporte"><i class="fa-solid fa-envelope fa-lg"></i></a>
                </div>
            </div>

            <div class="col-md-4 text-md-end">
                <small class="text-muted">&copy; 2026 Todos os direitos reservados</small>
                <br>
                <small class="fw-bold text-secondary">Módulo de Arquivamento</small>
            </div>
        </div>
        
        <hr class="my-4 opacity-25">
        
        <div class="row">
            <div class="col">
                <p class="mb-0 small text-muted">
                    <i class="fa-solid fa-lock text-success"></i> Conexão Segura | 
                    <i class="fa-solid fa-box-archive text-secondary"></i> Armazenamento de Dados Históricos
                </p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>