<?php
session_start();
include 'db.php'; // Liga ao banco de dados pgm_ponto

// Lógica para desbloquear servidor
if (isset($_GET['desbloquear'])) {
    $id = $_GET['desbloquear'];
    $stmt = $pdo->prepare("UPDATE servidores SET status = 'ativo' WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo "<script>alert('Servidor desbloqueado com sucesso!'); window.location='servidor_bloqueado.php';</script>";
    }
}

// Procura apenas servidores com status 'bloqueado'
$stmt = $pdo->prepare("SELECT * FROM servidores WHERE status = 'bloqueado'");
$stmt->execute();
$bloqueados = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servidores Bloqueados - PMG PONTO</title>
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
    <nav class="navbar navbar-dark bg-danger mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fa-solid fa-user-slash"></i> GESTÃO DE BLOQUEIOS
            </span>                  
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-danger">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Servidores com Acesso Restrito
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-4">A listagem abaixo contém servidores que foram bloqueados automaticamente por pendências de registro ou inconsistências.</p>
                
                <div class="table-responsive">
                    <table class="table table-hover table-v-align">
                        <thead class="table-light">
                            <tr>
                                <th>Nome do Servidor</th>
                                <th>Matrícula</th>
                                <th>Motivo do Bloqueio</th>
                                <th class="text-end">Ação Administrativa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($bloqueados) > 0): ?>
                                <?php foreach ($bloqueados as $s): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($s['nome_completo']) ?></td>
                                    <td><code class="text-dark fw-bold"><?= htmlspecialchars($s['matricula']) ?></code></td>
                                    <td>
                                        <span class="badge bg-warning text-dark px-3 py-2">
                                            <i class="fa-solid fa-clock me-1"></i> Pendência de batida
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="?desbloquear=<?= $s['id'] ?>" class="btn btn-success" onclick="return confirm('Deseja realmente reativar o acesso deste servidor?')">
                                            <i class="fa-solid fa-unlock"></i> Desbloquear
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <i class="fa-solid fa-circle-check fa-3x text-success mb-3 d-block opacity-25"></i>
                                        <span class="text-muted">Excelente! Não existem servidores bloqueados no momento.</span>
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
                <small class="fw-bold text-secondary">Módulo de Segurança</small>
            </div>
        </div>
        
        <hr class="my-4 opacity-25">
        
        <div class="row">
            <div class="col">
                <p class="mb-0 small text-muted">
                    <i class="fa-solid fa-lock text-success"></i> Conexão Segura | 
                    <i class="fa-solid fa-user-shield text-danger"></i> Controle de Acessos Restritos
                </p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>