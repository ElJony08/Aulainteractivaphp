<?php
// Funciones compartidas: estado, identificación de clientes por cookie y mezcla.
const ARCHIVO_ESTADO = __DIR__ . '/estado.json';
const MAX_CLIENTES   = 50;   // tope de participantes a la vez (evita que el archivo crezca sin límite)
const COOKIE_NOMBRE  = 'cid';
const COOKIE_DURACION = 86400; // 1 día: si cierra y reabre, sigue siendo el mismo cliente

function color_valido($color) {
    return is_string($color) && preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1;
}

// ---- Identidad del cliente (cookie) ----

function id_valido($id) {
    return is_string($id) && preg_match('/^c[a-f0-9]{16}$/', $id) === 1;
}

function id_cliente_cookie() {
    $id = $_COOKIE[COOKIE_NOMBRE] ?? '';
    return id_valido($id) ? $id : null;
}

function nuevo_id() {
    return 'c' . bin2hex(random_bytes(8));
}

function es_https() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function poner_cookie($id) {
    setcookie(COOKIE_NOMBRE, $id, [
        'expires'  => time() + COOKIE_DURACION,
        'path'     => '/',
        'secure'   => es_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function borrar_cookie() {
    setcookie(COOKIE_NOMBRE, '', ['expires' => time() - 3600, 'path' => '/']);
}

// ---- Estado: { "<id cliente>": "#rrggbb", ... } ----
// Solo están los clientes que han elegido color y no han salido.

function leer_estado() {
    if (!file_exists(ARCHIVO_ESTADO)) {
        return [];
    }
    $estado = json_decode(file_get_contents(ARCHIVO_ESTADO), true);
    return is_array($estado) ? $estado : [];
}

// Lee, modifica y escribe con bloqueo, para que varios clientes a la vez no se pisen.
function modificar_estado(callable $cambio) {
    $fp = fopen(ARCHIVO_ESTADO, 'c+');
    flock($fp, LOCK_EX);
    $estado = json_decode(stream_get_contents($fp), true);
    if (!is_array($estado)) {
        $estado = [];
    }
    $resultado = $cambio($estado);
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, $estado ? json_encode($estado) : '{}');
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return $resultado;
}

// Devuelve false si hay demasiados clientes y este es nuevo.
function guardar_color($id, $color) {
    return modificar_estado(function (&$estado) use ($id, $color) {
        if (!isset($estado[$id]) && count($estado) >= MAX_CLIENTES) {
            return false;
        }
        $estado[$id] = $color;
        return true;
    });
}

function quitar_cliente($id) {
    modificar_estado(function (&$estado) use ($id) {
        unset($estado[$id]);
    });
}

function reiniciar_estado() {
    file_put_contents(ARCHIVO_ESTADO, '{}', LOCK_EX);
}

// ---- Mezcla: media de los canales RGB de los colores elegidos ----
// Si no hay ninguno, devuelve negro.
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
