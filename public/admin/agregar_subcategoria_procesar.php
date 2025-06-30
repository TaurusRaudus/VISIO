<?php
session_start();
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../src/admin/GestionarSubcategoria.php";

use Admin\GestionarSubcategoria;

// Verifica que el usuario sea administrador
if (!isset($_SESSION['admin'])) {
    header("Location: ../sesion/login.php");
    exit;
}

// Verifica que la peticion sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin.php");
    exit;
}

// Obtiene y limpia los datos del formulario
$nombre = trim($_POST['nombre'] ?? '');
$padre_id = intval($_POST['padre_id'] ?? 0);

// Instancia la clase de gestion de subcategorias y agrega la subcategoria
$gestionarSubcategoria = new GestionarSubcategoria($conn);
$result = $gestionarSubcategoria->agregarSubcategoria($nombre, $padre_id);

// Si hubo error, guarda los errores en la sesion y redirige de vuelta al formulario
if ($result === false) {
    $_SESSION['mensaje_categoria'] = implode("<br>", $gestionarSubcategoria->getErrores());
    header("Location: agregar_subcategoria.php?padre_id=" . urlencode($padre_id));
    exit;
} else {
    // Si fue exitoso, guarda el mensaje de exito y redirige al formulario
    $_SESSION['mensaje_categoria'] = $gestionarSubcategoria->getMensaje();
    header("Location: agregar_subcategoria.php?padre_id=" . urlencode($padre_id));
    exit;
}
?>