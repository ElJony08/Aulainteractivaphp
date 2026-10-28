<?php
// Panel del cliente. Todos abren el mismo enlace: cliente.php
// Cada dispositivo se identifica solo con una cookie.
require __DIR__ . '/lib.php';

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
    $cid = id_cliente_cookie();

    // Botón "Salir": se quita su color de la mezcla y se libera su cookie
    if (($_POST['accion'] ?? '') === 'salir') {
        if ($cid) {
            quitar_cliente($cid);
        }
        borrar_cookie();
        header('Location: cliente.php?fin=1');
        exit;
    }

    // Elegir color. Nunca confiar en lo que llega del navegador: solo #rrggbb
    $color = $_POST['color'] ?? '';
    if (color_valido($color)) {
        $id = $cid ?: nuevo_id();
        if (guardar_color($id, strtolower($color))) {
            poner_cookie($id);   // también renueva la caducidad
        } else {
            header('Location: cliente.php?lleno=1');
            exit;
        }
    }
    header('Location: cliente.php');
    exit;
}

$cid = id_cliente_cookie();
$mio = $cid ? (leer_estado()[$cid] ?? null) : null;
$fin = isset($_GET['fin']);
$lleno = isset($_GET['lleno']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Elige tu color</title>
    <style>
        body { font-family: sans-serif; margin: 20px; max-width: 520px; }
        .actual { display: inline-block; width: 28px; height: 28px; border: 1px solid #000; vertical-align: middle; margin-left: 8px; }
        button { color: #fff; border: 2px solid #888; padding: 14px 18px; margin: 4px; font-size: 16px; cursor: pointer; }
        .claro { color: #000; }
        .otro, .salir { margin-top: 28px; }
        input[type=color] { width: 70px; height: 44px; vertical-align: middle; }
        .aviso { background: #fff3cd; border: 1px solid #e0c36a; padding: 10px 14px; }
    </style>
</head>
<body>
<?php if ($fin): ?>
    <h1>Has salido</h1>
    <p>Tu color ya no cuenta en la mezcla. Puedes cerrar esta pestaña.</p>
    <p><a href="cliente.php">Volver a entrar</a></p>
<?php else: ?>
    <h1>Elige tu color</h1>
    <?php if ($lleno): ?>
        <p class="aviso">Ahora mismo hay demasiados participantes. Prueba de nuevo en un rato.</p>
    <?php endif; ?>
    <p>Tu color:
        <?php if ($mio): ?>
            <span class="actual" style="background:<?= htmlspecialchars($mio) ?>"></span>
        <?php else: ?>
            todavía no has elegido (no cuentas en la mezcla)
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

    <?php if ($mio): ?>
        <form method="post" class="salir">
            <input type="hidden" name="accion" value="salir">
            <button style="background:#b00020">Salir</button>
        </form>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>
