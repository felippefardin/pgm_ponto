<?php
include 'db.php';
$matricula = $_GET['matricula'] ?? '';
$stmt = $pdo->prepare("SELECT face_token FROM servidores WHERE matricula = ?");
$stmt->execute([$matricula]);
$serv = $stmt->fetch();

if ($serv && $serv['face_token']) {
    // Retorna o sucesso e a string Base64 da imagem
    echo json_encode(['success' => true, 'face_token' => $serv['face_token']]);
} else {
    echo json_encode(['success' => false]);
}