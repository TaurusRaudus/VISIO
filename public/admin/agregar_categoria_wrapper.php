<?php
/**
 * Procesa el formulario de agregar categoría.
 * - Valida que la petición sea POST.
 * - Intenta agregar la categoría usando GestionarCategoria.
 * - Guarda el mensaje de éxito o error en la sesión.
 * - Redirige de vuelta a agregar_categoria.php.
 */

session_start();
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../src/admin/GestionarCategoria.php";

use Admin\GestionarCategoria;

// Verifica que la petición sea POST, si no redirige al formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: agregar_categoria.php");
    exit;
}

// Obtiene y limpia el nombre de la categoría desde el formulario
$nombre = trim($_POST['nombre'] ?? '');

// Instancia la clase de gestión de categorías
$gestionarCategoria = new GestionarCategoria($conn);

// Intenta agregar la categoría y guarda el resultado
$result = $gestionarCategoria->agregarCategoria($nombre);

// Si hubo error, guarda los errores en la sesión; si no, guarda el mensaje de éxito
if ($result === false) {
    $_SESSION['mensaje_categoria'] = implode("<br>", $gestionarCategoria->getErrores());
} else {
    $_SESSION['mensaje_categoria'] = $gestionarCategoria->getMensaje();
}

// Redirige de vuelta a la página de agregar categoría
header("Location: agregar_categoria.php");
exit;
?>