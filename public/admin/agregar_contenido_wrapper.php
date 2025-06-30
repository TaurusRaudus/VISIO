<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CU - 011 Agregar Contenido
// Verifica que el usuario sea administrador
if (!isset($_SESSION['admin'])) {
    header("Location: ../sesion/login.php");
    exit;
}

// Incluye la configuración de la base de datos y la clase de gestión de contenido
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../src/admin/GestionarContenido.php";

use Admin\GestionarContenido;

// Instancia la clase de gestión de contenido
$gestionarContenido = new GestionarContenido($conn);

// Intenta agregar el contenido usando los datos del formulario y archivos subidos
$result = $gestionarContenido->agregarContenido($_POST, $_FILES);

// Si hubo error, guarda los errores en la sesión y redirige de vuelta al formulario
if ($result === false) {
    $_SESSION['mensaje'] = implode("<br>", $gestionarContenido->getErrors());
    header("Location: agregar_contenido.php");
    exit;
} else {
    // Si fue exitoso, guarda el mensaje de éxito y redirige
    $_SESSION['mensaje'] = "Contenido agregado correctamente. Nuevo ID: " . $result;
    header("Location: agregar_contenido.php");
    exit;
}