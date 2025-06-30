<?php
// CU-010 Recargar saldo (Usuario)
session_start();
// Verifica que el usuario este autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../sesion/login.php");
    exit;
}
require_once __DIR__ . "/../../config/db.php";

// Obtener datos del usuario para el header
$usuario_id = $_SESSION['usuario'];
$stmt = $conn->prepare("SELECT nickname, foto FROM Usuario WHERE id = :id");
$stmt->execute(['id' => $usuario_id]);
$usuarioHeader = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtiene y limpia mensaje de la sesion
$mensaje = $_SESSION['mensaje'] ?? '';
unset($_SESSION['mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recargar Saldo</title>
    <!-- Carga los estilos principales de usuario -->
    <link rel="stylesheet" href="../css/user/vista_usuario.css">
    <link rel="stylesheet" href="../css/user/busqueda_usuario.css">
    <link rel="stylesheet" href="../css/user/regalar_contenido.css">
    <link rel="stylesheet" href="../css/user/recargar_saldo.css">
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
            <a href="vista_usuario.php" class="header-btn volver-btn">Volver</a>
        </div>
    </header>
    <!-- Contenedor principal para recargar saldo -->
    <div class="busqueda-container">
        <h1>Recargar Saldo</h1>
        <?php if (!empty($mensaje)): ?>
            <!-- Muestra mensaje de error o exito -->
            <p style="color:#e74c3c;"><?php echo htmlspecialchars($mensaje); ?></p>
            <?php unset($_SESSION['mensaje']); // Limpiar solo después de mostrar ?>
        <?php endif; ?>
        <!-- Formulario para recargar saldo -->
        <form action="recargar_saldo_procesar.php" method="post">
            <label>Nombre:</label>
            <input type="text" name="nombre" required>
            <label>Apellidos:</label>
            <input type="text" name="apellidos" required>
            <label>Número de tarjeta:</label>
            <input type="text" name="tarjeta" maxlength="16" pattern="\d{16}" required>
            <label>CVV:</label>
            <input type="text" name="cvv" maxlength="4" pattern="\d{3,4}" required>
            <label>Monto a recargar ($):</label>
            <input type="number" name="monto" min="5" max="500" step="0.01" required>
            <button type="submit" class="btn">Recargar</button>
            <a href="mi_perfil.php" class="btn">Cancelar</a>
        </form>
    </div>
</body>
</html>