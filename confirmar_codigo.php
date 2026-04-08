<?php
session_start();
include 'db.php';

$email = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_digitado = $_POST['codigo'];
    
    // Busca o usuário pelo e-mail e código
    $stmt = $pdo->prepare("SELECT id, nome_instituicao, email FROM instituicoes WHERE email = ? AND codigo_verificacao = ?");
    $stmt->execute([$email, $codigo_digitado]);
    $user = $stmt->fetch();

    if ($user) {
        // Atualiza para verificado
        $update = $pdo->prepare("UPDATE instituicoes SET verificado = 1 WHERE id = ?");
        $update->execute([$user['id']]);

        // CRITICAL: Cria a sessão para que o usuário não precise logar de novo
        $_SESSION['adm_id'] = $user['id'];
        $_SESSION['instituicao_nome'] = $user['nome_instituicao'];

        echo "<script>alert('E-mail verificado com sucesso!'); window.location='dashboard.php';</script>";
        exit();
    } else {
        $erro = "Código de verificação incorreto ou expirado!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Código - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-4">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-envelope-open-text fa-3x text-primary mb-3"></i>
                    <h4>Verificação de E-mail</h4>
                    <p class="text-muted small">Digite o código enviado para:<br><strong><?php echo htmlspecialchars($email); ?></strong></p>
                </div>

                <form method="POST">
                    <div class="mb-3">
                        <input type="text" name="codigo" class="form-control form-control-lg text-center fw-bold" 
                               placeholder="000000" maxlength="6" required autofocus>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 btn-lg">
                        Verificar e Acessar
                    </button>
                </form>

                <?php if(isset($erro)): ?>
                    <div class="alert alert-danger mt-3 text-center small">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?php echo $erro; ?>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-4">
                    <a href="index.php" class="text-decoration-none small text-muted">
                        <i class="fa-solid fa-arrow-left me-1"></i>Voltar ao login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>