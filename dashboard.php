<?php
session_start();
include 'db.php';

// Proteção simples: Se não estiver logado, volta para o login
if (!isset($_SESSION['adm_id'])) {
    // header("Location: index.php"); // Descomente após testar o login
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <style>
        .btn-dash {
            height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            transition: transform 0.2s;
        }
        .btn-dash:hover {
            transform: scale(1.05);
        }
        .btn-dash i {
            font-size: 2rem;
            margin-bottom: 10px;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">PMG_PONTO | Dashboard</span>
        <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
    </div>
</nav>

<div class="container">
    <div class="row g-3">
        <div class="col-md-3">
            <a href="bater_ponto.php" class="btn btn-primary w-100 btn-dash">
                <i class="fa-solid fa-fingerprint"></i> Bater Ponto
            </a>
        </div>
        <div class="col-md-3">
            <a href="relatorios.php" class="btn btn-info text-white w-100 btn-dash">
                <i class="fa-solid fa-file-pdf"></i> Relatórios
            </a>
        </div>
        <div class="col-md-3">
            <a href="ponto_ao_vivo.php" class="btn btn-success w-100 btn-dash">
                <i class="fa-solid fa-tower-broadcast"></i> Ponto ao Vivo
            </a>
        </div>
        <div class="col-md-3">
            <a href="auditoria.php" class="btn btn-warning text-white w-100 btn-dash">
                <i class="fa-solid fa-shield-halved"></i> Auditoria
            </a>
        </div>

        <div class="col-md-3">
            <a href="cadastrar_servidor.php" class="btn btn-dark w-100 btn-dash">
                <i class="fa-solid fa-user-plus"></i> Cadastrar Servidor
            </a>
        </div>
        <div class="col-md-3">
            <a href="servidor_bloqueado.php" class="btn btn-danger w-100 btn-dash">
                <i class="fa-solid fa-user-slash"></i> Servidor Bloqueado
            </a>
        </div>
        <div class="col-md-3">
            <a href="mapa.php" class="btn btn-secondary w-100 btn-dash">
                <i class="fa-solid fa-map-location-dot"></i> Mapa
            </a>
        </div>
        <div class="col-md-3">
            <a href="esqueci_pin.php" class="btn btn-outline-dark w-100 btn-dash">
                <i class="fa-solid fa-key"></i> Esqueci PIN
            </a>
        </div>
    </div>
</div>

<footer class="text-center mt-5 text-muted">
    <small>&copy; 2026 PMG PONTO - Sistema de Controle Biométrico</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>