<?php
session_start();
include 'db.php';

// Lógica para Restaurar ou Excluir Definitivamente
if (isset($_GET['acao']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    if ($_GET['acao'] == 'restaurar') {
        // Volta o status para ativo
        $pdo->prepare("UPDATE servidores SET status = 'ativo' WHERE id = ?")->execute([$id]);
        echo "<script>alert('Servidor restaurado!'); window.location='servidores_arquivados.php';</script>";
    } elseif ($_GET['acao'] == 'excluir_total') {
        // Remove permanentemente do banco de dados
        $pdo->prepare("DELETE FROM servidores WHERE id = ?")->execute([$id]);
        echo "<script>alert('Removido permanentemente!'); window.location='servidores_arquivados.php';</script>";
    }
}

// Busca apenas os servidores arquivados
$stmt = $pdo->query("SELECT * FROM servidores WHERE status = 'arquivado' ORDER BY nome_completo ASC");
$arquivados = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Servidores Arquivados - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-secondary mb-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-box-archive"></i> Arquivo de Servidores</span>
        <a href="gerenciar_servidores.php" class="btn btn-outline-light btn-sm">Voltar ao Gerenciamento</a>
    </div>
</nav>

<div class="container">
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Nome</th>
                            <th>Matrícula</th>
                            <th>Data de Arquivamento</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($arquivados) > 0): ?>
                            <?php foreach ($arquivados as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['nome_completo']) ?></td>
                                <td><?= htmlspecialchars($s['matricula']) ?></td>
                                <td><span class="badge bg-secondary">Arquivado</span></td>
                                <td>
                                    <a href="?acao=restaurar&id=<?= $s['id'] ?>" class="btn btn-sm btn-success">
                                        <i class="fa-solid fa-trash-arrow-up"></i> Restaurar
                                    </a>
                                    
                                    <a href="?acao=excluir_total&id=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('ATENÇÃO: Isso apagará todos os dados e fotos deste servidor para sempre. Confirmar?')">
                                        <i class="fa-solid fa-circle-xmark"></i> Excluir Permanente
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Nenhum servidor arquivado.</td>
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