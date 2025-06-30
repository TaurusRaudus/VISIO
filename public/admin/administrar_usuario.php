<?php
// CU-018 Administrar Usuarios
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../sesion/login.php");
    exit;
}
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../src/admin/GestionarUsuario.php";

if (isset($_SESSION['mensaje']) && $_SESSION['mensaje']) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
    echo "<script>alert(" . json_encode($mensaje) . ");</script>";
}

use Admin\GestionarUsuario;

/**
 * FUN-122 Obtener y validar el ID del usuario desde GET.
 * Redirige si no existe.
 */
function obtenerUsuarioId()
{
    $usuarioId = $_GET['id'] ?? '';
    if (!$usuarioId) {
        $_SESSION['mensaje'] = "No se especificó el usuario.";
        header("Location: admin_busqueda.php?type=usuario");
        exit;
    }
    return $usuarioId;
}

/**
 * FUN-123 Obtener datos del usuario y redirigir si no existe.
 */
function obtenerDatosUsuario($usuarioAdmin, $usuarioId)
{
    $usuario = $usuarioAdmin->obtenerUsuario($usuarioId);
    if (!$usuario) {
        $_SESSION['mensaje'] = implode("<br>", $usuarioAdmin->getErrores());
        header("Location: admin_busqueda.php?type=usuario");
        exit;
    }
    return $usuario;
}

/**
 * FUN-124 Filtrar descargas para excluir contenidos eliminados/no disponibles.
 * Devuelve un array de descargas activas con miniatura.
 */
function filtrarDescargasActivas($conn, $descargas)
{
    $descargas_activas = [];
    if (!empty($descargas)) {
        $ids_contenido = array_column($descargas, 'contenido_id');
        $in = str_repeat('?,', count($ids_contenido) - 1) . '?';
        $stmt = $conn->prepare("SELECT id, archivo, estado FROM Contenido WHERE id IN ($in)");
        $stmt->execute($ids_contenido);
        $estados = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $estados[$row['id']] = $row;
        }
        foreach ($descargas as $descarga) {
            $cid = $descarga['contenido_id'];
            if (isset($estados[$cid]) && $estados[$cid]['estado'] === 'disponible') {
                $descarga['miniatura'] = !empty($estados[$cid]['archivo'])
                    ? '../uploads/' . htmlspecialchars($estados[$cid]['archivo'])
                    : '../assets/placeholder_contenido.jpg';
                $descargas_activas[] = $descarga;
            }
        }
    }
    return $descargas_activas;
}

// --- Controlador principal ---

$usuarioId = obtenerUsuarioId();
$usuarioAdmin = new GestionarUsuario($conn);
$usuario = obtenerDatosUsuario($usuarioAdmin, $usuarioId);
$saldo = $usuarioAdmin->obtenerSaldo($usuarioId);
$descargas = $usuarioAdmin->obtenerUltimasDescargas($usuarioId, 10);
$descargas_activas = filtrarDescargasActivas($conn, $descargas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Usuario</title>
    <link rel="stylesheet" href="../css/admin/UsuarioAdmin.css">
</head>
<body>
    <div class="admin-container">
        <div class="header">
            <h1>Administrar Usuario</h1>
            <a href="admin_busqueda.php?type=usuario" class="back-link">← Volver a búsqueda</a>
        </div>
        <div class="user-detail">
            <div class="user-media">
                <img src="<?php echo !empty($usuario['foto']) ? '../uploads/' . htmlspecialchars($usuario['foto']) : '../assets/placeholder_usuario.jpg'; ?>"
                     alt="Foto de <?php echo htmlspecialchars($usuario['nickname']); ?>">
            </div>
            <div class="user-info">
                <div class="detail-item">
                    <span class="detail-label">Nickname:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($usuario['nickname']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Correo electrónico:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($usuario['correo_electronico']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Fecha de registro:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($usuario['fecha_de_registro']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Saldo actual:</span>
                    <span class="detail-value">$<?php echo number_format($saldo, 2); ?></span>
                </div>
            </div>
        </div>
        <div class="action-buttons">
            <!-- Botón para recargar saldo al usuario -->
            <form action="recargar_saldo_admin.php" method="get" style="display:inline;">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario['id']); ?>">
                <button type="submit" class="btn btn-primary">Recargar Saldo</button>
            </form>
        </div>
        <!-- Historial de descargas: últimas 10 descargas activas -->
        <div class="descargas-galeria">
            <h2>Historial de Descargas (Últimas 10)</h2>
            <div class="galeria-grid">
                <?php if (empty($descargas_activas)): ?>
                    <p>No hay descargas recientes para este usuario.</p>
                <?php else: ?>
                    <?php foreach ($descargas_activas as $descarga): ?>
                        <div class="galeria-item">
                            <a href="contenido_admin.php?id=<?php echo htmlspecialchars($descarga['contenido_id']); ?>">
                                <img class="galeria-img"
                                     src="<?php echo $descarga['miniatura']; ?>"
                                     alt="<?php echo htmlspecialchars($descarga['titulo']); ?>">
                                <div class="galeria-titulo"><?php echo htmlspecialchars($descarga['titulo']); ?></div>
                                <div class="galeria-fecha">
                                    <?php echo htmlspecialchars($descarga['fecha_de_compra']); ?> - $<?php echo number_format($descarga['precio_pagado'], 2); ?>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>