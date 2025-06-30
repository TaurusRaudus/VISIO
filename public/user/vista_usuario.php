<?php
session_start();
// Verifica que el usuario este autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../sesion/login.php");
    exit;
}
require_once __DIR__ . "/../../config/db.php";
// Obtiene el id del usuario de la sesion
$usuario_id = $_SESSION['usuario'];
// Consulta para obtener nickname y foto del usuario
$stmt = $conn->prepare("SELECT nickname, foto FROM Usuario WHERE id = :id");
$stmt->execute(['id' => $usuario_id]);
$usuarioHeader = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio Usuario</title>
    <link rel="stylesheet" href="../css/user/vista_usuario.css">
    <link rel="stylesheet" href="../css/user/busqueda_usuario.css">
</head>
<body>
    <header class="header">
        <span class="logo">VISIO</span>
        <div class="header-right">
            <span class="user-nick"><?php echo htmlspecialchars($usuarioHeader['nickname'] ?? 'Usuario'); ?></span>
            <img src="<?php echo !empty($usuarioHeader['foto']) ? '../uploads/' . htmlspecialchars($usuarioHeader['foto']) : '../assets/placeholder_usuario.jpg'; ?>" alt="Avatar" class="user-avatar">
            <a href="mi_perfil.php" class="header-btn">Mi Perfil</a>
            <a href="../sesion/logout.php" class="header-btn">Cerrar sesión</a>
        </div>
    </header>

    <!-- Muestra mensaje de bienvenida con el nickname del usuario -->
    <h2>Bienvenido, <?php echo htmlspecialchars($usuarioHeader['nickname']); ?></h2>

    <div class="main-content">
        <div class="search-bar">
            <!-- Formulario de busqueda, envia la palabra clave a usuario_buscar.php -->
            <form action="usuario_buscar.php" method="get">
                <input type="text" name="q" placeholder="Buscar fotos, sonidos, videos..." required>
                <button type="submit">🔍</button>
            </form>
        </div>

        <div class="user-actions">
            <!-- Boton para ver rankings de contenidos -->
            <form action="rankings_usuario.php" method="post">
                <button type="submit">Ver Rankings</button>
            </form>
            <!-- Boton para cerrar sesion -->
            <form action="../sesion/logout.php" method="post">
                <button type="submit">Cerrar sesión</button>
            </form>
        </div>
    </div>
</body>
</html>