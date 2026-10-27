<?php
// Pantalla de los proyectores: muestra SOLO el color resultante de la mezcla.
require __DIR__ . '/lib.php';

$color = mezclar(leer_estado());

header('Cache-Control: no-store');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="1">
    <title>Resultado</title>
    <style>
        html, body { margin: 0; height: 100%; background: <?= htmlspecialchars($color) ?>; }
    </style>
</head>
<body></body>
</html>
