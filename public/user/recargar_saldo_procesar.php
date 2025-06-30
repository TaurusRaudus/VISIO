<?php
session_start();
// Verifica que el usuario este autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../sesion/login.php");
    exit;
}
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../src/user/RecargarSaldo.php";

// FUN-072 Controlador para procesar recarga de saldo
function procesarRecargaSaldo($conn) {
    // Obtiene el id del usuario desde la sesion
    $usuario_id = $_SESSION['usuario'];
    // Obtiene y limpia los datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $tarjeta = trim($_POST['tarjeta'] ?? '');
    $cvv = trim($_POST['cvv'] ?? '');
    $monto = floatval($_POST['monto'] ?? 0);

    // Instancia la clase RecargarSaldo
    $recarga = new RecargarSaldo($conn, $usuario_id);
    // Procesa la recarga
    $resultado = $recarga->procesarRecarga($nombre, $apellidos, $tarjeta, $cvv, $monto);

    // Guarda el mensaje en la sesion
    $_SESSION['mensaje'] = $recarga->getMensaje();
    // Redirige segun el resultado
    if ($resultado) {
        header("Location: mi_perfil.php");
    } else {
        header("Location: recargar_saldo.php");
    }
    exit;
}

// Ejecuta el controlador de recarga
procesarRecargaSaldo($conn);
?>
<a href="recargar_saldo_admin.php?id=<?php echo urlencode($usuarioId); ?>" class="btn">Recargar Saldo</a>