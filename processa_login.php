<?php
session_start();
include 'db.php'; // Usa as configurações de conexão existentes

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Busca o administrador pelo e-mail na tabela instituicoes
    $stmt = $pdo->prepare("SELECT * FROM instituicoes WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verifica se o usuário existe e se a senha é válida
    if ($user && password_verify($senha, $user['senha'])) {
        
        // Verifica se o e-mail já foi verificado pelo código
        if ($user['verificado'] == 0) {
            header("Location: confirmar_codigo.php?email=" . $user['email']);
            exit();
        }

        // Se estiver tudo certo, cria a sessão e vai para o Dashboard
        $_SESSION['adm_id'] = $user['id'];
        $_SESSION['instituicao_nome'] = $user['nome_instituicao'];
        
        header("Location: dashboard.php");
        exit();
    } else {
        // Se a senha ou e-mail estiverem errados
        echo "<script>alert('E-mail ou senha incorretos.'); window.location='index.php';</script>";
    }
}
?>