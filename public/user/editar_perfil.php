<?php
// CU-005 Editar Perfil
session_start();
// Verifica que el usuario este autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../sesion/login.php");
    exit;
}
require_once __DIR__ . "/../../config/db.php";

// Obtiene el id del usuario de la sesion
$usuario_id = $_SESSION['usuario'];
// Consulta nickname y foto actual del usuario
$stmt = $conn->prepare("SELECT nickname, foto FROM Usuario WHERE id = :id");
$stmt->execute(['id' => $usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtiene y limpia mensaje de la sesion para mostrar errores o exito
$mensaje = $_SESSION['mensaje_editar_perfil'] ?? '';
unset($_SESSION['mensaje_editar_perfil']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil</title>
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

    <!-- Contenedor principal para editar perfil -->
    <div class="perfil-container">
        <h1>Editar Perfil</h1>
        <!-- Muestra mensaje de error o exito si existe -->
        <?php if ($mensaje): ?>
            <p style="color:#e74c3c;"><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>
        <!-- Formulario para editar foto y nickname -->
        <form action="editar_perfil_procesar.php" method="post" enctype="multipart/form-data">
            <div>
                <label for="foto">Cambiar foto (PNG/JPG, máx 8MB):</label><br>
                <input type="file" name="foto" accept="image/png,image/jpeg">
            </div>
            <div>
                <!-- Campo para cambiar nickname -->
                <label for="nickname">Cambiar nombre de perfil:</label><br>
                <input type="text" name="nickname" value="<?php echo htmlspecialchars($usuario['nickname']); ?>" minlength="5" maxlength="60" pattern="^[a-zA-Z0-9áéíóúñÑ\s]+$">
            </div>
            <!-- Boton para guardar cambios -->
            <button type="submit" class="btn">Guardar Cambios</button>
            <!-- Boton para cancelar y volver al perfil -->
            <a href="mi_perfil.php" class="btn">Cancelar</a>
        </form>
    </div>
</body>
</html>