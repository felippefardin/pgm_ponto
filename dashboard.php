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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
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
        /* Ajuste para o Footer colar no fundo se a página for pequena */
body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
.container {
    flex: 1;
}

/* Estilo do Footer */
footer {
    background: #ffffff;
    border-top: 1px solid #dee2e6;
    padding: 2rem 0;
}
footer i {
    transition: color 0.3s;
}
footer a:hover i {
    color: #0d6efd !important;
}
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">
            <i class="fa-solid fa-gauge-high"></i> PMG_PONTO | DASHBOARD
        </span>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">
            <i class="fa-solid fa-right-from-bracket"></i> Sair
        </a>
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
            <a href="gerenciar_servidores.php" class="btn btn-dark w-100 btn-dash">
    <i class="fa-solid fa-users-gear"></i> Gerenciar Servidores
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
    <a href="perfil_adm.php" class="btn btn-dark w-100 btn-dash" style="background-color: #4b5563; border: none;">
        <i class="fa-solid fa-user-gear"></i> Perfil ADM
    </a>
</div>
        <div class="col-md-3">
    <a href="servidores_arquivados.php" class="btn btn-outline-secondary w-100 btn-dash">
        <i class="fa-solid fa-box-archive"></i> Arquivados
    </a>
</div>
        <div class="col-md-3">
            <a href="esqueci_pin.php" class="btn btn-outline-dark w-100 btn-dash">
                <i class="fa-solid fa-key"></i> Esqueci PIN
            </a>
        </div>
    </div>
</div>

<footer class="mt-5">
    <div class="container text-center">
        <div class="row align-items-center">
            <div class="col-md-4 text-md-start mb-3 mb-md-0">
                <span class="fw-bold text-dark"><i class="fa-solid fa-clock-rotate-left"></i> PMG_PONTO</span>
                <br>
                <small class="text-muted">Gestão de Frequência Inteligente</small>
            </div>
            
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="d-flex justify-content-center gap-3">
                    <a href="#" class="text-muted text-decoration-none"><i class="fa-solid fa-shield-halved fa-lg"></i></a>
                    <a href="#" class="text-muted text-decoration-none"><i class="fa-solid fa-circle-question fa-lg"></i></a>
                    <a href="#" class="text-muted text-decoration-none"><i class="fa-solid fa-envelope fa-lg"></i></a>
                </div>
            </div>

            <div class="col-md-4 text-md-end">
                <small class="text-muted">&copy; 2026 Todos os direitos reservados</small>
                <br>
                <small class="fw-bold" style="color: #6c757d;">Desenvolvido para PMG</small>
            </div>
        </div>
        
        <hr class="my-4 opacity-25">
        
        <div class="row">
            <div class="col">
                <p class="mb-0 small text-muted">
                    <i class="fa-solid fa-lock"></i> Conexão Segura | 
                    <i class="fa-solid fa-location-dot"></i> Unidade: Procuradoria Geral
                </p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>