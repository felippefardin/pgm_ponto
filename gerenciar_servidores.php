<?php
session_start();
include 'db.php';

// Lógica para Excluir ou Arquivar
if (isset($_GET['acao']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    if ($_GET['acao'] == 'excluir') {
        $pdo->prepare("DELETE FROM servidores WHERE id = ?")->execute([$id]);
    } elseif ($_GET['acao'] == 'arquivar') {
        $pdo->prepare("UPDATE servidores SET status = 'arquivado' WHERE id = ?")->execute([$id]);
    }
    header("Location: gerenciar_servidores.php");
    exit();
}

$stmt = $pdo->query("SELECT * FROM servidores WHERE status != 'arquivado' ORDER BY nome_completo ASC");
$servidores = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Servidores - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
        .cursor-pointer { cursor: pointer; }
        .pin-box { 
            display: inline-flex; 
            align-items: center; 
            min-width: 90px; 
            justify-content: space-between;
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }
        .table img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>
<body class="bg-light">

<div class="main-content">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fa-solid fa-users-gear text-primary"></i> Gerenciar Servidores
            </span>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-secondary">Lista de Servidores Ativos</h5>
                <a href="cadastrar_servidor.php" class="btn btn-success btn-sm">
                    <i class="fa-solid fa-plus"></i> Novo Servidor
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nome do Servidor</th>
                                <th>Matrícula</th>
                                <th>PIN de Acesso</th>
                                <th>Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($servidores as $s): ?>
                            <tr>
                                <td class="fw-bold text-dark">
                                    <i class="fa-solid fa-circle-user text-muted me-2"></i>
                                    <?= htmlspecialchars($s['nome_completo']) ?>
                                </td>
                                <td><code class="text-primary fw-bold"><?= htmlspecialchars($s['matricula']) ?></code></td>
                                <td>
                                    <div class="pin-box shadow-sm">
                                        <span id="pin-text-<?= $s['id'] ?>" class="small fw-bold">****</span>
                                        <i class="fa-solid fa-eye text-primary cursor-pointer ms-2" 
                                           id="pin-btn-<?= $s['id'] ?>"
                                           onclick="togglePin(<?= $s['id'] ?>, '<?= htmlspecialchars($s['pin']) ?>')">
                                        </i>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3">
                                        <i class="fa-solid fa-check-circle me-1"></i> <?= ucfirst($s['status']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group shadow-sm">
                                        <a href="editar_servidor.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <a href="?acao=arquivar&id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Arquivar" onclick="return confirm('Deseja realmente arquivar este servidor?')">
                                            <i class="fa-solid fa-box-archive"></i>
                                        </a>
                                        <a href="?acao=excluir&id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="return confirm('ATENÇÃO: Esta ação é permanente! Confirmar exclusão?')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($servidores)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-user-slash fa-3x mb-3 d-block opacity-25"></i>
                                    Nenhum servidor ativo encontrado.
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
                <small class="fw-bold text-secondary">Módulo Gerencial</small>
            </div>
        </div>
        
        <hr class="my-4 opacity-25">
        
        <div class="row">
            <div class="col text-center">
                <p class="mb-0 small text-muted">
                    <i class="fa-solid fa-lock text-success"></i> Sistema Autenticado | 
                    <i class="fa-solid fa-user-shield text-primary"></i> Acesso Restrito ao Administrador
                </p>
            </div>
        </div>
    </div>
</footer>

<script>
function togglePin(id, pinReal) {
    const textSpan = document.getElementById('pin-text-' + id);
    const icon = document.getElementById('pin-btn-' + id);

    if (textSpan.innerText === '****') {
        textSpan.innerText = pinReal;
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        icon.classList.replace('text-primary', 'text-danger');
    } else {
        textSpan.innerText = '****';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        icon.classList.replace('text-danger', 'text-primary');
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>