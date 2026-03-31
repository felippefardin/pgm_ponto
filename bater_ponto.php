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
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
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
                <video id="video" autoplay></video>
                <canvas id="canvas" style="display:none;" width="400" height="300"></canvas>
                
                <div class="mt-3">
                    <input type="text" id="matricula" class="form-control form-control-lg mb-3 text-center" placeholder="Digite sua Matrícula">
                </div>

                <div class="d-grid gap-2 d-md-block">
                    <button onclick="registrarPonto('entrada')" class="btn btn-success btn-ponto col-md-3">Entrada</button>
                    <button onclick="registrarPonto('pause')" class="btn btn-warning btn-ponto col-md-3">Pausa</button>
                    <button onclick="registrarPonto('saida')" class="btn btn-danger btn-ponto col-md-3">Saída</button>
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

    async function carregarModelos() {
        try {
            const MODEL_URL = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights';
            await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
            await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
            console.log("Sistemas de Biometria Prontos!");
        } catch (err) {
            console.error("Erro ao carregar modelos: ", err);
        }
    }
    carregarModelos();

    // Lógica de GPS (Mantida)
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
            document.getElementById('status_gps').innerText = "Fora da área permitida.";
        }
    });

    function iniciarCamera() {
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => { document.getElementById('video').srcObject = stream; });
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

    // --- NOVA LÓGICA: MATRÍCULA PRIMEIRO, BIOMETRIA DEPOIS ---
    async function registrarPonto(tipo) {
        const matricula = document.getElementById('matricula').value;
        if (!matricula) return Swal.fire('Aviso', 'Digite sua matrícula antes de continuar.', 'warning');

        const video = document.getElementById('video');

        // PASSO 1: Verificar se a matrícula existe e buscar a foto de cadastro
        Swal.fire({ title: 'Verificando matrícula...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        try {
            const resp = await fetch(`obter_foto_servidor.php?matricula=${matricula}`);
            const dados = await resp.json();

            // Se a matrícula não for encontrada ou não tiver foto, para aqui
            if (!dados.success || !dados.face_token) {
                return Swal.fire('Erro', 'Matrícula não encontrada ou sem biometria cadastrada.', 'error');
            }

            // PASSO 2: Se a matrícula existir, agora sim iniciamos a leitura da biometria
            Swal.update({ title: 'Aguarde... Lendo biometria facial' });

            // Detecção do rosto na câmera
            const deteccaoAtual = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                                               .withFaceLandmarks()
                                               .withFaceDescriptor();

            if (!deteccaoAtual) {
                return Swal.fire('Rosto não detectado', 'Por favor, olhe para a câmera e tente novamente.', 'error');
            }

            // Comparação com a foto que acabamos de receber da matrícula validada
            const imgCadastro = await faceapi.fetchImage(dados.face_token);
            const deteccaoCadastro = await faceapi.detectSingleFace(imgCadastro, new faceapi.TinyFaceDetectorOptions())
                                                  .withFaceLandmarks()
                                                  .withFaceDescriptor();

            if (!deteccaoCadastro) {
                return Swal.fire('Erro Técnico', 'Não foi possível processar a imagem de cadastro original.', 'error');
            }

            const distancia = faceapi.euclideanDistance(deteccaoAtual.descriptor, deteccaoCadastro.descriptor);

            if (distancia > 0.6) {
                return Swal.fire('Acesso Negado', 'Biometria não confere com o titular da matrícula!', 'error');
            }

            // PASSO 3: Se tudo estiver OK, registra o ponto no banco
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
            Swal.fire('Falha no Sistema', 'Erro ao processar validação. Tente novamente.', 'error');
        }
    }
</script>
</body>
</html>