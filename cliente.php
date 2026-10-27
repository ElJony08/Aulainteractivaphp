<?php
// Panel de cada cliente: cliente.php?c=1, ?c=2 o ?c=3
require __DIR__ . '/lib.php';

$c = $_GET['c'] ?? '';
if (!in_array($c, ['1', '2', '3'], true)) {
    http_response_code(400);
    exit('Cliente no válido. Usa cliente.php?c=1, cliente.php?c=2 o cliente.php?c=3');
}

$paleta = [
    'Rojo'     => '#e63946',
    'Naranja'  => '#f77f00',
    'Amarillo' => '#ffd166',
    'Verde'    => '#2a9d8f',
    'Azul'     => '#1d3557',
    'Violeta'  => '#7b2cbf',
    'Blanco'   => '#ffffff',
    'Negro'    => '#000000',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $color = $_POST['color'] ?? '';
    // Nunca confiar en lo que llega del navegador: solo aceptamos #rrggbb
    if (color_valido($color)) {
        guardar_color($c, strtolower($color));
    }
    header('Location: cliente.php?c=' . $c);
    exit;
}

$mio = leer_estado()[$c];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cliente <?= $c ?></title>
    <style>
        body { font-family: sans-serif; margin: 20px; max-width: 520px; }
        .actual { display: inline-block; width: 28px; height: 28px; border: 1px solid #000; vertical-align: middle; margin-left: 8px; }
        button { color: #fff; border: 2px solid #888; padding: 14px 18px; margin: 4px; font-size: 16px; cursor: pointer; }
        .claro { color: #000; }
        .otro { margin-top: 24px; }
        input[type=color] { width: 70px; height: 44px; vertical-align: middle; }
    </style>
</head>
<body>
<h1>Cliente <?= $c ?></h1>
<p>Tu color:
    <?php if ($mio): ?>
        <span class="actual" style="background:<?= htmlspecialchars($mio) ?>"></span>
    <?php else: ?>
        todavía no has elegido
    <?php endif; ?>
</p>

<h3>Elige un color</h3>
<?php foreach ($paleta as $nombre => $hex): ?>
    <form method="post" style="display:inline">
        <input type="hidden" name="color" value="<?= $hex ?>">
        <button class="<?= in_array($hex, ['#ffffff', '#ffd166'], true) ? 'claro' : '' ?>"
                style="background:<?= $hex ?>"><?= $nombre ?></button>
    </form>
<?php endforeach; ?>

<form method="post" class="otro">
    <h3>O escoge uno cualquiera</h3>
    <input type="color" name="color" value="<?= htmlspecialchars($mio ?: '#808080') ?>">
    <button style="background:#444">Enviar</button>
</form>
</body>
</html>
