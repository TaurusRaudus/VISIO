<?php
session_start();
// Verifica que el usuario este autenticado
if (!isset($_SESSION['usuario'])) { header("Location: ../sesion/login.php"); exit; }
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../src/user/DescargarContenido.php";

// FUN-055 Controlador para procesar la compra y registrar la descarga
function procesarCompraDescarga($conn) {
    // Obtiene el id del usuario de la sesion
    $usuario_id = $_SESSION['usuario'];
    // Obtiene los datos enviados por POST
    $contenido_id = $_POST['contenido_id'] ?? '';
    $precio_final = floatval($_POST['precio_final'] ?? 0);
    $precio_original = floatval($_POST['precio_original'] ?? 0);

    // Valida que los datos sean correctos
    if (!$contenido_id || $precio_final <= 0) {
        $_SESSION['mensaje'] = "Solicitud inválida.";
        header("Location: descargar_contenido.php?id=" . urlencode($contenido_id));
        exit;
    }

    // Instancia la clase para procesar la descarga
    $descarga = new DescargarContenido($conn, $usuario_id);
    $resultado = $descarga->procesarCompra($contenido_id, $precio_final, $precio_original);

    // Si la compra fue exitosa, redirige al contenido
    if ($resultado === true) {
        $_SESSION['mensaje'] = "¡Contenido comprado con éxito! Ahora puedes descargarlo cuando quieras desde esta página.";
        header("Location: contenido_usuario.php?id=" . urlencode($contenido_id));
        exit;
    } else {
        // Si hubo error, redirige a la pagina de descarga mostrando el mensaje
        $_SESSION['mensaje'] = $resultado;
        header("Location: descargar_contenido.php?id=" . urlencode($contenido_id));
        exit;
    }
}
// Ejecuta el procesamiento de la compra y descarga
procesarCompraDescarga($conn);