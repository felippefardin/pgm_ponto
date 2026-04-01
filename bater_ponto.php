<?php
session_start();
include 'db.php';

// Busca a cerca geográfica permitida
$stmt = $pdo->prepare("SELECT latitude, longitude, raio_metros FROM cercas_geograficas LIMIT 1");
$stmt->execute();
$cerca = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Bater Ponto - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <style>
        #video { width: 100%; max-width: 400px; border-radius: 15px; border: 5px solid #ccc; transform: scaleX(-1); }
        .area-bloqueada { display: none; }
        .btn-ponto { height: 80px; font-size: 1.2rem; font-weight: bold; }
    </style>
</head>
<body class="bg-dark text-white">

<div class="container py-5 text-center">
    <h2 class="mb-4">Controle de Ponto Biométrico</h2>
    
    <div id="status_gps" class="alert alert-warning">Verificando sua localização...</div>

    <div id="area_ponto" class="area-bloqueada">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <video id="video" autoplay muted playsinline></video>
                <canvas id="canvas" style="display:none;" width="400" height="300"></canvas>
                
                <div class="mt-3">
                    <input type="text" id="matricula" class="form-control form-control-lg mb-3 text-center" placeholder="Digite sua Matrícula">
                </div>

                <div class="d-grid gap-2 d-md-block">
                    <button id="btn-entrada" onclick="registrarPonto('entrada')" class="btn btn-success btn-ponto col-md-3">Entrada</button>
                    <button id="btn-pause" onclick="registrarPonto('pause')" class="btn btn-warning btn-ponto col-md-3">Pausa</button>
                    <button id="btn-saida" onclick="registrarPonto('saida')" class="btn btn-danger btn-ponto col-md-3">Saída</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let userLat, userLng;
    const centroLat = <?= $cerca['latitude'] ?? 0 ?>;
    const centroLng = <?= $cerca['longitude'] ?? 0 ?>;
    const raioPermitido = <?= $cerca['raio_metros'] ?? 100 ?>;

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
    navigator.geolocation.getCurrentPosition(pos => {
        userLat = pos.coords.latitude;
        userLng = pos.coords.longitude;
        const distancia = calcularDistancia(userLat, userLng, centroLat, centroLng);
        
        if (distancia <= raioPermitido) {
            document.getElementById('status_gps').className = "alert alert-success";
            document.getElementById('status_gps').innerText = "Localização autorizada!";
            document.getElementById('area_ponto').style.display = "block";
            iniciarCamera();
        } else {
            document.getElementById('status_gps').className = "alert alert-danger";
            document.getElementById('status_gps').innerText = "Fora da área permitida (" + Math.round(distancia) + "m).";
        }
    }, err => {
        document.getElementById('status_gps').innerText = "Erro ao obter GPS. Verifique as permissões.";
    });

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