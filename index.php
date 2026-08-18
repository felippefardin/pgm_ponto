<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>PMG PONTO - Login ADM</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Ajuste para o botão do olho não quebrar o design */
        #togglePassword {
            cursor: pointer;
            border-left: none;
        }
        #senha {
            border-right: none;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container vh-100 d-flex justify-content-center align-items-center">
        <div class="card p-4 shadow" style="width: 400px;">
            <h3 class="text-center mb-4">Login Administrativo</h3>
            <form action="processa_login.php" method="POST">
                <div class="mb-3">
                    <label>E-mail</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Senha</label>
                    <div class="input-group">
                        <input type="password" name="senha" id="senha" class="form-control" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fa-solid fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    <div class="text-end mt-2">
                        <a href="esqueci_senha.php" class="text-decoration-none text-warning">Esqueceu a senha?</a>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>
            <hr>
            <p class="text-center">Não tem conta? <a href="cadastro.php">Fazer Cadastro</a></p>
            <a href="login_servidor.php" class="btn btn-success w-100 mb-2">
                <i class="fa-solid fa-user-clock me-2"></i>Acesso do Servidor
            </a>
            <a href="bater_ponto.php" class="btn btn-outline-primary w-100">
                <i class="fa-solid fa-fingerprint me-2"></i>Bater ponto sem login
            </a>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#senha');
        const eyeIcon = document.querySelector('#eyeIcon');

        togglePassword.addEventListener('click', function () {
            // Alterna o tipo do input entre password e text
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Alterna o ícone entre olho aberto e cortado
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
