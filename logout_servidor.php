<?php
session_start();
unset($_SESSION['servidor_id'], $_SESSION['servidor_nome'], $_SESSION['servidor_matricula']);
session_regenerate_id(true);
header('Location: login_servidor.php');
exit;
