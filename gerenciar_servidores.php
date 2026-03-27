<?php
session_start();
include 'db.php';

// Lógica para Excluir ou Arquivar
if (isset($_GET['acao']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    if ($_GET['acao'] == 'excluir') {
        $pdo->prepare("DELETE FROM servidores WHERE id = ?")->execute([$id]);
    } elseif ($_GET['acao'] == 'arquivar') {
        $pdo->prepare("UPDATE servidores SET status = 'arquivado' WHERE id = ?")->execute([$id]);
    }
    header("Location: gerenciar_servidores.php");
}

// Busca todos os servidores (exceto os arquivados, se preferir)
$stmt = $pdo->query("SELECT * FROM servidores WHERE status != 'arquivado' ORDER BY nome_completo ASC");
$servidores = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Servidores - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">Lista de Servidores Cadastrados</span>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">Voltar</a>
    </div>
</nav>

<div class="container">
    <div class="card shadow">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Matrícula</th>
                        <th>PIN</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($servidores as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['nome_completo']) ?></td>
                        <td><?= htmlspecialchars($s['matricula']) ?></td>
                        <td>****</td>
                        <td><span class="badge bg-info"><?= ucfirst($s['status']) ?></span></td>
                        <td>
                            <a href="editar_servidor.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                            
                            <a href="?acao=arquivar&id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary" onclick="return confirm('Arquivar servidor?')">Arquivar</a>
                            
                            <a href="?acao=excluir&id=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Excluir permanentemente?')">Excluir</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>