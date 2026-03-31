<?php
session_start();
include 'db.php';

// Proteção: Garante que apenas o ADM logado acesse e define o ID correto
if (!isset($_SESSION['adm_id'])) {
    header("Location: index.php");
    exit();
}

$adm_id = $_SESSION['adm_id'];

// 1. Busca a cerca atual vinculada ao ID do ADM logado
$stmt = $pdo->prepare("SELECT * FROM cercas_geograficas WHERE instituicao_id = ? LIMIT 1");
$stmt->execute([$adm_id]);
$cerca = $stmt->fetch();

// 2. Processa a gravação ou atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $raio = $_POST['raio'];

    if ($cerca) {
        // Atualiza a cerca existente do ADM logado
        $sql = "UPDATE cercas_geograficas SET latitude = ?, longitude = ?, raio_metros = ? WHERE instituicao_id = ?";
        $pdo->prepare($sql)->execute([$lat, $lng, $raio, $adm_id]);
    } else {
        // Insere uma nova cerca vinculada ao ID correto do ADM
        $sql = "INSERT INTO cercas_geograficas (instituicao_id, latitude, longitude, raio_metros) VALUES (?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$adm_id, $lat, $lng, $raio]);
    }
    echo "<script>alert('Cerca geográfica atualizada com sucesso!'); window.location='dashboard.php';</script>";
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
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Definir Área Permitida (Geofencing)</h5>
            <a href="dashboard.php" class="btn btn-sm btn-light">Voltar</a>
        </div>
        <div class="card-body">
            <p class="text-muted">Clique no mapa para definir o ponto central da sua unidade.</p>
            <div id="map" class="mb-3 border"></div>
            
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Latitude</label>
                        <input type="text" name="lat" id="lat" class="form-control" value="<?= $cerca['latitude'] ?? '' ?>" readonly required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Longitude</label>
                        <input type="text" name="lng" id="lng" class="form-control" value="<?= $cerca['longitude'] ?? '' ?>" readonly required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Raio de Alcance (metros)</label>
                        <input type="number" name="raio" id="raio" class="form-control" value="<?= $cerca['raio_metros'] ?? '100' ?>" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-success mt-4 w-100">
                    <i class="fa-solid fa-map-pin"></i> Salvar Localização de Trabalho
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Localização inicial (Usa a salva ou Serra Sede como padrão)
    var initialLat = <?= $cerca['latitude'] ?? -20.1264 ?>;
    var initialLng = <?= $cerca['longitude'] ?? -40.3078 ?>;
    var initialRaio = <?= $cerca['raio_metros'] ?? 100 ?>;

    var map = L.map('map').setView([initialLat, initialLng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    var marker = L.marker([initialLat, initialLng], {draggable: true}).addTo(map);
    var circle = L.circle([initialLat, initialLng], { radius: initialRaio, color: 'red' }).addTo(map);

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
</body>
</html>