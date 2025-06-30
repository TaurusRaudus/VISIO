<?php
// CU-014 Agregar subcategoria
session_start();
// Verifica que el usuario sea administrador
if (!isset($_SESSION['admin'])) {
    header("Location: ../sesion/login.php");
    exit;
}
require_once __DIR__ . "/../../config/db.php";

// Verifica que se haya recibido el id de la categoria padre
if (!isset($_GET['padre_id'])) {
    $_SESSION['mensaje_categoria'] = "No se especificó la categoría padre.";
    header("Location: admin.php");  // O la vista principal del administrador
    exit;
}

$padre_id = $_GET['padre_id'];

// Verifica que exista el padre en la base de datos
$stmt = $conn->prepare("SELECT * FROM Categoria WHERE id = :padre_id");
$stmt->execute(['padre_id' => $padre_id]);
$categoriaPadre = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no existe la categoria padre, redirige
if (!$categoriaPadre) {
    $_SESSION['mensaje_categoria'] = "La categoría padre no existe.";
    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar SubCategoría</title>
    <link rel="stylesheet" href="../css/admin/agregar_subcategoria.css">
</head>
<body>
  <div class="top-bar">
    <div class="search-section">
      <!-- Formulario de busqueda -->
      <form action="admin_busqueda.php" method="get" style="display:flex;gap:8px;">
        <input type="text" name="q" placeholder="Buscar..." required>
        <button type="submit">Buscar</button>
      </form>
    </div>
    <div class="action-buttons">
      <!-- Botones de acciones rapidas -->
      <form action="agregar_contenido.php" method="get">
        <button type="submit">Agregar Contenido</button>
      </form>
      <form action="agregar_categoria.php" method="get">
        <button type="submit">Agregar Categoría</button>
      </form>
      <form action="logout.php" method="get">
        <button type="submit">Cerrar Sesión</button>
      </form>
    </div>
  </div>
  <div class="admin-container">
    <h1>Agregar SubCategoría a: <?php echo htmlspecialchars($categoriaPadre['nombre']); ?></h1>
    <?php 
      // Muestra el mensaje de exito o error si existe en la sesion
      if (isset($_SESSION['mensaje_categoria'])) {
          echo '<p>' . htmlspecialchars($_SESSION['mensaje_categoria']) . '</p>';
          unset($_SESSION['mensaje_categoria']);
      }
    ?>
    <!-- Formulario para agregar una nueva subcategoria -->
    <form action="agregar_subcategoria_procesar.php" method="post">
        <input type="hidden" name="padre_id" value="<?php echo htmlspecialchars($padre_id); ?>">
        <label for="nombre">Ingresar nombre (3-50 caracteres):</label>
        <input type="text" id="nombre" name="nombre" required minlength="3" maxlength="50"
               pattern="^[a-zA-ZáéíóúñÑ\s]+$" title="Solo letras y espacios">
        <button type="submit">Agregar SubCategoría</button>
    </form>
    <!-- Boton para volver a la categoria padre -->
    <a href="categoria_admin.php?id=<?php echo urlencode($padre_id); ?>" class="btn-volver">
      <button type="button">Volver</button>
    </a>
  </div>
</body>
</html>