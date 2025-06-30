<?php
// CU-004 Ver mi perfil
session_start();
// Verifica que el usuario este autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../sesion/login.php");
    exit;
}
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../src/user/VerPerfil.php";

// FUN-030 Instanciar y obtener datos del perfil
$perfil = new VerPerfil($conn, $_SESSION['usuario']);
$data = $perfil->obtenerPerfilCompleto();
$usuario = $data['usuario'];
$saldo = $data['saldo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil</title>
    <!-- Carga los estilos principales del usuario -->
    <link rel="stylesheet" href="../css/user/vista_usuario.css">
    <link rel="stylesheet" href="../css/user/busqueda_usuario.css">
    <link rel="stylesheet" href="../css/user/regalar_contenido.css">
    <link rel="stylesheet" href="../css/mi_perfil.css">
</head>
<body>
    <!-- Header principal con datos del usuario -->
    <header class="header">
        <span class="logo">VISIO</span>
        <div class="header-right">
            <!-- Muestra el nickname del usuario -->
            <span class="user-nick"><?php echo htmlspecialchars($usuario['nickname'] ?? 'Usuario'); ?></span>
            <!-- Muestra la foto de perfil o un placeholder -->
            <img src="<?php echo !empty($usuario['foto']) ? '../uploads/' . htmlspecialchars($usuario['foto']) : '../assets/placeholder_usuario.jpg'; ?>" alt="Avatar" class="user-avatar">
            <!-- Boton para ir a Mi Perfil -->
            <a href="mi_perfil.php" class="header-btn">Mi Perfil</a>
            <!-- Boton para cerrar sesion -->
            <a href="../sesion/logout.php" class="header-btn">Cerrar sesión</a>
            <!-- Boton para volver a la vista principal -->
            <a href="vista_usuario.php" class="header-btn volver-btn">Volver</a>
        </div>
    </header>
    <!-- Contenedor principal del perfil -->
    <div class="perfil-container">
        <h1>Mi Perfil</h1>
        <!-- Informacion del usuario -->
        <div class="perfil-info">
            <!-- Imagen de perfil -->
            <img src="<?php echo !empty($usuario['foto']) ? '../uploads/' . htmlspecialchars($usuario['foto']) : '../assets/placeholder_usuario.jpg'; ?>" alt="Foto de perfil" class="perfil-foto">
            <div>
                <!-- Nickname del usuario -->
                <p><strong>Usuario:</strong> <?php echo htmlspecialchars($usuario['nickname']); ?></p>
                <!-- ID del usuario -->
                <p><strong>ID:</strong> <?php echo htmlspecialchars($usuario['id']); ?></p>
                <!-- Correo electronico del usuario -->
                <p><strong>Correo:</strong> <?php echo htmlspecialchars($usuario['correo_electronico']); ?></p>
                <!-- Saldo actual del usuario -->
                <p><strong>Saldo:</strong> $<?php echo number_format($saldo, 2); ?></p>
            </div>
        </div>
        <!-- Acciones disponibles en el perfil -->
        <div class="perfil-actions">
            <!-- Boton para editar perfil -->
            <a href="editar_perfil.php" class="btn">Editar Perfil</a>
            <!-- Boton para borrar cuenta -->
            <a href="borrar_cuenta.php" class="btn btn-danger" onclick="return confirm('¿Seguro que deseas borrar tu cuenta?');">Borrar Cuenta</a>
            <!-- Boton para recargar saldo -->
            <a href="recargar_saldo.php" class="btn">Recargar Saldo</a>
            <!-- Boton para volver a la vista principal -->
            <a href="vista_usuario.php" class="btn">Volver</a>
        </div>
        <!-- Formulario para cerrar sesion -->
        <form action="../sesion/logout.php" method="post">
            <button type="submit">Cerrar sesión</button>
        </form>
    </div>
</body>
</html>