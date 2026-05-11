<?php
session_start();
include 'db.php';

$servidor_id = $_GET['servidor'] ?? ''; // Pode ser vazio para "Todos"
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
$data_fim = $_GET['data_fim'] ?? date('Y-m-t');
$export = $_GET['export'] ?? '';

// Função auxiliar para calcular a diferença de horas
function calcularHoras($entrada, $saida) {
    if (!$entrada || !$saida) return 0;
    $ini = new DateTime($entrada);
    $fim = new DateTime($saida);
    $diff = $ini->diff($fim);
    return ($diff->h + ($diff->i / 60));
}

$pontos = [];
$params = [$data_inicio, $data_fim];
$sql = "SELECT p.*, s.nome_completo, s.matricula 
        FROM pontos p 
        JOIN servidores s ON p.servidor_id = s.id 
        WHERE DATE(p.data_hora) BETWEEN ? AND ?";

if ($servidor_id !== '') {
    $sql .= " AND s.id = ?";
    $params[] = $servidor_id;
}

$sql .= " ORDER BY s.nome_completo ASC, p.data_hora ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pontos = $stmt->fetchAll();

// Agrupamento por Servidor -> Data -> Tipo
$relatorio_agrupado = [];
foreach ($pontos as $p) {
    $s_id = $p['servidor_id'];
    $data = date('d/m/Y', strtotime($p['data_hora']));
    $relatorio_agrupado[$s_id]['info'] = ['nome' => $p['nome_completo'], 'matricula' => $p['matricula']];
    $relatorio_agrupado[$s_id]['dias'][$data][$p['tipo']] = $p['data_hora'];
}

// LÓGICA DE EXPORTAÇÃO CSV (Simplificada para todos ou um)
if ($export === 'csv' && !empty($relatorio_agrupado)) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=relatorio_ponto_geral.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Servidor', 'Matricula', 'Data', 'Entrada', 'Pausa', 'Saida', 'Total Horas Dia']);

    foreach ($relatorio_agrupado as $id => $dados) {
        foreach ($dados['dias'] as $data => $tipos) {
            $h_total = calcularHoras($tipos['entrada'] ?? null, $tipos['saida'] ?? null);
            fputcsv($output, [
                $dados['info']['nome'],
                $dados['info']['matricula'],
                $data,
                isset($tipos['entrada']) ? date('H:i', strtotime($tipos['entrada'])) : '--:--',
                isset($tipos['pausa']) ? date('H:i', strtotime($tipos['pausa'])) : '--:--',
                isset($tipos['saida']) ? date('H:i', strtotime($tipos['saida'])) : '--:--',
                number_format($h_total, 2)
            ]);
        }
    }
    fclose($output);
    exit();
}

$servidores = $pdo->query("SELECT id, nome_completo FROM servidores WHERE status != 'arquivado'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatórios de Ponto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print { .no-print { display: none !important; } .page-break { page-break-after: always; } }
        .folha-ponto { font-family: 'Courier New', monospace; font-size: 0.9rem; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-info mb-4 no-print">
    <div class="container-fluid">
        <span class="navbar-brand">Relatório de Frequência</span>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">Voltar</a>
    </div>
</nav>

<div class="container py-4">
    <div class="card shadow mb-4 no-print">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label>Servidor</label>
                    <select name="servidor" class="form-select">
                        <option value="">TODOS OS SERVIDORES</option>
                        <?php foreach($servidores as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= $servidor_id == $s['id'] ? 'selected' : '' ?>><?= $s['nome_completo'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Data Inicial</label>
                    <input type="date" name="data_inicio" class="form-control" value="<?= $data_inicio ?>">
                </div>
                <div class="col-md-3">
                    <label>Data Final</label>
                    <input type="date" name="data_fim" class="form-control" value="<?= $data_fim ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                    <a href="?<?= $_SERVER['QUERY_STRING'] ?>&export=csv" class="btn btn-success">CSV</a>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($relatorio_agrupado)): ?>
        <div class="alert alert-warning">Nenhum registro encontrado.</div>
    <?php else: ?>
        <?php foreach ($relatorio_agrupado as $id_serv => $conteudo): 
            $total_horas_mes = 0;
        ?>
        <div class="card shadow p-4 mb-5 folha-ponto page-break">
            <div class="text-center mb-4">
                <h4>PROCURADORIA GERAL DO MUNICÍPIO</h4>
                <h5>Espelho de Ponto Individual</h5>
                <p>Período: <?= date('d/m/Y', strtotime($data_inicio)) ?> a <?= date('d/m/Y', strtotime($data_fim)) ?></p>
            </div>

            <p><strong>Servidor:</strong> <?= $conteudo['info']['nome'] ?> | <strong>Matrícula:</strong> <?= $conteudo['info']['matricula'] ?></p>

            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Entrada</th>
                        <th>Pausa</th>
                        <th>Saída</th>
                        <th>Horas do Dia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($conteudo['dias'] as $data => $tipos): 
                        $h_dia = calcularHoras($tipos['entrada'] ?? null, $tipos['saida'] ?? null);
                        $total_horas_mes += $h_dia;
                    ?>
                    <tr>
                        <td><?= $data ?></td>
                        <td><?= isset($tipos['entrada']) ? date('H:i', strtotime($tipos['entrada'])) : '--:--' ?></td>
                        <td><?= isset($tipos['pausa']) ? date('H:i', strtotime($tipos['pausa'])) : '--:--' ?></td>
                        <td><?= isset($tipos['saida']) ? date('H:i', strtotime($tipos['saida'])) : '--:--' ?></td>
                        <td><?= number_format($h_dia, 2) ?>h</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-secondary">
                        <td colspan="4" class="text-end"><strong>Total de Horas no Período:</strong></td>
                        <td><strong><?= number_format($total_horas_mes, 2) ?>h</strong></td>
                    </tr>
                </tfoot>
            </table>

            <div class="row mt-5">
                <div class="col-6 text-center">_________________________<br>Assinatura Servidor</div>
                <div class="col-6 text-center">_________________________<br>Visto Chefia</div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="text-center no-print mb-5">
            <button onclick="window.print()" class="btn btn-danger btn-lg">
                <i class="fa-solid fa-print"></i> Imprimir Tudo / Salvar PDF
            </button>
        </div>
    <?php endif; ?>
</div>
</body>
</html>