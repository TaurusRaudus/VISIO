<?php
session_start();
// Verificamos que el usuario sea administrador
if (!isset($_SESSION['admin'])) {
    header("Location: ../sesion/login.php");
    exit;
}
require_once __DIR__ . "/../../config/db.php";

// Obtiene el id del usuario desde GET
$usuario_id = $_GET['id'] ?? '';
if (!$usuario_id) {
    echo "Usuario no especificado.";
    exit;
}

// Obtiene y limpia el mensaje de la sesion
$mensaje = $_SESSION['mensaje'] ?? '';
unset($_SESSION['mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recargar Saldo Usuario</title>
    <link rel="stylesheet" href="../css/admin/recargar_saldo_admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Recargar Saldo a Usuario</h1>
        <?php if ($mensaje): ?>
            <!-- Muestra mensaje de exito o error -->
            <div class="message <?php echo (strpos($mensaje, 'exito') !== false || strpos($mensaje, 'añadido') !== false) ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        <!-- Formulario para recargar saldo -->
        <form action="recargar_saldo_admin_procesar.php" method="post">
            <input type="hidden" name="usuario_id" value="<?php echo htmlspecialchars($usuario_id); ?>">
            <label>Monto a recargar ($):</label>
            <input type="number" name="monto" min="5" max="500" step="0.01" required>
            <button type="submit" class="btn">Recargar</button>
            <a href="administrar_usuario.php?id=<?php echo urlencode($usuario_id); ?>" class="btn">Cancelar</a>
        </form>
    </div>
</body>
</html>