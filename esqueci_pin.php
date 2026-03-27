<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricula = $_POST['matricula'];
    $novo_pin = rand(100000, 999999); // Gera um novo PIN de 6 dígitos

    $stmt = $pdo->prepare("UPDATE servidores SET pin = ? WHERE matricula = ?");
    if ($stmt->execute([$novo_pin, $matricula])) {
        // Em produção, aqui enviaria o e-mail para o administrador
        echo "<script>alert('Novo PIN gerado: $novo_pin. Informe este código ao servidor.'); window.location='dashboard.php';</script>";
    } else {
        echo "<script>alert('Erro ao localizar matrícula.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recuperar PIN - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow mx-auto" style="max-width: 400px;">
        <div class="card-header bg-dark text-white"><h5>Reset de PIN</h5></div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label>Matrícula do Servidor</label>
                    <input type="text" name="matricula" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Gerar Novo PIN</button>
                <a href="dashboard.php" class="btn btn-link w-100 mt-2">Voltar</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>