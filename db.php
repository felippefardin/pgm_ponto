<?php
// Configurações do banco de dados
$host = 'localhost';
$db   = 'pgm_ponto';
$user = 'root'; // Ajuste se o seu usuário for diferente
$pass = '';     // Ajuste se o seu banco tiver senha
$sgbd = 'mysql'; 

try {
    // Instância do PDO
    $pdo = new PDO("$sgbd:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    // Configura para lançar exceções em caso de erro
} catch (PDOException $e) {
    http_response_code(500);
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}
?>
