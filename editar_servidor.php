<?php
session_start();
include 'db.php';

// 1. Carregar os dados do servidor para o formulário
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM servidores WHERE id = ?");
    $stmt->execute([$id]);
    $servidor = $stmt->fetch();

    if (!$servidor) {
        die("Servidor não encontrado.");
    }
}

// 2. Processar a atualização dos dados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome_completo'];
    $matricula = $_POST['matricula'];
    $pin = $_POST['pin'];
    $foto_nova = $_POST['foto_cap']; // Se estiver vazio, mantém a antiga

    if (!empty($foto_nova)) {
        // Atualiza tudo, incluindo a nova foto
        $sql = "UPDATE servidores SET nome_completo = ?, matricula = ?, pin = ?, face_token = ? WHERE id = ?";
        $pdo->prepare($sql)->execute([$nome, $matricula, $pin, $foto_nova, $id]);
    } else {
        // Atualiza apenas os campos de texto
        $sql = "UPDATE servidores SET nome_completo = ?, matricula = ?, pin = ? WHERE id = ?";
        $pdo->prepare($sql)->execute([$nome, $matricula, $pin, $id]);
    }

    echo "<script>alert('Dados atualizados com sucesso!'); window.location='gerenciar_servidores.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Servidor - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #video { width: 100%; max-width: 320px; border-radius: 10px; background: #000; }
        .preview-img { width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 2px solid #ffc107; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow mx-auto" style="max-width: 850px;">
        <div class="card-header bg-warning text-dark"><h5>Editar Cadastro: <?= htmlspecialchars($servidor['nome_completo']) ?></h5></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="id" value="<?= $servidor['id'] ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Nome Completo</label>
                            <input type="text" name="nome_completo" class="form-control" value="<?= htmlspecialchars($servidor['nome_completo']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Matrícula</label>
                            <input type="text" name="matricula" class="form-control" value="<?= htmlspecialchars($servidor['matricula']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>PIN (6 dígitos)</label>
                            <input type="password" name="pin" maxlength="6" class="form-control" value="<?= htmlspecialchars($servidor['pin']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6 text-center border-start">
                        <label class="fw-bold">Atualizar Biometria (Opcional)</label><br>
                        <video id="video" autoplay></video>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="capturar()">Tirar Nova Foto</button>
                        
                        <div class="mt-3">
                            <p class="small text-muted">Foto Atual / Nova:</p>
                            <img id="foto_preview" src="<?= $servidor['face_token'] ?>" class="preview-img">
                            <canvas id="canvas" style="display:none;" width="400" height="300"></canvas>
                        </div>
                        <input type="hidden" name="foto_cap" id="foto_input">
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <a href="gerenciar_servidores.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const fotoInput = document.getElementById('foto_input');
    const preview = document.getElementById('foto_preview');

    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => { video.srcObject = stream; })
        .catch(err => { console.log("Câmera não disponível"); });

    function capturar() {
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, 400, 300);
        const data = canvas.toDataURL('image/png');
        fotoInput.value = data;
        preview.src = data;
    }
</script>
</body>
</html>