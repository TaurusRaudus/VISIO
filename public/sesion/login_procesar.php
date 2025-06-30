<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../src/sesion/login.php';

// Verificamos que la solicitud se haya hecho con POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acceso denegado");
}

// Instanciamos la clase de login con los datos del formulario
$login = new IniciarSesion($conn, $_POST);

// Procesamos el login
if (!$login->processLogin()) {
    // Almacenar errores en la sesión para que el usuario los vea en `login.php`
    $_SESSION['errors'] = $login->getErrors();
    $_SESSION['old'] = $_POST;

    header("Location: login.php"); // Redirigir al formulario de login
    exit();
}

// Si el login es exitoso, la clase ya se encarga de redirigir al usuario
?>