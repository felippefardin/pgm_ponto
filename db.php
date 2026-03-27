<?php
// Configurações do banco de dados
$host = 'localhost';
$db   = 'pgm_ponto';
$user = 'root'; // Ajuste se o seu usuário for diferente
$pass = '';     // Ajuste se o seu banco tiver senha
$sgbd = 'mysql'; 

try {
    // Instância do PDO
    $pdo = new PDO("$sgbd:host=$host;dbname=$db;charset=utf8", $user, $pass);
    
    // Configura para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados: " || $e->getMessage());
}
?>