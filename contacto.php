<?php
declare(strict_types=1);

/**
 * Recibe el formulario de contacto y guarda en MySQL (HU07).
 * Redirige a index.php#contacto con query de estado.
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php#contacto', true, 303);
    exit;
}

$config = require __DIR__ . '/config.php';

$nombre = isset($_POST['nombre']) ? trim((string) $_POST['nombre']) : '';
$email = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
$mensaje = isset($_POST['mensaje']) ? trim((string) $_POST['mensaje']) : '';

$ok =
    $nombre !== ''
    && $email !== ''
    && $mensaje !== ''
    && strlen($nombre) <= 120
    && strlen($email) <= 180
    && strlen($mensaje) <= 4000
    && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;

if (!$ok) {
    $qs = [];
    if ($nombre === '' || strlen($nombre) > 120) {
        $qs[] = 'e_nombre=1';
    }
    if ($email === '' || strlen($email) > 180) {
        $qs[] = 'e_email=1';
    } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $qs[] = 'e_email_fmt=1';
    }
    if ($mensaje === '' || strlen($mensaje) > 4000) {
        $qs[] = 'e_mensaje=1';
    }
    $query = $qs !== [] ? '?' . implode('&', $qs) : '';
    header('Location: index.php' . $query . '#contacto', true, 303);
    exit;
}

$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    $config['db_host'],
    $config['db_name'],
    $config['db_charset']
);

try {
    $pdo = new PDO(
        $dsn,
        $config['db_user'],
        $config['db_pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $stmt = $pdo->prepare(
        'INSERT INTO contactos (nombre, email, mensaje) VALUES (:nombre, :email, :mensaje)'
    );
    $stmt->execute([
        ':nombre' => $nombre,
        ':email' => $email,
        ':mensaje' => $mensaje,
    ]);
} catch (Throwable $e) {
    header('Location: index.php?error=1#contacto', true, 303);
    exit;
}

header('Location: index.php?enviado=1#contacto', true, 303);
exit;
