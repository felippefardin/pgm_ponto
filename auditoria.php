<?php
session_start();
include 'db.php';

// Filtros
$data_filtro = $_GET['data'] ?? date('Y-m-d');
$servidor_filtro = $_GET['servidor'] ?? '';

$sql = "SELECT p.*, s.nome_completo, s.matricula 
        FROM pontos p 
        JOIN servidores s ON p.servidor_id = s.id 
        WHERE DATE(p.data_hora) = ? ";

if ($servidor_filtro) {
    $sql .= " AND s.id = " . (int)$servidor_filtro;
}

$stmt = $pdo->prepare($sql);
$stmt->execute([$data_filtro]);
$pontos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Auditoria de Pontos - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
</head>
<body class="bg-light">
<div class="container-fluid py-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa-solid fa-shield-halved"></i> Painel de Auditoria</h5>
            <a href="logs.php" class="btn btn-dark btn-sm">Ver Logs de Alteração</a>
        </div>
        <div class="card-body">
            <form class="row g-3 mb-4">
                <div class="col-md-3">
                    <label>Data</label>
                    <input type="date" name="data" class="form-control" value="<?= $data_filtro ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </form>

            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Servidor</th>
                        <th>Matrícula</th>
                        <th>Tipo</th>
                        <th>Hora Original</th>
                        <th>Manual?</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pontos as $p): ?>
                    <tr>
                        <td><?= $p['nome_completo'] ?></td>
                        <td><?= $p['matricula'] ?></td>
                        <td><span class="badge bg-secondary"><?= ucfirst($p['tipo']) ?></span></td>
                        <td><?= date('H:i:s', strtotime($p['data_hora'])) ?></td>
                        <td><?= $p['registro_manual'] ? 'Sim' : 'Não' ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="abrirEdicao(<?= $p['id'] ?>)">
                                <i class="fa-solid fa-pen"></i> Editar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdicao" tabindex="-1">
    <div class="modal-dialog">
        <form action="processar_edicao.php" method="POST" class="modal-content">
            <div class="modal-header"><h5>Alterar Registro de Ponto</h5></div>
            <div class="modal-body">
                <input type="hidden" name="ponto_id" id="edit_ponto_id">
                <div class="mb-3">
                    <label>Novo Horário</label>
                    <input type="datetime-local" name="nova_data" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Justificativa da Alteração</label>
                    <textarea name="justificativa" class="form-control" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Salvar Alteração</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirEdicao(id) {
    document.getElementById('edit_ponto_id').value = id;
    new bootstrap.Modal(document.getElementById('modalEdicao')).show();
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>