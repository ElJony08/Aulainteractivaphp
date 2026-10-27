<?php
// Funciones compartidas: leer/guardar el estado y mezclar los colores.
const ARCHIVO_ESTADO = __DIR__ . '/estado.json';
const ESTADO_VACIO = ['1' => null, '2' => null, '3' => null];

function color_valido($color) {
    return is_string($color) && preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1;
}

function leer_estado() {
    if (!file_exists(ARCHIVO_ESTADO)) {
        return ESTADO_VACIO;
    }
    $estado = json_decode(file_get_contents(ARCHIVO_ESTADO), true);
    return is_array($estado) ? ($estado + ESTADO_VACIO) : ESTADO_VACIO;
}

// Lee, modifica y escribe con bloqueo, para que dos clientes a la vez no se pisen.
function guardar_color($cliente, $color) {
    $fp = fopen(ARCHIVO_ESTADO, 'c+');
    flock($fp, LOCK_EX);
    $estado = json_decode(stream_get_contents($fp), true);
    $estado = is_array($estado) ? ($estado + ESTADO_VACIO) : ESTADO_VACIO;
    $estado[$cliente] = $color;
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($estado));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
}

function reiniciar_estado() {
    file_put_contents(ARCHIVO_ESTADO, json_encode(ESTADO_VACIO), LOCK_EX);
}

// Mezcla: media de los canales RGB de los colores elegidos.
// Si todavía nadie ha elegido, devuelve negro.
function mezclar($estado) {
    $elegidos = array_filter($estado, 'color_valido');
    if (!$elegidos) {
        return '#000000';
    }
    $r = $g = $b = 0;
    foreach ($elegidos as $hex) {
        $r += hexdec(substr($hex, 1, 2));
        $g += hexdec(substr($hex, 3, 2));
        $b += hexdec(substr($hex, 5, 2));
    }
    $n = count($elegidos);
    return sprintf('#%02x%02x%02x', round($r / $n), round($g / $n), round($b / $n));
}
