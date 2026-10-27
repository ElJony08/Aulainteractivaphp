<?php
// Panel de control: elige el color de cada pantalla (proyector).
$archivo = __DIR__ . '/estado.json';

$porDefecto = ['1' => '#000000', '2' => '#000000', '3' => '#000000'];
$estado = file_exists($archivo)
    ? (json_decode(file_get_contents($archivo), true) ?: $porDefecto)
    : $porDefecto;

$colores = [
    'Rojo'     => '#e63946',
    'Azul'     => '#1d3557',
    'Verde'    => '#2a9d8f',
    'Amarillo' => '#ffd166',
    'Blanco'   => '#ffffff',
    'Negro'    => '#000000',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pantalla = $_POST['pantalla'] ?? '';
    $color    = $_POST['color'] ?? '';

    // Solo aceptamos colores de la lista (nunca confiar en lo que llega del navegador)
    if (in_array($color, $colores, true)) {
        if ($pantalla === 'todas') {
            foreach ($estado as $k => $_) {
                $estado[$k] = $color;
            }
        } elseif (isset($estado[$pantalla])) {
            $estado[$pantalla] = $color;
        }
        file_put_contents($archivo, json_encode($estado), LOCK_EX);
    }
    header('Location: control.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Control de pantallas</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        h3 { margin: 24px 0 8px; }
        button { color: #fff; border: 2px solid #888; padding: 12px 18px; margin: 4px; font-size: 16px; cursor: pointer; }
        .claro { color: #000; }
        .actual { display: inline-block; width: 18px; height: 18px; border: 1px solid #000; vertical-align: middle; margin-left: 8px; }
    </style>
</head>
<body>
<h1>Control de pantallas</h1>
<?php foreach (['1', '2', '3', 'todas'] as $p): ?>
    <h3>
        <?= $p === 'todas' ? 'Todas las pantallas' : 'Pantalla ' . $p ?>
        <?php if ($p !== 'todas'): ?>
            <span class="actual" style="background:<?= htmlspecialchars($estado[$p] ?? '#000000') ?>"></span>
        <?php endif; ?>
    </h3>
    <?php foreach ($colores as $nombre => $hex): ?>
        <form method="post" style="display:inline">
            <input type="hidden" name="pantalla" value="<?= $p ?>">
            <input type="hidden" name="color" value="<?= $hex ?>">
            <button class="<?= in_array($hex, ['#ffffff', '#ffd166'], true) ? 'claro' : '' ?>"
                    style="background:<?= $hex ?>"><?= $nombre ?></button>
        </form>
    <?php endforeach; ?>
<?php endforeach; ?>
</body>
</html>
