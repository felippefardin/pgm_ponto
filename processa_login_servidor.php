<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login_servidor.php');
    exit;
}

$matricula = trim($_POST['matricula'] ?? '');
$pin = trim($_POST['pin'] ?? '');

if ($matricula === '' || !preg_match('/^\d{6}$/', $pin)) {
    $_SESSION['erro_login_servidor'] = 'Informe a matrícula e o PIN de 6 dígitos.';
    header('Location: login_servidor.php');
    exit;
}

$stmt = $pdo->prepare("SELECT id, instituicao_id, nome_completo, matricula, pin, status FROM servidores WHERE matricula = ? LIMIT 1");
$stmt->execute([$matricula]);
$servidor = $stmt->fetch();

$pinValido = $servidor && (
    password_verify($pin, $servidor['pin'])
    || hash_equals((string) $servidor['pin'], $pin)
);

if (!$pinValido) {
    $_SESSION['erro_login_servidor'] = 'Matrícula ou PIN incorretos.';
    header('Location: login_servidor.php');
    exit;
}

if ($servidor['status'] !== 'ativo') {
    $_SESSION['erro_login_servidor'] = 'Seu acesso está ' . $servidor['status'] . '. Procure o administrador.';
    header('Location: login_servidor.php');
    exit;
}

session_regenerate_id(true);
$_SESSION['servidor_id'] = (int) $servidor['id'];
$_SESSION['servidor_nome'] = $servidor['nome_completo'];
$_SESSION['servidor_matricula'] = $servidor['matricula'];

header('Location: painel_servidor.php');
exit;
