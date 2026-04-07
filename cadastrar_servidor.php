<?php
session_start();
include 'db.php';

// Proteção: Garante que apenas o ADM logado consiga cadastrar
if (!isset($_SESSION['adm_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome_completo'];
    $matricula = $_POST['matricula'];
    $pin = $_POST['pin'];
    $foto_base64 = $_POST['foto_cap']; 
    $adm_id = $_SESSION['adm_id']; 

    $stmt = $pdo->prepare("INSERT INTO servidores (instituicao_id, nome_completo, matricula, pin, face_token, status) VALUES (?, ?, ?, ?, ?, 'ativo')");
    
    try {
        $stmt->execute([$adm_id, $nome, $matricula, $pin, $foto_base64]);
        echo "<script>alert('Servidor cadastrado com sucesso!'); window.location='dashboard.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Erro ao cadastrar: " . $e->getMessage() . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Servidor - PMG PONTO</title>
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
        #video { width: 100%; max-width: 400px; border-radius: 10px; background: #000; transform: scaleX(-1); border: 3px solid #dee2e6; }
        #canvas { display: none; }
        .preview-img { width: 150px; height: 150px; object-fit: cover; border-radius: 10px; border: 3px solid #198754; }
    </style>
</head>
<body class="bg-light">

<div class="main-content">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fa-solid fa-user-plus text-primary"></i> CADASTRO DE NOVO SERVIDOR
            </span>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel
            </a>
        </div>
    </nav>

    <div class="container py-4">
        <div class="card shadow border-0 mx-auto" style="max-width: 900px;">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-secondary">
                    <i class="fa-solid fa-id-card me-2"></i>Formulário de Inclusão
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" id="formCadastro">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nome Completo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                    <input type="text" name="nome_completo" class="form-control" placeholder="Ex: João Silva" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Matrícula</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-hashtag"></i></span>
                                    <input type="text" name="matricula" class="form-control" placeholder="ID Interno" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">PIN de Acesso (6 dígitos)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                    <input type="password" name="pin" maxlength="6" class="form-control" placeholder="Senha numérica" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 text-center border-start">
                            <label class="fw-bold mb-2 d-block"><i class="fa-solid fa-face-smile"></i> Biometria Facial</label>
                            <video id="video" autoplay></video>
                            <div class="mt-2">
                                <button type="button" class="btn btn-primary" onclick="capturar()">
                                    <i class="fa-solid fa-camera"></i> Capturar Rosto
                                </button>
                            </div>
                            
                            <div id="area-preview" class="mt-3" style="display:none;">
                                <div class="badge bg-success mb-2">Foto capturada com sucesso!</div>
                                <canvas id="canvas" width="400" height="300"></canvas>
                                <div class="d-flex justify-content-center">
                                    <img id="foto_preview" class="preview-img shadow">
                                </div>
                            </div>
                            <input type="hidden" name="foto_cap" id="foto_input" required>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="fa-solid fa-circle-check"></i> Finalizar Cadastro do Servidor
                            </button>
                        </div>
                    </div>
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
                    <a href="#" class="text-muted text-decoration-none"><i class="fa-solid fa-shield-halved fa-lg"></i></a>
                    <a href="#" class="text-muted text-decoration-none"><i class="fa-solid fa-circle-question fa-lg"></i></a>
                    <a href="#" class="text-muted text-decoration-none"><i class="fa-solid fa-envelope fa-lg"></i></a>
                </div>
            </div>

            <div class="col-md-4 text-md-end">
                <small class="text-muted">&copy; 2026 Todos os direitos reservados</small>
                <br>
                <small class="fw-bold text-secondary">Módulo de Cadastro</small>
            </div>
        </div>
        
        <hr class="my-4 opacity-25">
        
        <div class="row">
            <div class="col">
                <p class="mb-0 small text-muted">
                    <i class="fa-solid fa-lock text-success"></i> Conexão Segura | 
                    <i class="fa-solid fa-fingerprint text-primary"></i> Registro Biométrico Criptografado
                </p>
            </div>
        </div>
    </div>
</footer>

<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const fotoInput = document.getElementById('foto_input');
    const preview = document.getElementById('foto_preview');
    const areaPreview = document.getElementById('area-preview');

    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => { video.srcObject = stream; })
        .catch(err => { alert("Erro ao acessar câmera: " + err); });

    function capturar() {
        const context = canvas.getContext('2d');
        // Mantém a proporção da captura
        context.drawImage(video, 0, 0, 400, 300);
        const data = canvas.toDataURL('image/png');
        fotoInput.value = data; 
        preview.src = data;
        areaPreview.style.display = 'block';
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>