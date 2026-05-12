<?php
declare(strict_types=1);

$pathConfig = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

$config = require $pathConfig;

$nombre = isset($_POST['nombre']) ? trim((string) $_POST['nombre']) : '';
$email = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
$mensaje = isset($_POST['mensaje']) ? trim((string) $_POST['mensaje']) : '';

// 4. Validar datos
$ok = $nombre !== '' && $email !== '' && $mensaje !== '' && filter_var($email, FILTER_VALIDATE_EMAIL);

if (!$ok) {
    header('Location: index.html?validacion=1#contacto', true, 303);
    exit;
}

// 5. Conexión y Guardado
try {
    $dsn = "mysql:host={$config['db_host']};
    dbname={$config['db_name']};
    charset={$config['db_charset']}";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $stmt = $pdo->prepare("INSERT INTO contactos (nombre, email, mensaje) VALUES (?, ?, ?)");
    $stmt->execute([$nombre, $email, $mensaje]);

    header('Location: index.html?success=1#contacto', true, 303);
    exit;

} catch (PDOException $e) {
    die("Error de Conexión a MySQL: " . $e->getMessage());
}