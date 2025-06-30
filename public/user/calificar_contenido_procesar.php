<?php
session_start();
// Verifica que el usuario este autenticado
if (!isset($_SESSION['usuario'])) { header("Location: ../sesion/login.php"); exit; }
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../src/user/GestionarCalificacion.php";

use User\GestionarCalificacion;

// Obtiene datos del usuario y del formulario
$usuario_id = $_SESSION['usuario'];
$contenido_id = $_POST['contenido_id'] ?? '';
$nota = $_POST['nota'] ?? '';
$mensaje = trim($_POST['mensaje'] ?? '');

// Valida que el contenido y la nota sean obligatorios
if (!$contenido_id || !$nota) {
    $_SESSION['mensaje'] = "La calificación es obligatoria.";
    header("Location: calificar_contenido.php?id=" . urlencode($contenido_id));
    exit;
}

$calificador = new GestionarCalificacion($conn);

// Intenta guardar la calificacion y guarda el mensaje en la sesion
if ($calificador->guardarCalificacion($usuario_id, $contenido_id, $nota, $mensaje)) {
    $_SESSION['mensaje'] = $calificador->getMensaje();
} else {
    $_SESSION['mensaje'] = implode("<br>", $calificador->getErrores());
}
// Redirige de nuevo a la pagina de calificacion
header("Location: calificar_contenido.php?id=" . urlencode($contenido_id));
exit;