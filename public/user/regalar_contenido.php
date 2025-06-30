<?php
// CU-009 Regalar contenido
session_start();
// Verifica que el usuario este autenticado
if (!isset($_SESSION['usuario'])) { header("Location: ../sesion/login.php"); exit; }
require_once __DIR__ . "/../../config/db.php";

// Obtener datos del usuario para el header
$usuario_id = $_SESSION['usuario'];
$stmt = $conn->prepare("SELECT nickname, foto FROM Usuario WHERE id = :id");
$stmt->execute(['id' => $usuario_id]);
$usuarioHeader = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtiene el id del contenido a regalar
$contenido_id = $_GET['id'] ?? '';
if (!$contenido_id) { echo "Contenido no especificado."; exit; }

// Consulta los datos del contenido
$stmt = $conn->prepare("SELECT id, titulo, archivo, precio_original FROM Contenido WHERE id = :id AND estado = 'disponible'");
$stmt->execute(['id' => $contenido_id]);
$contenido = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$contenido) { echo "Contenido no disponible."; exit; }

// Verifica si el usuario ya descargo este contenido
$stmtCheck = $conn->prepare("SELECT COUNT(*) FROM Descarga WHERE usuario_id = :uid AND contenido_id = :cid");
$stmtCheck->execute(['uid' => $_SESSION['usuario'], 'cid' => $contenido['id']]);
$yaDescargado = $stmtCheck->fetchColumn() > 0;

// Obtiene y limpia mensajes de la sesion
$mensaje = $_SESSION['mensaje'] ?? '';
unset($_SESSION['mensaje']);
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? '';
unset($_SESSION['tipo_mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Regalar Contenido</title>
    <!-- Estilos para la vista de regalar contenido -->
    <link rel="stylesheet" href="../css/user/usuario_buscar.css">
    <link rel="stylesheet" href="../css/user/vista_usuario.css">
    <link rel="stylesheet" href="../css/user/busqueda_usuario.css">
    <link rel="stylesheet" href="../css/user/regalar_contenido.css">
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
    <!-- Contenedor principal para regalar contenido -->
    <div class="regalo-container">
        <?php if (!empty($contenido['archivo'])): ?>
            <!-- Muestra la imagen del contenido si existe -->
            <img src="../uploads/<?php echo htmlspecialchars($contenido['archivo']); ?>" alt="<?php echo htmlspecialchars($contenido['titulo']); ?>" style="max-width:100%;display:block;margin:0 auto 24px auto;">
        <?php endif; ?>
        <!-- Titulo del contenido a regalar -->
        <h1>Regalar: <?php echo htmlspecialchars($contenido['titulo']); ?></h1>
        <!-- Formulario para ingresar el destinatario -->
        <form action="regalar_contenido_procesar.php" method="post">
            <input type="hidden" name="contenido_id" value="<?php echo $contenido_id; ?>">
            <label>Usuario destinatario:</label>
            <input type="text" name="destinatario" required>
            <button type="submit" class="btn btn-secondary">Regalar</button>
            <a href="contenido_usuario.php?id=<?php echo urlencode($contenido_id); ?>" class="btn">Volver</a>
        </form>
        <?php if ($mensaje): ?>
            <!-- Muestra mensaje de exito o error -->
            <p class="<?php echo $tipo_mensaje === 'exito' ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
                <?php if ($tipo_mensaje === 'saldo'): ?>
                    <!-- Boton para recargar saldo si falta -->
                    <a href="recargar_saldo.php" class="btn btn-primary" style="margin-left:10px;">Recargar ahora</a>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>