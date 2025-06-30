<?php
session_start();
// Verifica que el usuario este autenticado
if (!isset($_SESSION['usuario'])) { header("Location: ../sesion/login.php"); exit; }
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../src/user/DescargarContenido.php";

// FUN-056 Controlador para descarga directa del archivo
function descargarArchivoDirecto($conn) {
    $usuario_id = $_SESSION['usuario'];
    $contenido_id = $_POST['contenido_id'] ?? '';

    // Verifica que el id de contenido este presente
    if (!$contenido_id) {
        $_SESSION['mensaje'] = "Solicitud inválida.";
        header("Location: descargar_contenido.php?id=" . urlencode($contenido_id));
        exit;
    }

    $descarga = new DescargarContenido($conn, $usuario_id);
    // Verifica que el usuario ya haya descargado el contenido
    if (!$descarga->yaDescargado($contenido_id)) {
        $_SESSION['mensaje'] = "No has adquirido este contenido.";
        header("Location: descargar_contenido.php?id=" . urlencode($contenido_id));
        exit;
    }

    $archivo = $descarga->obtenerArchivo($contenido_id);
    $ruta = __DIR__ . "/../uploads/" . $archivo;

    // Verifica que el archivo exista antes de descargar
    if ($archivo && file_exists($ruta)) {
        if (ob_get_level()) {
            ob_end_clean();
        }
        // Configura las cabeceras para forzar la descarga del archivo
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($archivo) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($ruta));
        readfile($ruta);
        exit;
    } else {
        // Si el archivo no existe, muestra mensaje de error y redirige
        $_SESSION['mensaje'] = "Archivo no encontrado.";
        header("Location: contenido_usuario.php?id=" . urlencode($contenido_id));
        exit;
    }
}
// Ejecuta la funcion para descarga directa
descargarArchivoDirecto($conn);