<?php
// CU-012 Agregar Categoría
// Inicia la sesión y verifica que el usuario sea administrador
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../sesion/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Categoría</title>
    <link rel="stylesheet" href="../css/admin/agregar_categoria.css">
</head>
<body>
  <!-- Barra superior con buscador y acciones rápidas -->
  <div class="top-bar">
    <div class="search-section">
      <!-- Formulario de búsqueda -->
      <form action="admin_busqueda.php" method="get" style="display:flex;gap:8px;">
        <input type="text" name="q" placeholder="Buscar..." required>
        <button type="submit">Buscar</button>
      </form>
    </div>
    <div class="action-buttons">
      <!-- Botón para agregar contenido -->
      <form action="agregar_contenido.php" method="get">
        <button type="submit">Agregar Contenido</button>
      </form>
      <!-- Botón para agregar categoría (actual) -->
      <form action="agregar_categoria.php" method="get">
        <button type="submit">Agregar Categoría</button>
      </form>
      <!-- Botón para cerrar sesión -->
      <form action="logout.php" method="get">
        <button type="submit">Cerrar Sesión</button>
      </form>
    </div>
  </div>
  <div class="admin-container">
    <h1>Agregar Categoría</h1>
    <?php 
      // Muestra el mensaje de éxito o error si existe en la sesión
      if (isset($_SESSION['mensaje_categoria'])) {
          echo '<p>' . htmlspecialchars($_SESSION['mensaje_categoria']) . '</p>';
          unset($_SESSION['mensaje_categoria']);
      }
    ?>
    <!-- Formulario para agregar una nueva categoría -->
    <form action="agregar_categoria_wrapper.php" method="post">
        <label for="nombre">Ingresar nombre (3-50 caracteres):</label>
        <input type="text" id="nombre" name="nombre" required minlength="3" maxlength="50"
               pattern="^[a-zA-ZáéíóúñÑ\s]+$" title="Solo letras y espacios">
        <button type="submit">Agregar Categoría</button>
    </form>
    <!-- Botón para volver al panel principal -->
    <a href="admin.php" class="btn-volver"><button type="button">Volver</button></a>
  </div>
</body>
</html>