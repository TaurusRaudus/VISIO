<?php
session_start();
// Verifica que el usuario este autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../sesion/login.php");
    exit;
}
require_once __DIR__ . "/../../config/db.php";
$usuario_id = $_SESSION['usuario'];
$stmt = $conn->prepare("SELECT nickname, foto FROM Usuario WHERE id = :id");
$stmt->execute(['id' => $usuario_id]);
$usuarioHeader = $stmt->fetch(PDO::FETCH_ASSOC);

// Guarda la ultima busqueda en la sesion si existe
if (isset($_GET['q'])) {
    $_SESSION['last_search'] = $_GET['q'];
}

// Determina la url para volver segun la ultima busqueda
if (!empty($_SESSION['last_search'])) {
      $volverUrl = 'usuario_buscar.php?q=' . urlencode($_SESSION['last_search']);
  } else {
      // Ajusta la ruta relativa segun donde este este header
      $volverUrl = 'vista_usuario.php';
  }

// Validar ID de contenido
if (!isset($_GET['id'])) {
    echo "Contenido no especificado.";
    exit;
}
$contenidoId = $_GET['id'];

// Obtener datos del contenido (solo disponible)
$stmt = $conn->prepare("
    SELECT 
        c.id, 
        c.titulo, 
        c.autor, 
        c.descripcion, 
        c.precio_original, 
        c.\"tamaño_mb\" AS tamano_mb, 
        c.fecha_de_subida, 
        c.archivo,
        c.categoria_id,
        t.nombre_del_tipo AS tipo_archivo, 
        cat.nombre AS categoria
    FROM Contenido c
    LEFT JOIN TipoArchivo t ON c.tipo_archivo_id = t.id
    LEFT JOIN Categoria cat ON c.categoria_id = cat.id
    WHERE c.id = :id AND c.estado = 'disponible'
");
$stmt->execute(['id' => $contenidoId]);
$contenido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contenido) {
    echo "Contenido no encontrado o no disponible.";
    exit;
}

// Verificamos si el usuario ya descargo este contenido
$stmtCheck = $conn->prepare("SELECT COUNT(*) FROM Descarga WHERE usuario_id = :uid AND contenido_id = :cid");
$stmtCheck->execute(['uid' => $_SESSION['usuario'], 'cid' => $contenido['id']]);
$yaDescargado = $stmtCheck->fetchColumn() > 0;

$rutaArchivo = "../uploads/" . htmlspecialchars($contenido['archivo']);
$tipo = strtolower($contenido['tipo_archivo']);

// Paginacion para la galeria lateral
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$porPagina = 10;
$offset = ($pagina - 1) * $porPagina;

// Obtener mas contenidos de la misma categoria, excluyendo el actual
$stmtTotal = $conn->prepare("SELECT COUNT(*) FROM Contenido WHERE categoria_id = :catid AND estado = 'disponible' AND id <> :actual");
$stmtTotal->execute(['catid' => $contenido['categoria_id'], 'actual' => $contenido['id']]);
$totalPaginas = ceil($stmtTotal->fetchColumn() / $porPagina);

$stmtMas = $conn->prepare("
    SELECT c.id, c.titulo, c.archivo, t.nombre_del_tipo AS tipo_archivo
    FROM Contenido c
    LEFT JOIN TipoArchivo t ON c.tipo_archivo_id = t.id
    WHERE c.categoria_id = :catid AND c.estado = 'disponible' AND c.id <> :actual
    ORDER BY c.fecha_de_subida DESC
    LIMIT :lim OFFSET :off
");
$stmtMas->bindValue(':catid', $contenido['categoria_id'], PDO::PARAM_INT);
$stmtMas->bindValue(':actual', $contenido['id'], PDO::PARAM_INT);
$stmtMas->bindValue(':lim', $porPagina, PDO::PARAM_INT);
$stmtMas->bindValue(':off', $offset, PDO::PARAM_INT);
$stmtMas->execute();
$masContenidos = $stmtMas->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($contenido['titulo']); ?> - Contenido</title>
    <!-- Carga los estilos principales de usuario -->
    <link rel="stylesheet" href="../css/user/vista_usuario.css"> <!-- Esta es de la barrita de arriba -->
    <link rel="stylesheet" href="../css/user/busqueda_usuario.css">
    <link rel="stylesheet" href="../css/user/contenido_usuario.css">
</head>
<body>
    <!-- Header principal con datos del usuario -->
    <header class="header">
        <span class="logo">VISIO</span>
        <div class="header-right">
            <span class="user-nick">
                <?= htmlspecialchars($usuarioHeader['nickname'] ?? 'Usuario') ?>
            </span>
            <img src="<?= !empty($usuarioHeader['foto']) ? '../uploads/' . htmlspecialchars($usuarioHeader['foto']) : '../assets/placeholder_usuario.jpg' ?>" alt="Avatar" class="user-avatar">
            <a href="mi_perfil.php" class="header-btn">Mi Perfil</a>
            <a href="../sesion/logout.php" class="header-btn">Cerrar sesión</a>
            <a href="<?= htmlspecialchars($volverUrl) ?>" class="header-btn volver-btn">Volver </a>
        </div>
    </header>

    <!-- Contenedor principal de la vista de contenido -->
    <div class="contenido-flex">
        <div class="busqueda-container principal">
            <h1><?php echo htmlspecialchars($contenido['titulo']); ?></h1>
            <p><strong>Autor:</strong> <?php echo htmlspecialchars($contenido['autor']); ?></p>
            <p><strong>Categoría:</strong> <?php echo htmlspecialchars($contenido['categoria']); ?></p>
            <p><strong>Descripción:</strong> <?php echo htmlspecialchars($contenido['descripcion']); ?></p>
            <p><strong>Precio:</strong> $<?php echo number_format($contenido['precio_original'], 2); ?></p>
            <p><strong>Tamaño:</strong> <?php echo htmlspecialchars($contenido['tamano_mb']); ?> MB</p>
            <p><strong>Fecha de subida:</strong> <?php echo htmlspecialchars($contenido['fecha_de_subida']); ?></p>
            <p><strong>Tipo de archivo:</strong> <?php echo htmlspecialchars($contenido['tipo_archivo']); ?></p>

            <?php if (!empty($contenido['archivo'])): ?>
                <div style="margin:20px 0;">
                    <?php 
                    // Muestra la vista previa segun el tipo de archivo
                    $valoresImagen = ['imagen', 'jpeg', 'jpg', 'png'];
                    $valoresVideo  = ['video', 'quicktime', 'mp4'];
                    $valoresAudio  = ['audio', 'mpeg'];

                    if (in_array($tipo, $valoresImagen)) {
                        echo "<img src='{$rutaArchivo}' alt='Imagen del contenido' style='max-width:100%;max-height:400px;border-radius:8px;'>";
                    } elseif (in_array($tipo, $valoresVideo)) {
                        echo "<video controls style='max-width:100%;border-radius:8px;'>
                                <source src='{$rutaArchivo}' type='video/mp4'>
                                Tu navegador no soporta la etiqueta de video.
                              </video>";
                    } elseif (in_array($tipo, $valoresAudio)) {
                        echo "<audio controls>
                                <source src='{$rutaArchivo}' type='audio/mpeg'>
                                Tu navegador no soporta la etiqueta de audio.
                              </audio>";
                    } else {
                        echo "<p>Archivo: " . htmlspecialchars($contenido['archivo']) . "</p>";
                        echo "<p>Tipo de archivo no reconocido para vista previa.</p>";
                    }
                    ?>
                </div>
            <?php else: ?>
                <p>No hay archivo multimedia asociado a este contenido.</p>
            <?php endif; ?>

            <!-- Enlace para volver a la busqueda si existe -->
            <a href="usuario_buscar.php<?php echo isset($_SESSION['last_search']) ? '?q=' . urlencode($_SESSION['last_search']) : ''; ?>" style="margin-left:10px;">Volver a búsqueda</a>
            
            <?php if (isset($_SESSION['mensaje'])): ?>
                <!-- Muestra mensaje de exito si existe -->
                <div class="success-message"><?php echo htmlspecialchars($_SESSION['mensaje']); unset($_SESSION['mensaje']); ?></div>
            <?php endif; ?>

            <?php if ($yaDescargado): ?>
                <!-- Si ya descargo, muestra boton para descargar -->
                <form action="descargar_contenido_directo.php" method="post">
                    <input type="hidden" name="contenido_id" value="<?php echo $contenido['id']; ?>">
                    <button type="submit" class="btn btn-primary">Descargar</button>
                </form>
            <?php else: ?>
                <!-- Si no ha descargado, muestra boton para comprar y descargar -->
                <a href="descargar_contenido.php?id=<?php echo urlencode($contenido['id']); ?>" class="btn btn-primary">Comprar y Descargar</a>
            <?php endif; ?>

            <!-- Boton para regalar contenido -->
            <a href="regalar_contenido.php?id=<?php echo urlencode($contenido['id']); ?>" class="btn btn-secondary">Regalar</a>

            <?php if ($yaDescargado): ?>
                <!-- Boton para calificar contenido si ya descargo -->
                <a href="calificar_contenido.php?id=<?php echo urlencode($contenido['id']); ?>" class="btn btn-secondary">Calificar</a>
            <?php endif; ?>

            <!-- Boton para volver usando la url calculada -->
            <a href="<?= htmlspecialchars($volverUrl) ?>" class="header-btn volver-btn">Volver </a>
        </div>

        <!-- Galeria lateral de mas contenidos de la misma categoria -->
        <div class="galeria-lateral">
            <h2>Más contenidos de esta categoría</h2>
            <?php if (empty($masContenidos)): ?>
                <p>No hay más contenidos en esta categoría.</p>
            <?php else: ?>
                <div class="galeria">
                    <?php foreach ($masContenidos as $cont): 
                        // Determina el tipo de archivo para mostrar el icono o imagen
                        $tipo = strtolower($cont['tipo_archivo'] ?? pathinfo($cont['archivo'], PATHINFO_EXTENSION));
                        $rutaArchivo = "../uploads/" . htmlspecialchars($cont['archivo']);
                        $esImagen = in_array($tipo, ['imagen', 'jpeg', 'jpg', 'png']);
                        $esVideo = in_array($tipo, ['video', 'mp4', 'quicktime']);
                        $esAudio = in_array($tipo, ['audio', 'mp3', 'mpeg']);
                    ?>
                    <div class="galeria-item">
                        <a href="contenido_usuario.php?id=<?php echo urlencode($cont['id']); ?>">
                            <?php if ($esImagen && file_exists(__DIR__ . "/../uploads/" . $cont['archivo'])): ?>
                                <img src="<?php echo $rutaArchivo; ?>" alt="Imagen" class="galeria-thumb">
                            <?php elseif ($esVideo): ?>
                                <div class="galeria-icon galeria-video"></div>
                            <?php elseif ($esAudio): ?>
                                <div class="galeria-icon galeria-audio"></div>
                            <?php else: ?>
                                <div class="galeria-icon galeria-otro"></div>
                            <?php endif; ?>
                            <div class="galeria-titulo"><?php echo htmlspecialchars($cont['titulo']); ?></div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <!-- Paginacion para la galeria lateral -->
                <div class="paginacion">
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <a href="contenido_usuario.php?id=<?php echo urlencode($contenido['id']); ?>&pagina=<?php echo $i; ?>"
                           class="btn<?php echo $i == $pagina ? ' btn-primary' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>