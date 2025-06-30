<?php
session_start();

// CU - 007 Buscar (Parte del Admin para Contenido)
// CU - 015 Eliminar (Parte para Contenido)

// Verifica que el usuario sea administrador
if (!isset($_SESSION['admin'])) {
    header("Location: ../sesion/login.php");
    exit;
}

require_once __DIR__ . "/../../config/db.php";

// Obtiene el id del contenido desde GET
$contenidoId = $_GET['id'] ?? null;
if (!$contenidoId) {
    $_SESSION['mensaje'] = "Contenido no especificado.";
    header("Location: admin.php");
    exit;
}

// Consulta para obtener los datos del contenido y su promocion si existe
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
        t.nombre_del_tipo AS tipo_archivo, 
        cat.nombre AS categoria,
        p.porcentaje_de_descuento,
        p.fecha_inicio,
        p.fecha_fin
    FROM Contenido c
    LEFT JOIN TipoArchivo t ON c.tipo_archivo_id = t.id
    LEFT JOIN Categoria cat ON c.categoria_id = cat.id
    LEFT JOIN Promocion p ON c.promocion_id = p.id
    WHERE c.id = :id
");
$stmt->execute(['id' => $contenidoId]);
$contenido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contenido) {
    $_SESSION['mensaje'] = "Contenido no encontrado.";
    header("Location: admin_busqueda.php");
    exit;
}

// Ruta del archivo y tipo para mostrar la vista previa
$rutaArchivo = "../uploads/" . htmlspecialchars($contenido['archivo']);
$tipo = strtolower($contenido['tipo_archivo']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Contenido - Administrador</title>

    <!-- CSS VAN AQUI                         -->
    <link rel="stylesheet" href="../css/admin/ContenidoAdmin.css">
    <!-- CSS VAN AQUI                         -->

</head>
<body>
    <div class="admin-container">
        <div class="header">
            <h1>Detalle del Contenido</h1>
            <!-- Enlace para volver al panel principal -->
            <a href="admin.php" class="back-link">← Volver al panel</a>
        </div>
        
        <div class="content-detail">
            <div class="content-info">
                <h2><?php echo htmlspecialchars($contenido['titulo']); ?></h2>
                
                <div class="detail-item">
                    <span class="detail-label">Autor</span>
                    <div class="detail-value"><?php echo htmlspecialchars($contenido['autor']); ?></div>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Descripción</span>
                    <div class="description"><?php echo htmlspecialchars($contenido['descripcion']); ?></div>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Precio</span>
                    <div class="price-container">
                        <?php if (!empty($contenido['porcentaje_de_descuento'])): 
                            $precioOriginal = floatval($contenido['precio_original']);
                            $descuento = floatval($contenido['porcentaje_de_descuento']);
                            $precioPromocion = $precioOriginal * (1 - ($descuento / 100));
                        ?>
                            <span class="original-price">$<?php echo number_format($precioOriginal, 2, '.', ','); ?></span>
                            <span class="discount-price">$<?php echo number_format($precioPromocion, 2, '.', ','); ?></span>
                            <span class="discount-badge"><?php echo number_format($descuento, 0); ?>% OFF</span>
                        <?php else: ?>
                            <span class="discount-price">$<?php echo number_format($contenido['precio_original'], 2, '.', ','); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Tamaño</span>
                    <div class="detail-value"><?php echo htmlspecialchars($contenido['tamano_mb']); ?> MB</div>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Fecha de Subida</span>
                    <div class="detail-value"><?php echo htmlspecialchars($contenido['fecha_de_subida']); ?></div>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Tipo de Archivo</span>
                    <div class="detail-value"><?php echo htmlspecialchars($contenido['tipo_archivo']); ?></div>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Categoría</span>
                    <div class="detail-value"><?php echo htmlspecialchars($contenido['categoria']); ?></div>
                </div>
                
                <?php if (!empty($contenido['porcentaje_de_descuento'])): ?>
                    <div class="promotion-section">
                        <h3 class="promotion-title">Promoción Activa</h3>
                        <div class="detail-item">
                            <span class="detail-label">Descuento</span>
                            <div class="detail-value"><?php echo htmlspecialchars($contenido['porcentaje_de_descuento']); ?>%</div>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Inicio</span>
                            <div class="detail-value"><?php echo htmlspecialchars($contenido['fecha_inicio']); ?></div>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Fin</span>
                            <div class="detail-value"><?php echo htmlspecialchars($contenido['fecha_fin']); ?></div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="action-buttons">
                    <!-- Boton para eliminar el contenido -->
                    <form action="borrar.php" method="post" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar este contenido?');">
                      <input type="hidden" name="tipo" value="contenido">
                      <input type="hidden" name="id" value="<?php echo htmlspecialchars($contenidoId); ?>">
                      <button type="submit" class="btn btn-danger">Eliminar Contenido</button>
                    </form>
                    <?php if (empty($contenido['porcentaje_de_descuento'])): ?>
                        <a href="agregar_promocion.php?id=<?php echo urlencode($contenido['id']); ?>" class="btn btn-primary">
                            Agregar Promoción
                        </a>
                    <?php endif; ?>
                    <!-- Si quieres permitir modificar, agrega aqui el boton solo si lo permites -->
                    <!-- <a href="editar_contenido.php?id=<?php echo urlencode($contenido['id']); ?>" class="btn btn-secondary">Editar Contenido</a> -->
                </div>
            </div>
            
            <div class="content-media">
                <?php if (!empty($contenido['archivo'])): ?>
                    <div class="media-container">
                        <?php 
                        // Determina el tipo de archivo para mostrar la vista previa adecuada
                        $valoresImagen = ['imagen', 'jpeg', 'jpg', 'png'];
                        $valoresVideo  = ['video', 'quicktime', 'mp4'];
                        $valoresAudio  = ['audio', 'mpeg'];
                        
                        if (in_array($tipo, $valoresImagen)) {
                            echo "<img src='{$rutaArchivo}' alt='Imagen del contenido'>";
                        } elseif (in_array($tipo, $valoresVideo)) {
                            echo "<video controls>
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
            </div>
        </div>
    </div>
</body>
</html>