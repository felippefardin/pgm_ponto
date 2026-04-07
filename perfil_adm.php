<?php
session_start();
include 'db.php';

// Proteção: Se não houver sessão, volta para o login
if (!isset($_SESSION['adm_id'])) {
    header("Location: index.php");
    exit();
}

$adm_id = $_SESSION['adm_id'];
$msg_feedback = ""; 

// 1. Buscar dados atuais do ADM
$stmt = $pdo->prepare("SELECT * FROM instituicoes WHERE id = ?");
$stmt->execute([$adm_id]);
$adm = $stmt->fetch();

// 2. Lógica de Atualização de Dados (Nome e E-mail)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar'])) {
    $nome = $_POST['nome_instituicao'];
    $email = $_POST['email'];
    
    $update = $pdo->prepare("UPDATE instituicoes SET nome_instituicao = ?, email = ? WHERE id = ?");
    if ($update->execute([$nome, $email, $adm_id])) {
        $_SESSION['instituicao_nome'] = $nome; 
        echo "<script>alert('Dados atualizados!'); window.location='perfil_adm.php';</script>";
    }
}

// 3. Alteração de Senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_senha'])) {
    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if (password_verify($senha_atual, $adm['senha'])) {
        if ($nova_senha === $confirmar_senha) {
            $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE instituicoes SET senha = ? WHERE id = ?");
            if ($update->execute([$hash, $adm_id])) {
                $msg_feedback = "<div class='alert alert-success'><i class='fa-solid fa-check-double'></i> Senha alterada com sucesso!</div>";
            }
        } else {
            $msg_feedback = "<div class='alert alert-danger'><i class='fa-solid fa-xmark'></i> As novas senhas não coincidem.</div>";
        }
    } else {
        $msg_feedback = "<div class='alert alert-danger'><i class='fa-solid fa-triangle-exclamation'></i> Senha atual incorreta.</div>";
    }
}

// 4. Lógica de Exclusão de Conta
if (isset($_GET['acao']) && $_GET['acao'] === 'excluir_conta') {
    try {
        $pdo->prepare("DELETE FROM logs_auditoria WHERE adm_id = ?")->execute([$adm_id]);
        $pdo->prepare("DELETE FROM cercas_geograficas WHERE instituicao_id = ?")->execute([$adm_id]);
        
        $delete = $pdo->prepare("DELETE FROM instituicoes WHERE id = ?");
        if ($delete->execute([$adm_id])) {
            session_destroy();
            echo "<script>alert('Sua conta e todos os dados vinculados foram excluídos.'); window.location='index.php';</script>";
            exit();
        }
    } catch (PDOException $e) {
        echo "<script>alert('Erro ao excluir conta: " . $e->getMessage() . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil Administrativo - PMG PONTO</title>
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
        .card-header h5 {
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body class="bg-light">

<div class="main-content">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fa-solid fa-user-shield"></i> PERFIL DO ADMINISTRADOR
            </span>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel
            </a>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                
                <?= $msg_feedback ?>

                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0">
                            <i class="fa-solid fa-address-card"></i> Dados Cadastrais
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nome da Instituição</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-building"></i></span>
                                    <input type="text" name="nome_instituicao" class="form-control" value="<?= htmlspecialchars($adm['nome_instituicao'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">E-mail de Acesso</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($adm['email'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" name="atualizar" class="btn btn-success">
                                    <i class="fa-solid fa-floppy-disk me-2"></i> Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-warning text-dark py-3 border-bottom-0">
                        <h5 class="mb-0">
                            <i class="fa-solid fa-shield-halved"></i> Segurança e Senha
                        </h5>
                    </div>
                    <div class="card-body p-4 border-top border-warning border-3">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Senha Atual</label>
                                <input type="password" name="senha_atual" class="form-control" required>
                            </div>
                            <hr class="my-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nova Senha</label>
                                <input type="password" name="nova_senha" class="form-control" placeholder="Mínimo 6 caracteres" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Confirmar Nova Senha</label>
                                <input type="password" name="confirmar_senha" class="form-control" required>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" name="alterar_senha" class="btn btn-warning text-dark fw-bold">
                                    <i class="fa-solid fa-arrows-rotate me-2"></i> Atualizar Senha
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-danger border-1 mb-5">
                    <div class="card-header bg-danger text-white py-3">
                        <h5 class="mb-0">
                            <i class="fa-solid fa-skull-crossbones"></i> Zona Crítica
                        </h5>
                    </div>
                    <div class="card-body text-center p-4">
                        <p class="text-muted small">
                            <i class="fa-solid fa-calendar-day me-1"></i> Instituição cadastrada em: <?= isset($adm['criado_em']) ? date('d/m/Y', strtotime($adm['criado_em'])) : '--' ?>
                        </p>
                        <div class="d-grid mt-3">
                            <a href="?acao=excluir_conta" class="btn btn-outline-danger" 
                               onclick="return confirm('ATENÇÃO CRÍTICA: Isso apagará permanentemente sua instituição, servidores, registros de ponto e auditoria. Esta ação não pode ser desfeita. Confirmar?')">
                                <i class="fa-solid fa-trash-can me-2"></i> Excluir Minha Conta Permanentemente
                            </a>
                        </div>
                    </div>
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
                    <a href="#" class="text-muted text-decoration-none" title="Dúvidas"><i class="fa-solid fa-circle-question fa-lg"></i></a>
                    <a href="#" class="text-muted text-decoration-none" title="Suporte"><i class="fa-solid fa-envelope fa-lg"></i></a>
                </div>
            </div>

            <div class="col-md-4 text-md-end">
                <small class="text-muted">&copy; 2026 Todos os direitos reservados</small>
                <br>
                <small class="fw-bold text-secondary">Configurações de Conta</small>
            </div>
        </div>
        
        <hr class="my-4 opacity-25">
        
        <div class="row">
            <div class="col">
                <p class="mb-0 small text-muted">
                    <i class="fa-solid fa-lock text-success"></i> Conexão Segura | 
                    <i class="fa-solid fa-user-gear text-primary"></i> Gerenciamento de Privilégios
                </p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>