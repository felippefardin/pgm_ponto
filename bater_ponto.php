<?php
session_start();
include 'db.php';
require_once 'localizacao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Bater Ponto - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <style>
        #video { width: 100%; max-width: 400px; border-radius: 15px; border: 5px solid #ccc; transform: scaleX(-1); }
        .area-bloqueada { display: none; }
        .btn-ponto { height: 80px; font-size: 1.2rem; font-weight: bold; }
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
<body class="bg-dark text-white">

<nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">
            <i class="fa-solid fa-tower-broadcast"></i> Monitoramento ao Vivo
        </span>                  
        <div class="d-flex gap-2">
            <a href="login_servidor.php" class="btn btn-light btn-sm"><i class="fa-solid fa-user me-1"></i>Meus pontos</a>
            <a href="index.php" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-user-shield me-1"></i>Administrador</a>
        </div>
    </div>
</nav>

<div class="container py-5 text-center">

    <h2 class="mb-4">Controle de Ponto Biométrico</h2>
    
    <div class="row justify-content-center mb-3">
        <div class="col-md-6">
            <input type="text" id="matricula" class="form-control form-control-lg mb-2 text-center" placeholder="Digite sua Matrícula">
            <button type="button" id="btn-validar-local" onclick="validarLocalizacao()" class="btn btn-primary w-100">
                <i class="fa-solid fa-location-crosshairs me-2"></i>Validar localização
            </button>
        </div>
    </div>
    <div id="status_gps" class="alert alert-warning">Informe sua matrícula para validar a localização.</div>

    <div id="area_ponto" class="area-bloqueada">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <video id="video" autoplay muted playsinline></video>
                <canvas id="canvas" style="display:none;" width="400" height="300"></canvas>
                
                <div class="d-grid gap-2 d-md-block">
                    <button id="btn-entrada" onclick="registrarPonto('entrada')" class="btn btn-success btn-ponto col-md-3">Entrada</button>
                    <button id="btn-pause" onclick="registrarPonto('pausa')" class="btn btn-warning btn-ponto col-md-3">Pausa</button>
                    <button id="btn-saida" onclick="registrarPonto('saida')" class="btn btn-danger btn-ponto col-md-3">Saída</button>
                </div>
            </div>
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



<script>
    let userLat, userLng;
    let centroLat, centroLng, raioPermitido;

    // 1. CARREGAMENTO DOS MODELOS COM FEEDBACK
    async function carregarModelos() {
        try {
            const MODEL_URL = './models'; // Caminho relativo
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]);
            console.log("Modelos de IA carregados com sucesso!");
        } catch (err) {
            console.error("Erro crítico ao carregar modelos:", err);
            Swal.fire('Erro de Sistema', 'Arquivos de biometria não encontrados ou corrompidos.', 'error');
        }
    }
    carregarModelos();

    // 2. GEOLOCALIZAÇÃO
    async function validarLocalizacao() {
        const matricula = document.getElementById('matricula').value.trim();
        const status = document.getElementById('status_gps');
        const botao = document.getElementById('btn-validar-local');
        document.getElementById('area_ponto').style.display = 'none';
        if (!matricula) return Swal.fire('Aviso', 'Informe sua matrícula.', 'warning');

        botao.disabled = true;
        status.className = 'alert alert-warning';
        status.innerText = 'Obtendo configuração e localização...';
        try {
            const resposta = await fetch('configuracao_localizacao.php?matricula=' + encodeURIComponent(matricula));
            const config = await resposta.json();
            if (!config.success) throw new Error(config.message);
            centroLat = config.latitude;
            centroLng = config.longitude;
            raioPermitido = config.raio_metros;

            navigator.geolocation.getCurrentPosition(pos => {
                userLat = pos.coords.latitude;
                userLng = pos.coords.longitude;
                const distancia = calcularDistancia(userLat, userLng, centroLat, centroLng);
                if (distancia <= raioPermitido) {
                    status.className = 'alert alert-success';
                    status.innerText = 'Localização autorizada! Distância aproximada: ' + Math.round(distancia) + ' m.';
                    document.getElementById('area_ponto').style.display = 'block';
                    iniciarCamera();
                } else {
                    status.className = 'alert alert-danger';
                    status.innerText = 'Fora da área permitida (' + Math.round(distancia) + ' m; limite de ' + raioPermitido + ' m).';
                }
                botao.disabled = false;
            }, () => {
                status.className = 'alert alert-danger';
                status.innerText = 'Não foi possível obter o GPS. Verifique a permissão de localização.';
                botao.disabled = false;
            }, {enableHighAccuracy: true, timeout: 15000, maximumAge: 0});
        } catch (erro) {
            status.className = 'alert alert-danger';
            status.innerText = erro.message || 'Não foi possível validar a localização.';
            botao.disabled = false;
        }
    }

    function iniciarCamera() {
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => { document.getElementById('video').srcObject = stream; })
            .catch(err => Swal.fire('Erro', 'Câmera não disponível.', 'error'));
    }

    function calcularDistancia(lat1, lon1, lat2, lon2) {
        const R = 6371e3;
        const φ1 = lat1 * Math.PI/180;
        const φ2 = lat2 * Math.PI/180;
        const Δφ = (lat2-lat1) * Math.PI/180;
        const Δλ = (lon2-lon1) * Math.PI/180;
        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) + Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ/2) * Math.sin(Δλ/2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

    // 3. REGISTRO DE PONTO COM TRATAMENTO DE TRAVAMENTO
    async function registrarPonto(tipo) {
        const matricula = document.getElementById('matricula').value;
        if (!matricula) return Swal.fire('Aviso', 'Digite sua matrícula primeiro.', 'warning');

        const video = document.getElementById('video');

        // Mostra o loading que você viu na imagem
        Swal.fire({ 
            title: 'Validando...', 
            text: 'Aguarde o reconhecimento facial',
            allowOutsideClick: false, 
            didOpen: () => { Swal.showLoading(); } 
        });

        try {
            // BUSCA FOTO NO SERVIDOR
            const resp = await fetch(`obter_foto_servidor.php?matricula=${matricula}`);
            const dados = await resp.json();

            if (!dados.success || !dados.face_token) {
                return Swal.fire('Erro', 'Matrícula não encontrada ou sem foto.', 'error');
            }

            // DETECÇÃO NA CÂMERA (Adicionado timeout manual para não travar)
            const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 });
            
            const deteccaoAtual = await faceapi.detectSingleFace(video, options)
                                               .withFaceLandmarks()
                                               .withFaceDescriptor();

            if (!deteccaoAtual) {
                return Swal.fire('Rosto não detectado', 'Aproxime-se da câmera e tente novamente.', 'error');
            }

            // COMPARAÇÃO COM FOTO REGISTRADA
            const imgCadastro = await faceapi.fetchImage(dados.face_token);
            const deteccaoCadastro = await faceapi.detectSingleFace(imgCadastro, options)
                                                  .withFaceLandmarks()
                                                  .withFaceDescriptor();

            if (!deteccaoCadastro) {
                return Swal.fire('Erro na Foto Base', 'Não conseguimos processar sua foto de cadastro.', 'error');
            }

            const distancia = faceapi.euclideanDistance(deteccaoAtual.descriptor, deteccaoCadastro.descriptor);

            if (distancia > 0.6) {
                return Swal.fire('Acesso Negado', 'Identidade não confirmada!', 'error');
            }

            // ENVIO PARA O PHP REGISTRAR NO BANCO
            const canvas = document.getElementById('canvas');
            canvas.getContext('2d').drawImage(video, 0, 0, 400, 300);
            
            const formData = new FormData();
            formData.append('tipo', tipo);
            formData.append('matricula', matricula);
            formData.append('foto', canvas.toDataURL('image/png'));
            formData.append('lat', userLat);
            formData.append('lng', userLng);

            const finalResp = await fetch('processar_batida.php', { method: 'POST', body: formData });
            const finalResult = await finalResp.json();

            Swal.fire(finalResult.success ? 'Sucesso' : 'Erro', finalResult.message, finalResult.success ? 'success' : 'error');

        } catch (error) {
            console.error(error);
            Swal.fire('Falha Técnica', 'Ocorreu um erro no processamento. Verifique sua conexão.', 'error');
        }
    }
</script>
</body>
</html>
