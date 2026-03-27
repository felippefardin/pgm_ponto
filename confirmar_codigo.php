<?php
include 'db.php';
$email = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_digitado = $_POST['codigo'];
    
    $stmt = $pdo->prepare("SELECT id FROM instituicoes WHERE email = ? AND codigo_verificacao = ?");
    $stmt->execute([$email, $codigo_digitado]);
    $user = $stmt->fetch();

    if ($user) {
        $update = $pdo->prepare("UPDATE instituicoes SET verificado = 1 WHERE id = ?");
        $update->execute([$user['id']]);
        echo "<script>alert('Cadastro realizado com sucesso!'); window.location='index.php';</script>";
    } else {
        $erro = "Código incorreto!";
    }
}
?>
<div class="container mt-5 text-center">
    <div class="card mx-auto p-4" style="max-width: 400px;">
        <h4>Digite o código enviado para <br><small><?php echo $email; ?></small></h4>
        <form method="POST">
            <input type="text" name="codigo" class="form-control mb-3 text-center" placeholder="000000" required>
            <button type="submit" class="btn btn-primary w-100">Verificar e Finalizar</button>
        </form>
        <?php if(isset($erro)) echo "<p class='text-danger mt-2'>$erro</p>"; ?>
    </div>
</div>