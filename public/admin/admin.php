<?php
// Inicia la sesión y verifica que el usuario sea administrador
session_start();
if (!isset($_SESSION['admin'])) {
    // Si no hay sesión de admin, redirige al login
    header("Location: ../sesion/login.php");
    exit;
}
require_once __DIR__ . "/../../config/db.php";

// Mostrar mensaje de sesión como alerta si existe
if (isset($_SESSION['mensaje']) && $_SESSION['mensaje']) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
    // Muestra el mensaje como alerta en JavaScript
    echo "<script>alert(" . json_encode($mensaje) . ");</script>";
}

// Obtiene el nickname del administrador usando su correo electrónico almacenado en sesión
$email = $_SESSION['admin'];
$stmt = $conn->prepare("SELECT nickname FROM Administrador WHERE correo_electronico = :email LIMIT 1");
$stmt->execute(['email' => $email]);
$adminRecord = $stmt->fetch(PDO::FETCH_ASSOC);

if ($adminRecord) {
    $displayName = $adminRecord['nickname'];
} else {
    // Si no se encuentra el nickname, muestra el correo
    $displayName = $email;
}
// Guarda el nombre a mostrar en la sesión
$_SESSION['displayName'] = $displayName;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administrador</title>

    <!-- Hojas de estilo para el panel de administrador -->
    <link rel="stylesheet" href="../css/admin/admin_header.css">
    <link rel="stylesheet" href="../css/admin/admin.css">
</head>

<body>
    <!-- Encabezado del panel de administrador -->
    <header class="admin-header">
        <span class="admin-logo">VISIO</span>
        <div class="admin-header-right">
            <span class="admin-nick"><?php echo htmlspecialchars($displayName); ?></span>
            <!-- Botón para cerrar sesión -->
            <a href="../sesion/logout.php" class="admin-header-btn">Cerrar sesión</a>
        </div>
    </header>
    <div class="admin-container">
        <h1>Bienvenido, <?php echo htmlspecialchars($displayName); ?></h1>
        
        <!-- Sección de búsqueda y acciones rápidas -->
        <div class="search-section">
            <!-- Barra de búsqueda de contenido, categoría o usuario -->
            <form action="admin_busqueda.php" method="get" class="search-bar">
                <input type="text" name="q" placeholder="Buscar contenido, categoría o usuario..." required>
            </form>
            
            <!-- Botones de acción para agregar contenido, categoría o cerrar sesión -->
            <div class="action-buttons">
                <form action="agregar_contenido.php" method="get">
                    <button type="submit">Agregar Contenido</button>
                </form>
                <form action="agregar_categoria.php" method="get">
                    <button type="submit">Agregar Categoría</button>
                </form>
                <form action="../sesion/logout.php" method="get">
                    <button type="submit">Cerrar Sesion</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>