<?php
session_start();
// Verifica que el usuario sea administrador
if (!isset($_SESSION['admin'])) {
    header("Location: ../sesion/login.php");
    exit;
}
require_once __DIR__ . "/../../config/db.php";

// Obtiene los datos del formulario
$usuario_id = $_POST['usuario_id'] ?? '';
$monto = floatval($_POST['monto'] ?? 0);
$admin_id = $_SESSION['admin_id'] ?? null;

// Valida el monto de la recarga
if ($monto < 5 || $monto > 500) {
    $_SESSION['mensaje'] = "Error, monto debe encontrarse entre \$5 y \$500";
    header("Location: recargar_saldo_admin.php?id=" . urlencode($usuario_id));
    exit;
}

try {
    // Inserta la recarga en la base de datos
    $stmt = $conn->prepare("INSERT INTO Recarga (monto, fecha_de_recarga, usuario_id, administrador_id) VALUES (:monto, NOW(), :usuario_id, :admin_id)");
    $stmt->execute([
        'monto' => $monto,
        'usuario_id' => $usuario_id,
        'admin_id' => $admin_id
    ]);
    $_SESSION['mensaje'] = "Recarga exitosa. Se han añadido \${$monto} al usuario.";
} catch (Exception $e) {
    // Maneja errores en la insercion
    $_SESSION['mensaje'] = "Error al procesar la recarga.";
}
// Redirige de vuelta a la administracion del usuario
header("Location: administrar_usuario.php?id=" . urlencode($usuario_id));
exit;