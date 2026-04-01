<?php
include 'db.php';

$erro = "";
$sucesso = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    try {
        // Correção: O nome da tabela no seu SQL é 'instituicoes'
        $stmt = $pdo->prepare("SELECT id FROM instituicoes WHERE email = ?");
        $stmt->execute([$email]);
        $instituicao = $stmt->fetch();

        if ($instituicao) {
            // Aqui você implementaria o envio real de e-mail. 
            // Por enquanto, simulamos o sucesso para validar a lógica.
            $sucesso = "Se o e-mail informado estiver em nossa base, você receberá um link de recuperação em instantes.";
        } else {
            $erro = "E-mail não encontrado em nossa base de dados.";
        }
    } catch (PDOException $e) {
        $erro = "Erro no sistema: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card bg-secondary text-white p-4 shadow">
                <h3 class="text-center mb-4">Recuperar Acesso</h3>
                
                <?php if ($erro): ?>
                    <div class="alert alert-danger"><?= $erro ?></div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                    <div class="alert alert-success"><?= $sucesso ?></div>
                <?php else: ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">E-mail Cadastrado</label>
                            <input type="email" name="email" class="form-control" placeholder="exemplo@email.com" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Enviar Instruções</button>
                            <a href="index.php" class="btn btn-outline-light">Voltar ao Login</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>