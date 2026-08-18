<?php
header('Content-Type: application/json; charset=utf-8');
require 'db.php';
require_once 'localizacao.php';

$matricula = trim($_GET['matricula'] ?? '');
if ($matricula === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Informe a matrícula.']);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT s.status, c.latitude, c.longitude, c.raio_metros
     FROM servidores s
     LEFT JOIN cercas_geograficas c ON c.instituicao_id = s.instituicao_id
     WHERE s.matricula = ? LIMIT 1"
);
$stmt->execute([$matricula]);
$dados = $stmt->fetch();

if (!$dados || $dados['status'] !== 'ativo') {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Matrícula não encontrada ou servidor inativo.']);
    exit;
}

echo json_encode([
    'success' => true,
    'latitude' => (float) ($dados['latitude'] ?? LOCALIZACAO_LATITUDE),
    'longitude' => (float) ($dados['longitude'] ?? LOCALIZACAO_LONGITUDE),
    'raio_metros' => (int) ($dados['raio_metros'] ?? LOCALIZACAO_RAIO_METROS),
]);
