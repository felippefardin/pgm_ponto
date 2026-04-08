<?php
include 'db.php'; 

$erro_msg = ""; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['instituicao'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $codigo = rand(100000, 999999);

    try {
        $checkEmail = $pdo->prepare("SELECT id FROM instituicoes WHERE email = ?");
        $checkEmail->execute([$email]);
        
        if ($checkEmail->rowCount() > 0) {
            $erro_msg = "Este e-mail já está cadastrado!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO instituicoes (nome_instituicao, email, senha, codigo_verificacao) VALUES (?, ?, ?, ?)");
            
            if ($stmt->execute([$nome, $email, $senha, $codigo])) {
                // AVISO TEMPORÁRIO: Como o envio de e-mail requer PHPMailer, 
                // exibimos o código via JavaScript antes de redirecionar para você poder testar.
                echo "<script>
                    alert('CADASTRO REALIZADO! \\nComo o servidor de e-mail não está configurado, use o código: $codigo');
                    window.location.href = 'confirmar_codigo.php?email=$email';
                </script>";
                exit();
            }
        }
    } catch (PDOException $e) {
        $erro_msg = "Erro no sistema: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <style>
        .password-container { position: relative; }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card mx-auto shadow border-0" style="max-width: 500px; border-radius: 15px;">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <i class="fa-solid fa-building-shield fa-3x text-success mb-2"></i>
                <h3 class="fw-bold">Cadastro</h3>
            </div>
            
            <?php if ($erro_msg): ?>
                <div class="alert alert-danger text-center small">
                    <i class="fa-solid fa-circle-exclamation me-2"></i><?= $erro_msg ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nome da Instituição</label>
                    <input type="text" name="instituicao" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">E-mail (ADM)</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Senha</label>
                    <div class="password-container">
                        <input type="password" name="senha" id="senha" class="form-control" required>
                        <i class="fa-solid fa-eye toggle-password" id="toggleIcon"></i>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-success w-100 py-2 fw-bold mt-3">
                    Receber Código
                </button>
            </form>
            
            <div class="text-center mt-3">
                <a href="index.php" class="text-decoration-none small text-muted">Já tem conta? Voltar ao login</a>
            </div>
        </div>
    </div>
</div>

<script>
    const togglePassword = document.querySelector('#toggleIcon');
    const passwordField = document.querySelector('#senha');

    togglePassword.addEventListener('click', function () {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>