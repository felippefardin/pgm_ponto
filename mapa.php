<?php
session_start();
include 'db.php';

// Busca a cerca atual se já existir
$stmt = $pdo->prepare("SELECT * FROM cercas_geograficas WHERE instituicao_id = 1 LIMIT 1");
$stmt->execute();
$cerca = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $raio = $_POST['raio'];

    if ($cerca) {
        $sql = "UPDATE cercas_geograficas SET latitude = ?, longitude = ?, raio_metros = ? WHERE id = ?";
        $pdo->prepare($sql)->execute([$lat, $lng, $raio, $cerca['id']]);
    } else {
        $sql = "INSERT INTO cercas_geograficas (instituicao_id, latitude, longitude, raio_metros) VALUES (1, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$lat, $lng, $raio]);
    }
    echo "<script>alert('Cerca geográfica atualizada!'); window.location='dashboard.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Configurar Mapa - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 500px; width: 100%; border-radius: 15px; cursor: crosshair; }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="card shadow">
        <div class="card-header bg-secondary text-white d-flex justify-content-between">
            <h5>Definir Área Permitida para Batida de Ponto</h5>
            <a href="dashboard.php" class="btn btn-sm btn-light">Voltar</a>
        </div>
        <div class="card-body">
            <p class="text-muted">Clique no mapa para definir o centro da instituição e ajuste o raio abaixo.</p>
            <div id="map" class="mb-3"></div>
            
            <form method="POST">
                <div class="row">
                    <div class="col-md-4">
                        <label>Latitude</label>
                        <input type="text" name="lat" id="lat" class="form-control" value="<?= $cerca['latitude'] ?? '' ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>Longitude</label>
                        <input type="text" name="lng" id="lng" class="form-control" value="<?= $cerca['longitude'] ?? '' ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>Raio de Alcance (metros)</label>
                        <input type="number" name="raio" id="raio" class="form-control" value="<?= $cerca['raio_metros'] ?? '100' ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-success mt-3 w-100">Salvar Localização</button>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Inicializa o mapa (Padrão Vila Velha se não houver dados)
    var initialLat = <?= $cerca['latitude'] ?? -20.3297 ?>;
    var initialLng = <?= $cerca['longitude'] ?? -40.2944 ?>;
    var initialRaio = <?= $cerca['raio_metros'] ?? 100 ?>;

    var map = L.map('map').setView([initialLat, initialLng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var marker = L.marker([initialLat, initialLng], {draggable: true}).addTo(map);
    var circle = L.circle([initialLat, initialLng], { radius: initialRaio }).addTo(map);

    function updateInputs(lat, lng) {
        document.getElementById('lat').value = lat.toFixed(8);
        document.getElementById('lng').value = lng.toFixed(8);
    }

    map.on('click', function(e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;
        marker.setLatLng(e.latlng);
        circle.setLatLng(e.latlng);
        updateInputs(lat, lng);
    });

    document.getElementById('raio').addEventListener('input', function() {
        circle.setRadius(this.value);
    });
</script>
</body>
</html>