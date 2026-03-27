<?php
session_start();
include 'db.php';

$servidor_id = $_GET['servidor'] ?? '';
$mes = $_GET['mes'] ?? date('m');
$ano = $_GET['ano'] ?? date('Y');

$pontos = [];
if ($servidor_id) {
    $stmt = $pdo->prepare("
        SELECT p.*, s.nome_completo, s.matricula 
        FROM pontos p 
        JOIN servidores s ON p.servidor_id = s.id 
        WHERE s.id = ? AND MONTH(p.data_hora) = ? AND YEAR(p.data_hora) = ?
        ORDER BY p.data_hora ASC
    ");
    $stmt->execute([$servidor_id, $mes, $ano]);
    $pontos = $stmt->fetchAll();
    
    // Busca dados do servidor para o cabeçalho
    $stmt_serv = $pdo->prepare("SELECT nome_completo, matricula FROM servidores WHERE id = ?");
    $stmt_serv->execute([$servidor_id]);
    $info_servidor = $stmt_serv->fetch();
}

// Busca todos os servidores para o select
$servidores = $pdo->query("SELECT id, nome_completo FROM servidores WHERE status != 'arquivado'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Ponto - PMG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
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
                <div class="col-md-4">
                    <label>Servidor</label>
                    <select name="servidor" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach($servidores as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= $servidor_id == $s['id'] ? 'selected' : '' ?>><?= $s['nome_completo'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Mês</label>
                    <input type="number" name="mes" class="form-control" value="<?= $mes ?>" min="1" max="12">
                </div>
                <div class="col-md-3">
                    <label>Ano</label>
                    <input type="number" name="ano" class="form-control" value="<?= $ano ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Gerar</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($servidor_id && $info_servidor): ?>
    <div class="card shadow p-4 folha-ponto">
        <div class="text-center mb-4">
            <h4>PROCURADORIA GERAL DO MUNICÍPIO</h4>
            <h5>Espelho de Ponto Individual - <?= $mes ?>/<?= $ano ?></h5>
        </div>

        <div class="mb-3 border-bottom pb-2">
            <p><strong>Servidor:</strong> <?= $info_servidor['nome_completo'] ?> | <strong>Matrícula:</strong> <?= $info_servidor['matricula'] ?></p>
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
                // Agrupar pontos por dia
                $dias = [];
                foreach ($pontos as $p) {
                    $data = date('d/m/Y', strtotime($p['data_hora']));
                    $dias[$data][$p['tipo']] = date('H:i', strtotime($p['data_hora']));
                }

                foreach ($dias as $data => $tipos): ?>
                <tr>
                    <td><?= $data ?></td>
                    <td><?= $tipos['entrada'] ?? '--:--' ?></td>
                    <td><?= $tipos['pausa'] ?? '--:--' ?></td>
                    <td><?= $tipos['saida'] ?? '--:--' ?></td>
                    <td class="small"><?= (isset($tipos['entrada']) && isset($tipos['saida'])) ? '' : 'Falta batida' ?></td>
                </tr>
                <?php endforeach; ?>
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
            <button onclick="window.print()" class="btn btn-success"><i class="fa-solid fa-print"></i> Imprimir / Salvar PDF</button>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.js"></script>
</body>
</html>