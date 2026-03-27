<?php
include 'db.php';
// Lógica simplificada de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['instituicao'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $codigo = rand(100000, 999999); // Gera o código

    $stmt = $pdo->prepare("INSERT INTO instituicoes (nome_instituicao, email, senha, codigo_verificacao) VALUES (?, ?, ?, ?)");
    
    if ($stmt->execute([$nome, $email, $senha, $codigo])) {
        // Como o mail() não funciona no XAMPP sem config, vamos apenas redirecionar
        // Em produção, você usaria o PHPMailer aqui.
        header("Location: confirmar_codigo.php?email=$email&debug_code=$codigo");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro - PMG PONTO</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card mx-auto shadow" style="max-width: 500px;">
            <div class="card-body">
                <h3>Cadastro de Instituição</h3>
                <form method="POST">
                    <div class="mb-3">
                        <label>Nome da Instituição</label>
                        <input type="text" name="instituicao" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>E-mail (ADM)</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Senha</label>
                        <input type="password" name="senha" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Receber Código no E-mail</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>