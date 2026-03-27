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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <style>
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
                    <input type="text" id="matricula" class="form-control form-control-lg mb-2 text-center" placeholder="Sua Matrícula">
                    <input type="password" id="pin" class="form-control form-control-lg mb-3 text-center" placeholder="Seu PIN (6 dígitos)" maxlength="6">
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
    const centroLat = <?= $cerca['latitude'] ?>;
    const centroLng = <?= $cerca['longitude'] ?>;
    const raioPermitido = <?= $cerca['raio_metros'] ?>;

    // 1. Verificar GPS
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
            document.getElementById('status_gps').innerText = "Fora da área permitida (" + Math.round(distancia) + "m de distância).";
        }
    }, err => {
        alert("Erro ao obter GPS. Verifique as permissões do navegador.");
    });

    function iniciarCamera() {
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => { document.getElementById('video').srcObject = stream; });
    }

    function calcularDistancia(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // Raio da Terra em metros
        const φ1 = lat1 * Math.PI/180;
        const φ2 = lat2 * Math.PI/180;
        const Δφ = (lat2-lat1) * Math.PI/180;
        const Δλ = (lon2-lon1) * Math.PI/180;
        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) + Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ/2) * Math.sin(Δλ/2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

    async function registrarPonto(tipo) {
        const canvas = document.getElementById('canvas');
        const video = document.getElementById('video');
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, 400, 300);
        const fotoAtual = canvas.toDataURL('image/png');

        const dados = new FormData();
        dados.append('tipo', tipo);
        dados.append('matricula', document.getElementById('matricula').value);
        dados.append('pin', document.getElementById('pin').value);
        dados.append('foto', fotoAtual);
        dados.append('lat', userLat);
        dados.append('lng', userLng);

        const response = await fetch('processar_batida.php', { method: 'POST', body: dados });
        const result = await response.json();

        if (result.success) {
            Swal.fire('Sucesso!', result.message, 'success');
        } else {
            Swal.fire('Erro!', result.message, 'error');
        }
    }
</script>
</body>
</html>