<?php

const LOCALIZACAO_ENDERECO = 'Rua Maestro Antônio Cícero, 111, Caçaroca, Serra - ES, CEP 29176-439';
const LOCALIZACAO_LATITUDE = -20.12534000;
const LOCALIZACAO_LONGITUDE = -40.30581000;
const LOCALIZACAO_RAIO_METROS = 200;

function distanciaEmMetros(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    $raioTerra = 6371000;
    $lat1Rad = deg2rad($lat1);
    $lat2Rad = deg2rad($lat2);
    $deltaLat = deg2rad($lat2 - $lat1);
    $deltaLng = deg2rad($lng2 - $lng1);

    $a = sin($deltaLat / 2) ** 2
        + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLng / 2) ** 2;

    return $raioTerra * 2 * atan2(sqrt($a), sqrt(1 - $a));
}
