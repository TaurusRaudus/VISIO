<?php
session_start();
// Verifica que el usuario sea administrador
if (!isset($_SESSION['admin'])) {
    header("Location: ../sesion/login.php");
    exit;
}
require_once __DIR__ . "/../../config/db.php";

// Verifica que se reciba el id de la categoria
if (!isset($_GET['id'])) {
    $_SESSION['mensaje'] = "No se especificó la categoría.";
    header("Location: admin.php");
    exit;
}

$catId = $_GET['id'];

// Obtener los datos de la categoria
$stmt = $conn->prepare("SELECT * FROM Categoria WHERE id = :id");
$stmt->execute(['id' => $catId]);
$categoria = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$categoria) {
    $_SESSION['mensaje'] = "Categoría no encontrada.";
    header("Location: admin.php");
    exit;
}

// Paginacion para los contenidos
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) { 
    $page = 1; 
}
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Contar el total de contenidos en la categoria
$stmtCount = $conn->prepare("SELECT COUNT(*) as total FROM Contenido WHERE categoria_id = :id AND estado = 'disponible'");
$stmtCount->execute(['id' => $catId]);
$totalContents = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalContents / $items_per_page);

// Obtener los contenidos asociados a esta categoria (solo disponibles)
$stmtContents = $conn->prepare("SELECT id, titulo, archivo FROM Contenido WHERE categoria_id = :id AND estado = 'disponible' ORDER BY fecha_de_subida DESC LIMIT :limit OFFSET :offset");
$stmtContents->bindValue(':id', $catId, PDO::PARAM_INT);
$stmtContents->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmtContents->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtContents->execute();
$contenidos = $stmtContents->fetchAll(PDO::FETCH_ASSOC);

// Obtener subcategorias activas de esta categoria (sin paginacion)
$stmtSubcat = $conn->prepare("SELECT * FROM Categoria WHERE padre_id = :id AND estado = 'activa'");
$stmtSubcat->execute(['id' => $catId]);
$subcategorias = $stmtSubcat->fetchAll(PDO::FETCH_ASSOC);

/**
 * Obtener el camino de padres (breadcrumb)
 */
function obtenerCaminoPadresAdmin($conn, $catId) {
    $camino = [];
    while ($catId) {
        $stmt = $conn->prepare("SELECT id, nombre, padre_id FROM Categoria WHERE id = :id");
        $stmt->execute(['id' => $catId]);
        $cat = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($cat) {
            array_unshift($camino, $cat); // Insertar al inicio
            $catId = $cat['padre_id'];
        } else {
            break;
        }
    }
    return $camino;
}

// Obtener filtro de tipo de archivo
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Administrar Categoría</title>
  <link rel="stylesheet" href="../css/admin/categoria_admin.css">
</head>
<body>
  <div class="admin-container">
    <!-- Informacion de la Categoria -->
    <h1><?php echo htmlspecialchars($categoria['nombre']); ?></h1>
    <?php if (!empty($categoria['descripcion'])): ?>
      <p><?php echo htmlspecialchars($categoria['descripcion']); ?></p>
    <?php endif; ?>

    <!-- Breadcrumb -->
    <nav class="breadcrumb-admin">
      <?php
        $camino = obtenerCaminoPadresAdmin($conn, $catId);
        foreach ($camino as $i => $cat) {
          if ($i > 0) echo ' &raquo; ';
          if ($cat['id'] == $catId) {
            echo '<span class="active">'.htmlspecialchars($cat['nombre']).'</span>';
          } else {
            echo '<a href="categoria_admin.php?id='.urlencode($cat['id']).'">'.htmlspecialchars($cat['nombre']).'</a>';
          }
        }
      ?>
    </nav>

    <!-- Layout de dos columnas -->
    <div class="main-layout">
      <!-- Columna Izquierda: Contenidos en forma de cards -->
      <div class="main-content">
        <h2>Contenidos en esta categoría</h2>
        <?php if (empty($contenidos)): ?>
          <p>No se encontraron contenidos.</p>
        <?php else: ?>
          <div class="cards">
            <?php foreach ($contenidos as $contenido): ?>
              <?php
                // Determina el tipo de archivo y si debe mostrarse segun el filtro
                $archivo = $contenido['archivo'] ?? '';
                $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
                $extensiones_imagen = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $extensiones_video = ['mp4', 'mov', 'avi', 'mkv', 'webm'];
                $extensiones_audio = ['mp3', 'wav', 'ogg', 'aac', 'flac'];
                $mostrar = false;
                if ($filtro === 'todos') {
                  $mostrar = true;
                } elseif ($filtro === 'imagen' && in_array($extension, $extensiones_imagen)) {
                  $mostrar = true;
                } elseif ($filtro === 'video' && in_array($extension, $extensiones_video)) {
                  $mostrar = true;
                } elseif ($filtro === 'sonido' && in_array($extension, $extensiones_audio)) {
                  $mostrar = true;
                }
                if (!$mostrar) continue;
                $img = !empty($contenido['archivo']) 
                         ? "../uploads/" . htmlspecialchars($contenido['archivo']) 
                         : "../assets/placeholder_contenido.jpg";
              ?>
              <div class="card">
                <a href="contenido_admin.php?id=<?php echo urlencode($contenido['id']); ?>">
                  <img src="<?php echo $img; ?>" alt="Imagen de <?php echo htmlspecialchars($contenido['titulo']); ?>">
                  <h3><?php echo htmlspecialchars($contenido['titulo']); ?></h3>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
          <!-- Paginacion numerica -->
          <?php if ($totalPages > 1): ?>
            <div class="pagination">
              <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <?php $class = ($p == $page) ? 'active' : ''; ?>
                <a href="categoria_admin.php?id=<?php echo urlencode($catId); ?>&page=<?php echo $p; ?>" class="<?php echo $class; ?>"><?php echo $p; ?></a>
              <?php endfor; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
      
      <!-- Columna Derecha: Sidebar para subcategorias -->
      <div class="sidebar subcat-sidebar">
        <h3>Subcategorías</h3>
        <?php if (empty($subcategorias)): ?>
          <p>No se encontraron subcategorías.</p>
        <?php else: ?>
          <ul class="subcat-list">
            <?php foreach ($subcategorias as $subcat): ?>
              <li>
                <a href="categoria_admin.php?id=<?php echo urlencode($subcat['id']); ?>">
                  <?php echo htmlspecialchars($subcat['nombre']); ?>
                </a>
                <form action="borrar.php" method="post" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar esta subcategoría y todo su contenido?');">
                  <input type="hidden" name="tipo" value="subcategoria">
                  <input type="hidden" name="id" value="<?php echo htmlspecialchars($subcat['id']); ?>">
                  <button type="submit" style="background:#dc3545;color:#fff;border:none;padding:2px 8px;border-radius:3px;font-size:0.9em;">Eliminar</button>
                </form>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
        <!-- Boton para agregar subcategoria -->
        <div class="add-subcat-btn">
          <form action="agregar_subcategoria.php" method="get" style="display:inline;">
            <input type="hidden" name="padre_id" value="<?php echo htmlspecialchars($catId); ?>">
            <button type="submit">Agregar SubCategoría</button>
          </form>
        </div>

        <!-- Boton para eliminar la categoria -->
        <form action="borrar.php" method="post" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar esta categoría y todo su contenido?');">
          <input type="hidden" name="tipo" value="categoria">
          <input type="hidden" name="id" value="<?php echo htmlspecialchars($catId); ?>">
          <button type="submit" class="btn-eliminar-categoria">Eliminar Categoría</button>
        </form>

        <!-- Breadcrumb y filtros -->
        <nav class="breadcrumb-admin">
          <?php
            $camino = obtenerCaminoPadresAdmin($conn, $catId);
            foreach ($camino as $i => $cat) {
              if ($i > 0) echo ' &raquo; ';
              if ($cat['id'] == $catId) {
                echo '<span class="active">'.htmlspecialchars($cat['nombre']).'</span>';
              } else {
                echo '<a href="categoria_admin.php?id='.urlencode($cat['id']).'">'.htmlspecialchars($cat['nombre']).'</a>';
              }
            }
          ?>
        </nav>
        <div class="admin-filtros-tipo">
          <form method="get" action="categoria_admin.php" class="admin-filtros-tipo-form">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($catId); ?>">
            <?php
              // Botones de filtro por tipo de archivo
              $tipos = [
                'todos' => 'Todos',
                'imagen' => 'Imágenes',
                'video' => 'Videos',
                'sonido' => 'Sonidos'
              ];
              foreach ($tipos as $key => $label) {
                $active = ($filtro === $key) ? 'active' : '';
                echo '<button type="submit" name="filtro" value="'.$key.'" class="admin-filtro-btn '.$active.'">'.$label.'</button>';
              }
            ?>
          </form>
        </div>
      </div>
    </div>
    
    <p><a href="admin.php">Volver al panel principal</a></p>
  </div>
</body>
</html>