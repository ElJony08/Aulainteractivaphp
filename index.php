<?php
// Pantalla de proyector: muestra a pantalla completa el color que toca.
// Uso: pantalla.php?p=1  (o p=2, p=3)
$p = $_GET['p'] ?? '1';
$archivo = __DIR__ . '/estado.json';
$estado = file_exists($archivo) ? (json_decode(file_get_contents($archivo), true) ?: []) : [];
$color = $estado[$p] ?? '#000000';

// Evitamos que el navegador guarde la página en caché
header('Cache-Control: no-store');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="1">
    <title>Pantalla <?= htmlspecialchars($p) ?></title>
    <style>
        html, body { margin: 0; height: 100%; background: <?= htmlspecialchars($color) ?>; }
    </style>
</head>
<body></body>
</html>
