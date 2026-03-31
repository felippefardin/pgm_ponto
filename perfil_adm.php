<?php
session_start();
include 'db.php';

// Proteção: Se não houver sessão, volta para o login
if (!isset($_SESSION['adm_id'])) {
    header("Location: index.php");
    exit();
}

$adm_id = $_SESSION['adm_id'];

// 1. Buscar dados atuais do ADM
$stmt = $pdo->prepare("SELECT * FROM instituicoes WHERE id = ?");
$stmt->execute([$adm_id]);
$adm = $stmt->fetch();

// 2. Lógica de Atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar'])) {
    $nome = $_POST['nome_instituicao'];
    $email = $_POST['email'];
    
    $update = $pdo->prepare("UPDATE instituicoes SET nome_instituicao = ?, email = ? WHERE id = ?");
    if ($update->execute([$nome, $email, $adm_id])) {
        $_SESSION['instituicao_nome'] = $nome; 
        echo "<script>alert('Dados atualizados!'); window.location='perfil_adm.php';</script>";
    }
}

// 3. Lógica de Exclusão de Conta (Corrigida)
if (isset($_GET['acao']) && $_GET['acao'] === 'excluir_conta') {
    try {
        // Limpeza manual preventiva de tabelas que podem causar erro de integridade
        $pdo->prepare("DELETE FROM logs_auditoria WHERE adm_id = ?")->execute([$adm_id]);
        $pdo->prepare("DELETE FROM cercas_geograficas WHERE instituicao_id = ?")->execute([$adm_id]);
        
        // Agora exclui a instituição (isso acionará o CASCADE para servidores e pontos)
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
    <title>Perfil Administrativo - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-user-tie"></i> Perfil do Administrador</span>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">Voltar ao Painel</a>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Dados Cadastrais</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nome da Instituição</label>
                            <input type="text" name="nome_instituicao" class="form-control" value="<?= htmlspecialchars($adm['nome_instituicao'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">E-mail de Acesso</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($adm['email'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3 text-muted">
                            <small>Membro desde: <?= isset($adm['criado_em']) ? date('d/m/Y', strtotime($adm['criado_em'])) : '--' ?></small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" name="atualizar" class="btn btn-success">
                                <i class="fa-solid fa-floppy-disk"></i> Salvar Alterações
                            </button>
                            <hr>
                            <a href="?acao=excluir_conta" class="btn btn-outline-danger" 
                               onclick="return confirm('ATENÇÃO CRÍTICA: Isso apagará permanentemente sua instituição, todos os servidores cadastrados, todos os registros de ponto e o histórico de auditoria. Esta ação não pode ser desfeita. Confirmar?')">
                                <i class="fa-solid fa-trash-can"></i> Excluir Minha Conta Permanentemente
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>