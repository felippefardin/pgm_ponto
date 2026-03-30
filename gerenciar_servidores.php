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

$stmt = $pdo->query("SELECT * FROM servidores WHERE status != 'arquivado' ORDER BY nome_completo ASC");
$servidores = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Servidores - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .cursor-pointer { cursor: pointer; }
        .pin-box { 
            display: inline-flex; 
            align-items: center; 
            min-width: 80px; 
            justify-content: space-between;
        }
    </style>
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
            <table class="table table-hover align-middle">
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
                        <td>
                            <div class="pin-box">
                                <span id="pin-text-<?= $s['id'] ?>" style="font-family: monospace;">****</span>
                                <i class="fa-solid fa-eye text-primary cursor-pointer ms-2" 
                                   id="pin-btn-<?= $s['id'] ?>"
                                   onclick="togglePin(<?= $s['id'] ?>, '<?= htmlspecialchars($s['pin']) ?>')">
                                </i>
                            </div>
                        </td>
                        <td><span class="badge bg-info"><?= ucfirst($s['status']) ?></span></td>
                        <td>
                            <a href="editar_servidor.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                            <a href="?acao=arquivar&id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary" onclick="return confirm('Arquivar?')">Arquivar</a>
                            <a href="?acao=excluir&id=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Excluir?')">Excluir</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function togglePin(id, pinReal) {
    const textSpan = document.getElementById('pin-text-' + id);
    const icon = document.getElementById('pin-btn-' + id);

    if (textSpan.innerText === '****') {
        textSpan.innerText = pinReal;
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        textSpan.innerText = '****';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
</body>
</html>