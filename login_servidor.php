<?php
session_start();

if (isset($_SESSION['servidor_id'])) {
    header('Location: painel_servidor.php');
    exit;
}

$erro = $_SESSION['erro_login_servidor'] ?? '';
unset($_SESSION['erro_login_servidor']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso do Servidor - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container min-vh-100 d-flex justify-content-center align-items-center py-4">
        <div class="card border-0 shadow p-4" style="width: 100%; max-width: 430px;">
            <div class="text-center mb-4">
                <i class="fa-solid fa-user-clock fa-3x text-success mb-3"></i>
                <h3>Acesso do Servidor</h3>
                <p class="text-muted mb-0">Consulte seus registros de ponto</p>
            </div>

            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form action="processa_login_servidor.php" method="POST">
                <div class="mb-3">
                    <label for="matricula" class="form-label">Matrícula</label>
                    <input type="text" name="matricula" id="matricula" class="form-control form-control-lg" autocomplete="username" required autofocus>
                </div>
                <div class="mb-4">
                    <label for="pin" class="form-label">PIN de 6 dígitos</label>
                    <div class="input-group input-group-lg">
                        <input type="password" name="pin" id="pin" class="form-control" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="current-password" required>
                        <button type="button" class="btn btn-outline-secondary" id="mostrarPin" aria-label="Mostrar PIN">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-success btn-lg w-100">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>Entrar
                </button>
            </form>

            <hr class="my-4">
            <a href="bater_ponto.php" class="btn btn-primary w-100 mb-2">
                <i class="fa-solid fa-fingerprint me-2"></i>Bater ponto sem login
            </a>
            <a href="index.php" class="btn btn-link text-secondary text-decoration-none">Acesso administrativo</a>
        </div>
    </div>
    <script>
        document.getElementById('mostrarPin').addEventListener('click', function () {
            const campo = document.getElementById('pin');
            campo.type = campo.type === 'password' ? 'text' : 'password';
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
