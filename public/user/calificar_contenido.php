<?php
session_start();
// Verifica que el usuario este autenticado
if (!isset($_SESSION['usuario'])) { header("Location: ../sesion/login.php"); exit; }
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../src/user/GestionarCalificacion.php";

// Obtiene datos del usuario para el header
$usuario_id = $_SESSION['usuario'];
$stmt = $conn->prepare("SELECT nickname, foto FROM Usuario WHERE id = :id");
$stmt->execute(['id' => $usuario_id]);
$usuarioHeader = $stmt->fetch(PDO::FETCH_ASSOC);

use User\GestionarCalificacion;

/**
 * FUN-133 Obtener y validar el ID del contenido desde GET.
 * Si no existe, muestra mensaje y termina.
 */
function obtenerContenidoId()
{
    // Obtiene el id del contenido desde GET y valida que exista
    $contenido_id = $_GET['id'] ?? '';
    if (!$contenido_id) {
        echo "Contenido no especificado.";
        exit;
    }
    return $contenido_id;
}

/**
 * FUN-134 Verifica que el usuario tenga acceso al contenido.
 * Si no tiene acceso, muestra errores y termina.
 */
function verificarAccesoUsuario($calificador, $usuario_id, $contenido_id)
{
    // Verifica si el usuario tiene acceso al contenido
    if (!$calificador->usuarioTieneAcceso($usuario_id, $contenido_id)) {
        echo implode("<br>", $calificador->getErrores());
        exit;
    }
}

/**
 * FUN-135 Obtiene la calificación previa y si ya calificó.
 * Devuelve array [calificacion, yaCalificado]
 */
function obtenerEstadoCalificacion($calificador, $usuario_id, $contenido_id)
{
    // Obtiene la calificacion y si ya califico
    $calificacion = $calificador->obtenerCalificacion($usuario_id, $contenido_id);
    $yaCalificado = $calificador->yaCalificado($usuario_id, $contenido_id);
    return [$calificacion, $yaCalificado];
}

/**
 * FUN-136 Obtiene los datos del contenido y la ruta de la imagen.
 * Devuelve array [cont, img]
 */
function obtenerDatosYMiniatura($calificador, $contenido_id)
{
    // Obtiene los datos del contenido y la ruta de la imagen o placeholder
    $cont = $calificador->obtenerDatosContenido($contenido_id);
    $img = (!empty($cont['archivo']) && file_exists(__DIR__ . "/../uploads/" . $cont['archivo']))
        ? "../uploads/" . htmlspecialchars($cont['archivo'])
        : "../assets/placeholder_contenido.jpg";
    return [$cont, $img];
}

/**
 * FUN-137 Obtiene y limpia el mensaje de sesión.
 */
function obtenerYLimpiarMensajeSesion()
{
    // Obtiene y limpia el mensaje de la sesion
    $mensaje = $_SESSION['mensaje'] ?? '';
    unset($_SESSION['mensaje']);
    return $mensaje;
}

// --- Controlador principal ---

$usuario_id = $_SESSION['usuario'];
$contenido_id = obtenerContenidoId();

$calificador = new GestionarCalificacion($conn);

verificarAccesoUsuario($calificador, $usuario_id, $contenido_id);

list($calificacion, $yaCalificado) = obtenerEstadoCalificacion($calificador, $usuario_id, $contenido_id);

list($cont, $img) = obtenerDatosYMiniatura($calificador, $contenido_id);

$mensaje = obtenerYLimpiarMensajeSesion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calificar Contenido</title>
    <!-- Carga los estilos principales para la vista de calificar -->
    <link rel="stylesheet" href="../css/user/usuario_buscar.css">
    <link rel="stylesheet" href="../css/user/vista_usuario.css">
    <link rel="stylesheet" href="../css/user/busqueda_usuario.css">
    <link rel="stylesheet" href="../css/user/regalar_contenido.css">
    <link rel="stylesheet" href="../css/user/calificar_contenido.css">
</head>
<body>
<!-- Header principal con datos del usuario -->
<header class="header">
    <span class="logo">VISIO</span>
    <div class="header-right">
        <span class="user-nick"><?php echo htmlspecialchars($usuarioHeader['nickname'] ?? 'Usuario'); ?></span>
        <img src="<?php echo !empty($usuarioHeader['foto']) ? '../uploads/' . htmlspecialchars($usuarioHeader['foto']) : '../assets/placeholder_usuario.jpg'; ?>" alt="Avatar" class="user-avatar">
        <a href="mi_perfil.php" class="header-btn">Mi Perfil</a>
        <a href="../sesion/logout.php" class="header-btn">Cerrar sesión</a>
        <a href="contenido_usuario.php?id=<?php echo urlencode($contenido_id); ?>" class="header-btn volver-btn">Volver</a>
    </div>
</header>
<!-- Contenedor principal para calificar contenido -->
<div class="calificar-container">
    <h1>Calificar Contenido</h1>
    <?php if ($mensaje): ?>
        <!-- Muestra mensaje de exito si existe -->
        <div class="success-message"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
    <!-- Muestra la imagen o miniatura del contenido -->
    <img src="<?php echo $img; ?>" alt="Imagen de <?php echo htmlspecialchars($cont['titulo']); ?>" class="calificar-thumb">
    <?php if ($yaCalificado): ?>
        <!-- Si ya califico, muestra la calificacion y comentario -->
        <div class="success-message">Ya has calificado este contenido.</div>
        <p><strong>Tu calificación:</strong> <?php echo htmlspecialchars($calificacion['nota']); ?></p>
        <p><strong>Comentario:</strong> <?php echo htmlspecialchars($calificacion['mensaje']); ?></p>
        <a href="contenido_usuario.php?id=<?php echo urlencode($contenido_id); ?>" class="btn">Volver</a>
    <?php else: ?>
        <!-- Formulario para calificar el contenido -->
        <form action="calificar_contenido_procesar.php" method="post" class="calificar-form">
            <input type="hidden" name="contenido_id" value="<?php echo htmlspecialchars($contenido_id); ?>">
            <label for="nota">Calificación (1-10):</label>
            <select name="nota" id="nota" required>
                <option value="">Selecciona</option>
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
            <label for="mensaje">Comentario (opcional):</label>
            <textarea name="mensaje" id="mensaje" rows="3" maxlength="255"></textarea>
            <button type="submit" class="btn">Enviar Calificación</button>
            <a href="contenido_usuario.php?id=<?php echo urlencode($contenido_id); ?>" class="btn btn-secondary">Cancelar</a>
        </form>
    <?php endif; ?>
</div>
</body>
</html>