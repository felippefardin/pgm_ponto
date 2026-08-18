<?php
session_start();
require 'db.php';

if (!isset($_SESSION['servidor_id'])) {
    header('Location: login_servidor.php');
    exit;
}

$servidorId = (int) $_SESSION['servidor_id'];
$data = $_GET['data'] ?? date('Y-m-d');
$dataValida = DateTime::createFromFormat('Y-m-d', $data);
if (!$dataValida || $dataValida->format('Y-m-d') !== $data) {
    $data = date('Y-m-d');
}

$stmtServidor = $pdo->prepare("SELECT nome_completo, matricula, status FROM servidores WHERE id = ? LIMIT 1");
$stmtServidor->execute([$servidorId]);
$servidor = $stmtServidor->fetch();

if (!$servidor || $servidor['status'] !== 'ativo') {
    header('Location: logout_servidor.php');
    exit;
}

$stmt = $pdo->prepare("SELECT tipo, data_hora, registro_manual, justificativa FROM pontos WHERE servidor_id = ? AND DATE(data_hora) = ? ORDER BY data_hora ASC");
$stmt->execute([$servidorId, $data]);
$pontos = $stmt->fetchAll();

$entrada = null;
$saida = null;
foreach ($pontos as $ponto) {
    if ($ponto['tipo'] === 'entrada' && $entrada === null) {
        $entrada = new DateTime($ponto['data_hora']);
    }
    if ($ponto['tipo'] === 'saida') {
        $saida = new DateTime($ponto['data_hora']);
    }
}

$total = '--';
if ($entrada && $saida && $saida >= $entrada) {
    $intervalo = $entrada->diff($saida);
    $total = sprintf('%02dh %02dmin', ($intervalo->days * 24) + $intervalo->h, $intervalo->i);
}

$rotulos = ['entrada' => 'Entrada', 'pausa' => 'Pausa', 'saida' => 'Saída'];
$cores = ['entrada' => 'success', 'pausa' => 'warning', 'saida' => 'danger'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pontos - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-success">
        <div class="container">
            <span class="navbar-brand"><i class="fa-solid fa-user-clock me-2"></i>Meu Controle de Ponto</span>
            <a href="logout_servidor.php" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-right-from-bracket me-1"></i>Sair</a>
        </div>
    </nav>

    <main class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h3 class="mb-1">Olá, <?= htmlspecialchars($servidor['nome_completo']) ?></h3>
                <span class="text-muted">Matrícula: <?= htmlspecialchars($servidor['matricula']) ?></span>
            </div>
            <a href="bater_ponto.php" class="btn btn-primary btn-lg"><i class="fa-solid fa-fingerprint me-2"></i>Bater ponto</a>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="data" class="form-label fw-semibold">Consultar dia</label>
                        <input type="date" name="data" id="data" class="form-control" value="<?= htmlspecialchars($data) ?>" max="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-3"><button class="btn btn-success w-100"><i class="fa-solid fa-magnifying-glass me-2"></i>Consultar</button></div>
                    <div class="col-md-4"><a href="painel_servidor.php" class="btn btn-outline-secondary w-100">Ver hoje</a></div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Data consultada</small><h4 class="mb-0"><?= date('d/m/Y', strtotime($data)) ?></h4></div></div></div>
            <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Marcações</small><h4 class="mb-0"><?= count($pontos) ?></h4></div></div></div>
            <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Período entre entrada e saída</small><h4 class="mb-0"><?= $total ?></h4></div></div></div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3"><h5 class="mb-0">Registros do dia</h5></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Tipo</th><th>Horário</th><th>Origem</th><th>Observação</th></tr></thead>
                    <tbody>
                    <?php foreach ($pontos as $ponto): ?>
                        <tr>
                            <td><span class="badge bg-<?= $cores[$ponto['tipo']] ?? 'secondary' ?>"><?= $rotulos[$ponto['tipo']] ?? htmlspecialchars($ponto['tipo']) ?></span></td>
                            <td class="fw-bold"><?= date('H:i:s', strtotime($ponto['data_hora'])) ?></td>
                            <td><?= $ponto['registro_manual'] ? 'Ajuste administrativo' : 'Registro biométrico' ?></td>
                            <td><?= htmlspecialchars($ponto['justificativa'] ?: '--') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$pontos): ?>
                        <tr><td colspan="4" class="text-center text-muted py-5"><i class="fa-regular fa-calendar-xmark fa-2x d-block mb-2"></i>Nenhum ponto registrado neste dia.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
