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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoria de Pontos - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
    <style>
        /* Estrutura para o Footer colar no fim */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;
        }
        footer {
            background: #ffffff;
            border-top: 1px solid #dee2e6;
            padding: 2rem 0;
            margin-top: 3rem;
        }
    </style>
</head>
<body class="bg-light">

<div class="main-content">
    <nav class="navbar navbar-dark bg-warning mb-4 text-dark">
        <div class="container-fluid">
            <h5 class="mb-0 text-dark">
                <i class="fa-solid fa-file-circle-check"></i> Painel de Auditoria
            </h5>
            <div class="d-flex gap-2">
                <a href="logs.php" class="btn btn-dark btn-sm">
                    <i class="fa-solid fa-list-check"></i> Ver Logs de Alteração
                </a>
                <a href="dashboard.php" class="btn btn-outline-dark btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-2">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Data do Registro</label>
                        <input type="date" name="data" class="form-control" value="<?= $data_filtro ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa-solid fa-filter"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
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
                        <?php if (count($pontos) > 0): ?>
                            <?php foreach ($pontos as $p): ?>
                            <tr class="align-middle">
                                <td><?= htmlspecialchars($p['nome_completo']) ?></td>
                                <td><code class="text-dark"><?= $p['matricula'] ?></code></td>
                                <td>
                                    <?php 
                                    $cor = ($p['tipo'] == 'entrada') ? 'success' : (($p['tipo'] == 'saida') ? 'danger' : 'warning');
                                    ?>
                                    <span class="badge bg-<?= $cor ?>"><?= ucfirst($p['tipo']) ?></span>
                                </td>
                                <td><strong><?= date('H:i:s', strtotime($p['data_hora'])) ?></strong></td>
                                <td>
                                    <?= $p['registro_manual'] ? 
                                        '<span class="text-warning"><i class="fa-solid fa-hand"></i> Sim</span>' : 
                                        '<span class="text-muted"><i class="fa-solid fa-robot"></i> Não</span>' ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="abrirEdicao(<?= $p['id'] ?>)">
                                        <i class="fa-solid fa-pen-to-square"></i> Ajustar
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Nenhum registro encontrado para esta data.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdicao" tabindex="-1">
    <div class="modal-dialog">
        <form action="processar_edicao.php" method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa-solid fa-pen-to-square"></i> Alterar Registro de Ponto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="ponto_id" id="edit_ponto_id">
                <div class="mb-3">
                    <label class="form-label fw-bold">Novo Horário</label>
                    <input type="datetime-local" name="nova_data" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Justificativa da Alteração</label>
                    <textarea name="justificativa" class="form-control" placeholder="Descreva o motivo desta correção legal..." rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">
                    <i class="fa-solid fa-floppy-disk"></i> Confirmar Alteração
                </button>
            </div>
        </form>
    </div>
</div>

<footer>
    <div class="container text-center">
        <div class="row align-items-center">
            <div class="col-md-4 text-md-start mb-3 mb-md-0">
                <span class="fw-bold text-dark"><i class="fa-solid fa-clock-rotate-left"></i> PMG_PONTO</span>
                <br>
                <small class="text-muted">Gestão de Frequência Inteligente</small>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="d-flex justify-content-center gap-3">
                    <a href="#" class="text-muted text-decoration-none"><i class="fa-solid fa-shield-halved fa-lg"></i></a>
                    <a href="#" class="text-muted text-decoration-none"><i class="fa-solid fa-circle-question fa-lg"></i></a>
                    <a href="#" class="text-muted text-decoration-none"><i class="fa-solid fa-envelope fa-lg"></i></a>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <small class="text-muted">&copy; 2026 Todos os direitos reservados</small>
                <br>
                <small class="fw-bold text-secondary">Painel de Auditoria</small>
            </div>
        </div>
        <hr class="my-4 opacity-25">
        <p class="mb-0 small text-muted"><i class="fa-solid fa-lock"></i> Conexão Segura | Acesso Administrativo Rastreado</p>
    </div>
</footer>

<script>
function abrirEdicao(id) {
    document.getElementById('edit_ponto_id').value = id;
    new bootstrap.Modal(document.getElementById('modalEdicao')).show();
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>