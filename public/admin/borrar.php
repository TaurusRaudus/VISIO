<?php
session_start();
// Verifica que el usuario sea administrador
if (!isset($_SESSION['admin'])) {
    header("Location: ../sesion/login.php");
    exit;
}
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../src/admin/GestionarEliminacion.php";

use Admin\GestionarEliminacion;

// Instancia la clase de gestion de eliminacion
$eliminador = new GestionarEliminacion($conn);

// Paso 1: Mostrar formulario de confirmacion si no se ha enviado la contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['confirmar'])) {
    $tipo = $_POST['tipo'] ?? '';
    $id = $_POST['id'] ?? '';
    // Verifica que se reciban los datos necesarios
    if (!$id || !$tipo) {
        $_SESSION['mensaje'] = "Datos incompletos para borrar.";
        header("Location: admin.php");
        exit;
    }
    // Muestra el formulario de confirmacion
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Confirmar Eliminación</title>
        <link rel="stylesheet" href="../css/admin/borrar_confirmacion.css">
    </head>
    <body>
        <div class="confirm-container">
            <h2>Confirmar Eliminación</h2>
            <p>Para eliminar este <?php echo htmlspecialchars($tipo); ?>, ingresa tu contraseña de administrador:</p>
            <form action="borrar.php" method="post">
                <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($tipo); ?>">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                <input type="password" name="password" placeholder="Contraseña" required>
                <div class="confirm-actions">
                    <button type="submit" name="confirmar" value="1" class="btn-confirm">Confirmar</button>
                    <button type="button" class="btn-cancel" onclick="window.history.back();">Cancelar</button>
                </div>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Paso 2: Procesar confirmacion o cancelacion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    $tipo = $_POST['tipo'] ?? '';
    $id = $_POST['id'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validar contraseña del admin
    $correoAdmin = $_SESSION['admin'];
    if (!$eliminador->validarPasswordAdmin($correoAdmin, $password)) {
        $_SESSION['mensaje'] = implode("<br>", $eliminador->getErrores());
        if ($tipo === 'contenido') {
            header("Location: contenido_admin.php?id=" . urlencode($id));
        } elseif ($tipo === 'categoria' || $tipo === 'subcategoria') {
            header("Location: categoria_admin.php?id=" . urlencode($id));
        } else {
            header("Location: admin.php");
        }
        exit;
    }

    // Ejecuta el borrado logico segun el tipo
    $ok = false;
    if ($tipo === 'contenido') {
        $ok = $eliminador->borrarContenido($id);
        $_SESSION['mensaje'] = $ok ? "Contenido eliminado (desactivado)." : "Error al eliminar el contenido.<br>" . implode("<br>", $eliminador->getErrores());
        header("Location: admin.php");
        exit;
    } elseif ($tipo === 'categoria') {
        $ok = $eliminador->borrarCategoria($id);
        $_SESSION['mensaje'] = $ok ? "Categoría eliminada (desactivada o movidos sus elementos)." : "Error al eliminar la categoría.<br>" . implode("<br>", $eliminador->getErrores());
        header("Location: admin.php");
        exit;
    } elseif ($tipo === 'subcategoria') {
        $ok = $eliminador->borrarSubcategoria($id);
        $_SESSION['mensaje'] = $ok ? "Subcategoría eliminada (desactivada o movidos sus elementos)." : "Error al eliminar la subcategoría.<br>" . implode("<br>", $eliminador->getErrores());
        header("Location: admin.php");
        exit;
    } elseif ($tipo === 'usuario') {
        $ok = $eliminador->borrarUsuario($id);
        $_SESSION['mensaje'] = $ok ? "Usuario desactivado." : "Error al desactivar el usuario.<br>" . implode("<br>", $eliminador->getErrores());
        header("Location: admin.php");
        exit;
    }
}

// Si se accede de otra forma, redirige al panel principal
header("Location: admin.php");
exit;

// Ejemplo para agregar_categoria_wrapper.php
// Consulta para verificar si ya existe una categoria activa con ese nombre
$stmt = $conn->prepare("SELECT COUNT(*) FROM Categoria WHERE nombre = :nombre AND estado = 'activa'");
$stmt->execute(['nombre' => $nombre]);
if ($stmt->fetchColumn() > 0) {
    $_SESSION['mensaje_categoria'] = "Ya existe una categoría activa con ese nombre.";
    header("Location: agregar_categoria.php");
    exit;
}
?>