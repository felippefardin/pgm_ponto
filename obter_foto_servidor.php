<?php
include 'db.php';
$matricula = $_GET['matricula'] ?? '';
$stmt = $pdo->prepare("SELECT face_token FROM servidores WHERE matricula = ?");
$stmt->execute([$matricula]);
$serv = $stmt->fetch();

if ($serv && $serv['face_token']) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}