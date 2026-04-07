<?php
session_start();
include 'db.php';

// Proteção: Apenas ADM logado acessa
if (!isset($_SESSION['adm_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricula = $_POST['matricula'];
    $novo_pin = rand(100000, 999999); // Gera um novo PIN de 6 dígitos

    $stmt = $pdo->prepare("UPDATE servidores SET pin = ? WHERE matricula = ?");
    if ($stmt->execute([$novo_pin, $matricula])) {
        // Em produção, aqui enviaria o e-mail para o administrador
        echo "<script>alert('Novo PIN gerado com sucesso: $novo_pin. Informe este código ao servidor.'); window.location='dashboard.php';</script>";
    } else {
        echo "<script>alert('Erro ao localizar matrícula. Verifique os dados e tente novamente.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar PIN - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
    <style>
        /* Estrutura para o Footer colar no fim da página */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;
        }
        footer {
            background: #ffffff;
            border-top: 1px solid #dee2e6;
            padding: 2rem 0;
            margin-top: 3rem;
        }
    </style>
</head>
<body class="bg-light">

<div class="main-content">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fa-solid fa-key"></i> <i class="fa-solid fa-arrows-rotate fa-xs"></i> RESET DE PIN
            </span>                  
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel
            </a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="card shadow-sm border-0 mx-auto" style="max-width: 450px;">
            <div class="card-header bg-white py-3 text-center">
                <h5 class="mb-0 fw-bold text-secondary">Recuperação de Acesso</h5>
            </div>
            <div class="card-body p-4 text-center">
                <div class="mb-4">
                    <i class="fa-solid fa-user-lock fa-3x text-primary opacity-50"></i>
                </div>
                <p class="text-muted small mb-4">Insira a matrícula do servidor para gerar um novo código PIN aleatório de 6 dígitos.</p>
                
                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-bold d-block text-start">Matrícula do Servidor</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-hashtag"></i></span>
                            <input type="text" name="matricula" class="form-control" placeholder="Digite a matrícula" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm mb-3">
                        <i class="fa-solid fa-wand-magic-sparkles me-2"></i> Gerar Novo PIN
                    </button>
                    
                    <a href="dashboard.php" class="text-decoration-none text-muted small">
                        <i class="fa-solid fa-xmark me-1"></i> Cancelar operação
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<footer>
    <div class="container text-center">
        <div class="row align-items-center">
            <div class="col-md-4 text-md-start mb-3 mb-md-0">
                <span class="fw-bold text-dark"><i class="fa-solid fa-clock-rotate-left"></i> PMG_PONTO</span>
                <br>
                <small class="text-muted">Gestão de Frequência Inteligente</small>
            </div>
            
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="d-flex justify-content-center gap-3">
                    <a href="#" class="text-muted text-decoration-none" title="Segurança"><i class="fa-solid fa-shield-halved fa-lg"></i></a>
                    <a href="#" class="text-muted text-decoration-none" title="Ajuda"><i class="fa-solid fa-circle-question fa-lg"></i></a>
                    <a href="#" class="text-muted text-decoration-none" title="Suporte"><i class="fa-solid fa-envelope fa-lg"></i></a>
                </div>
            </div>

            <div class="col-md-4 text-md-end">
                <small class="text-muted">&copy; 2026 Todos os direitos reservados</small>
                <br>
                <small class="fw-bold text-secondary">Módulo de Credenciais</small>
            </div>
        </div>
        
        <hr class="my-4 opacity-25">
        
        <div class="row">
            <div class="col text-center">
                <p class="mb-0 small text-muted">
                    <i class="fa-solid fa-lock text-success"></i> Conexão Segura | 
                    <i class="fa-solid fa-shield-check text-primary"></i> Alteração Registrada em Log de Auditoria
                </p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>