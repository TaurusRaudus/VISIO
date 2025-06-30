<?php
session_start();
// Verifica que el usuario sea administrador
if (!isset($_SESSION['admin'])) {
    header("Location: ../sesion/login.php");
    exit;
}
require_once __DIR__ . "/../../config/db.php";

// CU - 011 Agregar Contenido

// Obtener categorias activas para el selector
$stmt = $conn->query("SELECT id, nombre FROM Categoria WHERE estado = 'activa' ORDER BY nombre ASC");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Agregar Contenido</title>
    <!-- Enlace a la hoja de estilos -->
    <link rel="stylesheet" href="../css/admin/agregar_contenido.css">
</head>

<body>
    <h1>Agregar Contenido</h1>
    
    <?php 
    // Muestra el mensaje de éxito o error si existe en la sesión
    if (isset($_SESSION['mensaje'])): ?>
        <div class="message <?= $_SESSION['mensaje_tipo'] ?? '' ?>">
            <?= $_SESSION['mensaje'] ?>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']); ?>
    <?php endif; ?>

    <!-- Formulario para agregar un nuevo contenido -->
    <form action="agregar_contenido_wrapper.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="titulo">Título (5-60 caracteres):</label>
            <input type="text" name="titulo" id="titulo" required minlength="5" maxlength="60">
        </div>

        <div class="form-group">
            <label for="autor">Autor:</label>
            <input type="text" name="autor" id="autor" required>
        </div>

        <div class="form-group">
            <label for="precio">Precio (ej. 9.99):</label>
            <input type="number" name="precio" id="precio" required step="0.01" min="0">
        </div>

        <div class="form-group">
            <label for="formato">Formato:</label>
            <select name="formato" id="formato" required>
                <option value="Imagen">Imagen</option>
                <option value="Audio">Audio</option>
                <option value="Video">Video</option>
            </select>
        </div>

        <div class="form-group">
            <label for="categoria">Categoría:</label>
            <select name="categoria" id="categoria" required>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['id']) ?>">
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción:</label>
            <textarea name="descripcion" id="descripcion" rows="4"></textarea>
        </div>

        <div class="form-group">
            <label for="archivo">Archivo (máx. 100MB):</label>
            <input type="file" name="archivo" id="archivo" required>
        </div>

        <button type="submit">Agregar Contenido</button>
        <!-- Botón para volver al panel principal, con el mismo estilo de ancho -->
        <a href="admin.php" class="btn-volver" style="margin-left:10px;">
            <button type="button">Volver al Panel</button>
        </a>
    </form>
</body>
</html>