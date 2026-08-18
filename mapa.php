<?php
session_start();
include 'db.php';
require_once 'localizacao.php';

// Proteção: Garante que apenas o ADM logado acesse e define o ID correto
if (!isset($_SESSION['adm_id'])) {
    header("Location: index.php");
    exit();
}

$adm_id = (int) $_SESSION['adm_id'];
$stmt = $pdo->prepare("SELECT latitude, longitude, raio_metros FROM cercas_geograficas WHERE instituicao_id = ? LIMIT 1");
$stmt->execute([$adm_id]);
$cerca = $stmt->fetch() ?: [
    'latitude' => LOCALIZACAO_LATITUDE,
    'longitude' => LOCALIZACAO_LONGITUDE,
    'raio_metros' => LOCALIZACAO_RAIO_METROS,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lat = filter_var($_POST['lat'] ?? null, FILTER_VALIDATE_FLOAT);
    $lng = filter_var($_POST['lng'] ?? null, FILTER_VALIDATE_FLOAT);
    $raio = filter_var($_POST['raio'] ?? null, FILTER_VALIDATE_INT);

    if ($lat === false || $lng === false || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180
        || $raio === false || $raio < 10 || $raio > 5000) {
        $erro = 'Informe uma localização válida e um raio entre 10 e 5.000 metros.';
    } else {
        $sql = "INSERT INTO cercas_geograficas (instituicao_id, latitude, longitude, raio_metros)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE latitude = VALUES(latitude), longitude = VALUES(longitude), raio_metros = VALUES(raio_metros)";
        $pdo->prepare($sql)->execute([$adm_id, $lat, $lng, $raio]);
        header('Location: mapa.php?salvo=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Mapa - PMG PONTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
        #map { 
            height: 450px; 
            width: 100%; 
            border-radius: 12px; 
            cursor: crosshair; 
            box-shadow: inset 0 0 10px rgba(0,0,0,0.1);
            border: 2px solid #dee2e6;
        }
        .form-label { font-weight: bold; color: #495057; }
    </style>
</head>
<body class="bg-light">

<div class="main-content">
    <nav class="navbar navbar-dark bg-secondary mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fa-solid fa-map-location-dot"></i> CONFIGURAÇÃO DE CERCA GEOGRÁFICA
            </span>                  
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel
            </a>
        </div>
    </nav>

    <div class="container py-2">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-secondary fw-bold">
                    <i class="fa-solid fa-earth-americas me-2"></i>Definir Perímetro de Batida
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info d-flex align-items-center" role="alert">
                    <i class="fa-solid fa-circle-info me-3 fa-lg"></i>
                    <div>
                        Clique no mapa ou arraste o marcador para definir o centro. Ajuste abaixo a distância permitida.
                    </div>
                </div>

                <div id="map" class="mb-4"></div>
                
                <?php if (isset($_GET['salvo'])): ?><div class="alert alert-success">Localização e distância atualizadas com sucesso.</div><?php endif; ?>
                <?php if (!empty($erro)): ?><div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
                <form method="POST">
                    <div class="row g-3 p-3 bg-white border rounded shadow-sm">
                        <div class="col-md-4">
                            <label class="form-label">Latitude</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-location-crosshairs"></i></span>
                                <input type="text" name="lat" id="lat" class="form-control bg-light" value="<?= $cerca['latitude'] ?? '' ?>" readonly required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Longitude</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-location-dot"></i></span>
                                <input type="text" name="lng" id="lng" class="form-control bg-light" value="<?= $cerca['longitude'] ?? '' ?>" readonly required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Raio de Alcance (metros)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fa-solid fa-ruler-combined"></i></span>
                                <input type="number" name="raio" id="raio" class="form-control" min="10" max="5000" value="<?= (int) $cerca['raio_metros'] ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid mt-3">
                        <button type="submit" class="btn btn-success btn-lg"><i class="fa-solid fa-floppy-disk me-2"></i>Salvar localização permitida</button>
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
                    <a href="#" class="text-muted text-decoration-none" title="Segurança"><i class="fa-solid fa-shield-halved fa-lg"></i></a>
                    <a href="#" class="text-muted text-decoration-none" title="Ajuda"><i class="fa-solid fa-circle-question fa-lg"></i></a>
                    <a href="#" class="text-muted text-decoration-none" title="Suporte"><i class="fa-solid fa-envelope fa-lg"></i></a>
                </div>
            </div>

            <div class="col-md-4 text-md-end">
                <small class="text-muted">&copy; 2026 Todos os direitos reservados</small>
                <br>
                <small class="fw-bold text-secondary">Módulo de Geolocalização</small>
            </div>
        </div>
        
        <hr class="my-4 opacity-25">
        
        <div class="row">
            <div class="col">
                <p class="mb-0 small text-muted">
                    <i class="fa-solid fa-lock text-success"></i> Conexão Segura | 
                    <i class="fa-solid fa-satellite text-primary"></i> Precisão GPS via OpenStreetMap
                </p>
            </div>
        </div>
    </div>
</footer>

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
    var circle = L.circle([initialLat, initialLng], { 
        radius: initialRaio, 
        color: '#dc3545', 
        fillColor: '#dc3545', 
        fillOpacity: 0.2 
    }).addTo(map);

    function updateInputs(lat, lng) {
        document.getElementById('lat').value = lat.toFixed(8);
        document.getElementById('lng').value = lng.toFixed(8);
    }

    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        circle.setLatLng(e.latlng);
        updateInputs(e.latlng.lat, e.latlng.lng);
    });

    marker.on('dragend', function() {
        var posicao = marker.getLatLng();
        circle.setLatLng(posicao);
        updateInputs(posicao.lat, posicao.lng);
    });

    document.getElementById('raio').addEventListener('input', function() {
        circle.setRadius(Number(this.value) || 10);
    });

</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
