<?php
session_start();
include 'db.php'; // Liga ao banco de dados pgm_ponto

// Lógica para desbloquear servidor
if (isset($_GET['desbloquear'])) {
    $id = $_GET['desbloquear'];
    $stmt = $pdo->prepare("UPDATE servidores SET status = 'ativo' WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo "<script>alert('Servidor desbloqueado com sucesso!'); window.location='servidor_bloqueado.php';</script>";
    }
}

// Procura apenas servidores com status 'bloqueado'
$stmt = $pdo->prepare("SELECT * FROM servidores WHERE status = 'bloqueado'");
$stmt->execute();
$bloqueados = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Servidores Bloqueados - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-danger mb-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-user-lock"></i> Gestão de Bloqueios</span>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">Voltar ao Painel</a>
    </div>
</nav>

<div class="container">
    <div class="card shadow">
        <div class="card-header">
            <h5 class="mb-0">Servidores que esqueceram de bater ponto</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Matrícula</th>
                            <th>Motivo</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($bloqueados) > 0): ?>
                            <?php foreach ($bloqueados as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['nome_completo']) ?></td>
                                <td><?= htmlspecialchars($s['matricula']) ?></td>
                                <td><span class="badge bg-warning text-dark">Pendência de batida</span></td>
                                <td>
                                    <a href="?desbloquear=<?= $s['id'] ?>" class="btn btn-sm btn-success">
                                        <i class="fa-solid fa-unlock"></i> Libertar Servidor
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">Não existem servidores bloqueados no momento.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>