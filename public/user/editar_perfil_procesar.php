<?php
// CU-005 Editar Perfil
session_start();
// Verifica que el usuario este autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../sesion/login.php");
    exit;
}
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../src/user/EditarPerfil.php";

// FUN-038 Procesar edicion de perfil desde el controlador
function procesarEdicionPerfil($conn) {
    // Obtiene el id del usuario de la sesion
    $usuario_id = $_SESSION['usuario'];
    // Obtiene el nickname enviado por POST
    $nickname = isset($_POST['nickname']) ? trim($_POST['nickname']) : null;
    // Obtiene el archivo de foto enviado
    $foto = $_FILES['foto'] ?? null;

    // Instancia la clase EditarPerfil
    $editor = new EditarPerfil($conn, $usuario_id);
    // Procesa la edicion del perfil
    $editor->procesarEdicion($foto, $nickname);

    // Guarda el mensaje en la sesion
    $_SESSION['mensaje'] = $editor->getMensaje();
    // Redirige a mi_perfil.php
    header("Location: mi_perfil.php");
    exit;
}

// Ejecuta el procesamiento de edicion de perfil
procesarEdicionPerfil($conn);
?>

<!-- Formulario HTML de ejemplo para editar perfil (no se usa en este flujo, solo referencia) -->
<form method="POST" enctype="multipart/form-data">
    <!-- Campo para subir nueva foto -->
    <input type="file" name="foto" accept="image/*">
    <!-- Campo para editar nickname -->
    <input type="text" name="nickname" value="<?php echo htmlspecialchars($usuario['nickname']); ?>" minlength="5" maxlength="60" pattern="^[a-zA-Z0-9áéíóúñÑ\s]+$">
    <!-- Boton para guardar cambios -->
    <button type="submit">Guardar cambios</button>
</form>
<?php
// Guarda mensaje de edicion en la sesion y redirige a editar_perfil.php
$_SESSION['mensaje_editar_perfil'] = $mensaje;
// Redirige a editar_perfil.php
header("Location: editar_perfil.php");
exit;
?>