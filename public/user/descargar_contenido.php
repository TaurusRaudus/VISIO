<?php
session_start();
// Verifica que el usuario este autenticado
if (!isset($_SESSION['usuario'])) { header("Location: ../sesion/login.php"); exit; }
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../src/user/DescargarContenido.php";

// Obtener datos del usuario para el header
$usuario_id = $_SESSION['usuario'];
$stmt = $conn->prepare("SELECT nickname, foto FROM Usuario WHERE id = :id");
$stmt->execute(['id' => $usuario_id]);
$usuarioHeader = $stmt->fetch(PDO::FETCH_ASSOC);

// FUN-054 Controlador para mostrar la vista de descarga
function mostrarVistaDescarga($conn, $usuarioHeader) {
    $usuario_id = $_SESSION['usuario'];
    $contenido_id = $_GET['id'] ?? '';
    // Verifica que el id de contenido este presente
    if (!$contenido_id) { echo "Contenido no especificado."; exit; }

    $descarga = new DescargarContenido($conn, $usuario_id);
    list($contenido, $precio, $precio_final, $promo) = $descarga->obtenerInfoContenido($contenido_id);
    // Verifica que el contenido exista y este disponible
    if (!$contenido) { echo "Contenido no disponible."; exit; }

    $saldo = $descarga->obtenerSaldo();
    $mensaje = $_SESSION['mensaje'] ?? '';
    unset($_SESSION['mensaje']);
    $yaDescargado = $descarga->yaDescargado($contenido_id);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Descargar Contenido</title>
        <!-- Carga los estilos principales de usuario -->
        <link rel="stylesheet" href="../css/user/vista_usuario.css">
        <link rel="stylesheet" href="../css/user/busqueda_usuario.css">
        <link rel="stylesheet" href="../css/user/descargar_contenido.css">
    </head>
    <body>
    <!-- Header principal con datos del usuario -->
    <header class="header">
        <span class="logo">VISIO</span>
        <div class="header-right">
            <!-- Muestra el nickname del usuario -->
            <span class="user-nick"><?php echo htmlspecialchars($usuarioHeader['nickname'] ?? 'Usuario'); ?></span>
            <!-- Muestra la foto de perfil o un placeholder -->
            <img src="<?php echo !empty($usuarioHeader['foto']) ? '../uploads/' . htmlspecialchars($usuarioHeader['foto']) : '../assets/placeholder_usuario.jpg'; ?>" alt="Avatar" class="user-avatar">
            <!-- Boton para ir a Mi Perfil -->
            <a href="mi_perfil.php" class="header-btn">Mi Perfil</a>
            <!-- Boton para cerrar sesion -->
            <a href="../sesion/logout.php" class="header-btn">Cerrar sesión</a>
            <!-- Boton para volver a la vista principal -->
            <a href="vista_usuario.php" class="header-btn volver-btn">Volver</a>
        </div>
    </header>
    <!-- Contenedor principal para la descarga -->
    <div class="descarga-container">
        <h1>Descargar: <?php echo htmlspecialchars($contenido['titulo']); ?></h1>
        <p>Precio original: $<?php echo number_format($precio,2); ?></p>
        <?php if ($promo): ?>
            <!-- Muestra mensaje si hay promocion activa -->
            <p class="promo">¡Promoción activa! Precio con descuento: $<?php echo number_format($precio_final,2); ?></p>
        <?php elseif ($precio_final < $precio): ?>
            <!-- Muestra mensaje si hay descuento por gastos acumulados -->
            <p class="promo">¡Descuento por gastos acumulados! Precio: $<?php echo number_format($precio_final,2); ?></p>
        <?php endif; ?>
        <p>Tu saldo: $<?php echo number_format($saldo,2); ?></p>
        <?php if ($mensaje): ?>
            <!-- Muestra mensaje de error si existe -->
            <p class="error"><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>
        <?php if ($yaDescargado): ?>
            <!-- Si ya descargo, permite descargar de nuevo -->
            <form action="descargar_contenido_directo.php" method="post">
                <input type="hidden" name="contenido_id" value="<?php echo $contenido_id; ?>">
                <button type="submit" class="btn btn-primary">Descargar de nuevo</button>
            </form>
        <?php elseif ($saldo < $precio_final): ?>
            <!-- Si no tiene saldo suficiente, muestra mensaje y link para recargar -->
            <p class="error">Saldo insuficiente. <a href="recargar_saldo.php">¿Recargar ahora?</a></p>
        <?php else: ?>
            <!-- Formulario para confirmar y descargar -->
            <form action="descargar_contenido_procesar.php" method="post">
                <input type="hidden" name="contenido_id" value="<?php echo $contenido_id; ?>">
                <input type="hidden" name="precio_final" value="<?php echo $precio_final; ?>">
                <input type="hidden" name="precio_original" value="<?php echo $precio; ?>">
                <button type="submit" class="btn btn-primary">Confirmar y Descargar</button>
            </form>
        <?php endif; ?>
        <a href="contenido_usuario.php?id=<?php echo urlencode($contenido_id); ?>" class="btn">Cancelar</a>
    </div>
    </body>
    </html>
    <?php
}
mostrarVistaDescarga($conn, $usuarioHeader);