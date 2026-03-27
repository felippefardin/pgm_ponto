<?php
session_start();
session_destroy(); // Limpa todas as sessões
header("Location: index.php"); // Volta para o ecrã de login
exit();
?>