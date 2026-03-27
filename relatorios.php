<?php
session_start();
include 'db.php';

$servidor_id = $_GET['servidor'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01'); // Padrão: primeiro dia do mês atual
$data_fim = $_GET['data_fim'] ?? date('Y-m-t');      // Padrão: último dia do mês atual
$export = $_GET['export'] ?? '';

$pontos = [];
if ($servidor_id) {
    // Busca os pontos dentro do intervalo de datas selecionado
    $stmt = $pdo->prepare("
        SELECT p.*, s.nome_completo, s.matricula 
        FROM pontos p 
        JOIN servidores s ON p.servidor_id = s.id 
        WHERE s.id = ? AND DATE(p.data_hora) BETWEEN ? AND ?
        ORDER BY p.data_hora ASC
    ");
    $stmt->execute([$servidor_id, $data_inicio, $data_fim]);
    $pontos = $stmt->fetchAll();
    
    $stmt_serv = $pdo->prepare("SELECT nome_completo, matricula FROM servidores WHERE id = ?");
    $stmt_serv->execute([$servidor_id]);
    $info_servidor = $stmt_serv->fetch();

    // LÓGICA DE EXPORTAÇÃO CSV
    if ($export === 'csv' && $info_servidor) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=relatorio_ponto_'.$info_servidor['matricula'].'.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Data', 'Entrada', 'Pausa', 'Saida', 'Matricula', 'Nome']);

        $dias_csv = [];
        foreach ($pontos as $p) {
            $data = date('d/m/Y', strtotime($p['data_hora']));
            $dias_csv[$data][$p['tipo']] = date('H:i', strtotime($p['data_hora']));
        }

        foreach ($dias_csv as $data => $tipos) {
            fputcsv($output, [
                $data, 
                $tipos['entrada'] ?? '--:--', 
                $tipos['pausa'] ?? '--:--', 
                $tipos['saida'] ?? '--:--', 
                $info_servidor['matricula'], 
                $info_servidor['nome_completo']
            ]);
        }
        fclose($output);
        exit();
    }
}

$servidores = $pdo->query("SELECT id, nome_completo FROM servidores WHERE status != 'arquivado'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Ponto - PMG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
            body { background: white !important; }
        }
        .folha-ponto { font-family: 'Courier New', Courier, monospace; }
    </style>
</head>
<body class="bg-light">

<div class="container py-4">
    <div class="card shadow mb-4 no-print">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label>Servidor</label>
                    <select name="servidor" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach($servidores as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= $servidor_id == $s['id'] ? 'selected' : '' ?>><?= $s['nome_completo'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Data Inicial</label>
                    <input type="date" name="data_inicio" class="form-control" value="<?= $data_inicio ?>" required>
                </div>
                <div class="col-md-3">
                    <label>Data Final</label>
                    <input type="date" name="data_fim" class="form-control" value="<?= $data_fim ?>" required>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Filtrar</button>
                    <?php if($servidor_id): ?>
                        <a href="?servidor=<?= $servidor_id ?>&data_inicio=<?= $data_inicio ?>&data_fim=<?= $data_fim ?>&export=csv" class="btn btn-success">
                            <i class="fa-solid fa-file-csv"></i> CSV
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php if ($servidor_id && $info_servidor): ?>
    <div class="card shadow p-4 folha-ponto">
        <div class="text-center mb-4">
            <h4>PROCURADORIA GERAL DO MUNICÍPIO</h4>
            <h5>Espelho de Ponto Individual</h5>
            <p class="small text-muted">Período: <?= date('d/m/Y', strtotime($data_inicio)) ?> até <?= date('d/m/Y', strtotime($data_fim)) ?></p>
        </div>

        <div class="mb-3 border-bottom pb-2">
            <p><strong>Servidor:</strong> <?= htmlspecialchars($info_servidor['nome_completo']) ?> | <strong>Matrícula:</strong> <?= htmlspecialchars($info_servidor['matricula']) ?></p>
        </div>

        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th>Data</th>
                    <th>Entrada</th>
                    <th>Pausa</th>
                    <th>Saída</th>
                    <th>Observações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $dias = [];
                foreach ($pontos as $p) {
                    $data = date('d/m/Y', strtotime($p['data_hora']));
                    $dias[$data][$p['tipo']] = date('H:i', strtotime($p['data_hora']));
                }

                if (count($dias) > 0):
                    foreach ($dias as $data => $tipos): ?>
                    <tr>
                        <td><?= $data ?></td>
                        <td><?= $tipos['entrada'] ?? '--:--' ?></td>
                        <td><?= $tipos['pausa'] ?? '--:--' ?></td>
                        <td><?= $tipos['saida'] ?? '--:--' ?></td>
                        <td class="small"><?= (isset($tipos['entrada']) && isset($tipos['saida'])) ? '' : 'Falta batida' ?></td>
                    </tr>
                    <?php endforeach;
                else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Nenhum registro encontrado para este período.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="row mt-5 pt-4">
            <div class="col-6 text-center">
                <hr style="width: 80%; margin: auto;">
                <p>Assinatura do Servidor</p>
            </div>
            <div class="col-6 text-center">
                <hr style="width: 80%; margin: auto;">
                <p>Visto Chefia Imediata</p>
            </div>
        </div>
        
        <div class="text-center mt-4 no-print">
            <button onclick="window.print()" class="btn btn-danger">
                <i class="fa-solid fa-file-pdf"></i> Baixar/Imprimir PDF
            </button>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.js"></script>
</body>
</html>