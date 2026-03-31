<?php
session_start();
include 'db.php';

// Proteção: Garante que apenas o ADM logado consiga cadastrar e define o ID correto
if (!isset($_SESSION['adm_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome_completo'];
    $matricula = $_POST['matricula'];
    $pin = $_POST['pin'];
    $foto_base64 = $_POST['foto_cap']; // Recebe a string da imagem
    $adm_id = $_SESSION['adm_id']; // ID dinâmico da sessão atual

    // O uso do $adm_id resolve o erro de chave estrangeira (Integrity constraint violation)
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
    <title>Cadastrar Servidor - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        #video { width: 100%; max-width: 400px; border-radius: 10px; background: #000; transform: scaleX(-1); }
        #canvas { display: none; }
        .preview-img { width: 150px; height: 150px; object-fit: cover; border-radius: 10px; border: 2px solid #28a745; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow mx-auto" style="max-width: 800px;">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Novo Cadastro de Servidor</h5>
            <a href="dashboard.php" class="btn btn-sm btn-outline-light">Voltar</a>
        </div>
        <div class="card-body">
            <form method="POST" id="formCadastro">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nome Completo</label>
                            <input type="text" name="nome_completo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Matrícula</label>
                            <input type="text" name="matricula" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">PIN (6 dígitos)</label>
                            <input type="password" name="pin" maxlength="6" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6 text-center border-start">
                        <label class="fw-bold">Capturar Biometria Facial</label><br>
                        <video id="video" autoplay></video>
                        <button type="button" class="btn btn-sm btn-primary mt-2" onclick="capturar()">
                            <i class="fa-solid fa-camera"></i> Tirar Foto
                        </button>
                        <div id="area-preview" class="mt-3" style="display:none;">
                            <p class="small text-success fw-bold">Foto capturada com sucesso!</p>
                            <canvas id="canvas" width="400" height="300"></canvas>
                            <img id="foto_preview" class="preview-img shadow">
                        </div>
                        <input type="hidden" name="foto_cap" id="foto_input" required>
                    </div>
                </div>
                <hr>
                <button type="submit" class="btn btn-success w-100 py-2">
                    <i class="fa-solid fa-user-check"></i> Finalizar Cadastro
                </button>
            </form>
        </div>
    </div>
</div>

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
        context.drawImage(video, 0, 0, 400, 300);
        const data = canvas.toDataURL('image/png');
        fotoInput.value = data; 
        preview.src = data;
        areaPreview.style.display = 'block';
    }
</script>
</body>
</html>