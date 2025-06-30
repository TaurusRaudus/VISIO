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

// Validar ID de categoría
if (!isset($_GET['id'])) {
    echo "Categoría no especificada.";
    exit;
}
$catId = $_GET['id'];

// Obtener datos de la categoría (solo activa)
$stmt = $conn->prepare("SELECT * FROM Categoria WHERE id = :id AND estado = 'activa'");
$stmt->execute(['id' => $catId]);
$categoria = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$categoria) {
    echo "Categoría no encontrada o inactiva.";
    exit;
}

// Paginación
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$porPagina = 10;
$offset = ($pagina - 1) * $porPagina;

// Contar contenidos disponibles en la categoría
$stmtTotal = $conn->prepare("SELECT COUNT(*) FROM Contenido WHERE categoria_id = :catid AND estado = 'disponible'");
$stmtTotal->execute(['catid' => $catId]);
$total = $stmtTotal->fetchColumn();
$totalPaginas = ceil($total / $porPagina);

// Obtener contenidos de la categoría (solo disponibles)
$stmtContents = $conn->prepare("SELECT * FROM Contenido WHERE categoria_id = :catid AND estado = 'disponible' ORDER BY fecha_de_subida DESC LIMIT :lim OFFSET :off");
$stmtContents->bindValue(':catid', $catId, PDO::PARAM_INT);
$stmtContents->bindValue(':lim', $porPagina, PDO::PARAM_INT);
$stmtContents->bindValue(':off', $offset, PDO::PARAM_INT);
$stmtContents->execute();
$contenidos = $stmtContents->fetchAll(PDO::FETCH_ASSOC);

/**
 * Obtener el camino de padres (breadcrumb)
 */
function obtenerCaminoPadres($conn, $catId) {
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

/**
 * Obtener solo las subcategorías hijas directas
 */
function obtenerHijas($conn, $catId) {
    $stmt = $conn->prepare("SELECT id, nombre FROM Categoria WHERE padre_id = :padre AND estado = 'activa' ORDER BY nombre ASC");
    $stmt->execute(['padre' => $catId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener filtro de tipo de archivo
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($categoria['nombre']); ?> - Categoría</title>
  <!-- Carga los estilos principales de usuario -->
  <link rel="stylesheet" href="../css/user/vista_usuario.css">
  <link rel="stylesheet" href="../css/user/busqueda_usuario.css">
  <link rel="stylesheet" href="../css/user/categoria_usuario.css">
</head>
<body>
  <!-- Header principal con datos del usuario -->
  <header class="header">
    <span class="logo">VISIO</span>
    <div class="header-right">
      <span class="user-nick"><?php echo htmlspecialchars($usuarioHeader['nickname'] ?? 'Usuario'); ?></span>
      <img src="<?php echo !empty($usuarioHeader['foto']) ? '../uploads/' . htmlspecialchars($usuarioHeader['foto']) : '../assets/placeholder_usuario.jpg'; ?>" alt="Avatar" class="user-avatar">
      <a href="mi_perfil.php" class="header-btn">Mi Perfil</a>
      <a href="../sesion/logout.php" class="header-btn">Cerrar sesión</a>
      <a href="usuario_buscar.php<?php echo isset($_SESSION['last_search']) ? '?q=' . urlencode($_SESSION['last_search']) : ''; ?>" class="header-btn volver-btn">Volver</a>
    </div>
  </header>
  <!-- Layout principal de la categoria -->
  <div class="main-layout">
    <div class="busqueda-container">
      <h1><?php echo htmlspecialchars($categoria['nombre']); ?></h1>
      <?php if (!empty($categoria['descripcion'])): ?>
        <p><?php echo htmlspecialchars($categoria['descripcion']); ?></p>
      <?php endif; ?>

      <h2>Contenidos en esta categoría</h2>
      <?php
      // Filtrado por tipo de archivo
      $extensiones_imagen = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
      $extensiones_video = ['mp4', 'mov', 'avi', 'mkv', 'webm'];
      $extensiones_audio = ['mp3', 'wav', 'ogg', 'aac', 'flac'];
      $hayContenido = false;
      ?>
      <div class="galeria">
        <?php foreach ($contenidos as $contenido):
            $archivo = $contenido['archivo'] ?? '';
            $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            $mostrar = false;
            // Aplica el filtro de tipo de archivo
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
            $hayContenido = true;
            $tipo = strtolower($contenido['tipo_archivo'] ?? pathinfo($contenido['archivo'], PATHINFO_EXTENSION));
            $rutaArchivo = "../uploads/" . htmlspecialchars($contenido['archivo']);
            $esImagen = in_array($tipo, ['imagen', 'jpeg', 'jpg', 'png']);
            $esVideo = in_array($tipo, ['video', 'mp4', 'quicktime']);
            $esAudio = in_array($tipo, ['audio', 'mp3', 'mpeg']);
            $archivoExiste = !empty($contenido['archivo']) && file_exists(__DIR__ . "/../uploads/" . $contenido['archivo']);
        ?>
          <!-- Card de contenido de la galeria -->
          <div class="galeria-item">
            <a href="contenido_usuario.php?id=<?php echo urlencode($contenido['id']); ?>">
              <?php if ($esImagen && $archivoExiste): ?>
                <img src="<?php echo $rutaArchivo; ?>" alt="Imagen" class="galeria-thumb">
              <?php elseif ($esVideo): ?>
                <div class="galeria-icon galeria-video"></div>
              <?php elseif ($esAudio): ?>
                <div class="galeria-icon galeria-audio"></div>
              <?php else: ?>
                <img src="../assets/placeholder_contenido.jpg" alt="Sin imagen" class="galeria-thumb">
              <?php endif; ?>
              <div class="galeria-titulo"><?php echo htmlspecialchars($contenido['titulo']); ?></div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if (!$hayContenido): ?>
        <!-- Mensaje si no hay contenidos para mostrar -->
        <p>No se encontraron contenidos.</p>
      <?php endif; ?>

      <!-- Paginacion de la galeria -->
      <div class="paginacion">
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
          <a href="categoria_usuario.php?id=<?php echo urlencode($catId); ?>&pagina=<?php echo $i; ?>&filtro=<?php echo urlencode($filtro); ?>"
             class="btn<?php echo $i == $pagina ? ' btn-primary' : ''; ?>">
            <?php echo $i; ?>
          </a>
        <?php endfor; ?>
      </div>

      <!-- Enlace para volver a la busqueda -->
      <a href="usuario_buscar.php<?php echo isset($_SESSION['last_search']) ? '?q=' . urlencode($_SESSION['last_search']) : ''; ?>" style="margin-left:10px;">Volver a búsqueda</a>
    </div>
    <!-- Sidebar de navegacion y filtros -->
    <aside class="sidebar-categorias">
      <h3>Navegación</h3>
      <!-- Breadcrumb de padres -->
      <nav class="breadcrumb-categorias">
        <?php
          $camino = obtenerCaminoPadres($conn, $catId);
          foreach ($camino as $i => $cat) {
            if ($i > 0) echo ' &raquo; ';
            if ($cat['id'] == $catId) {
              echo '<span class="active">'.htmlspecialchars($cat['nombre']).'</span>';
            } else {
              echo '<a href="categoria_usuario.php?id='.urlencode($cat['id']).'">'.htmlspecialchars($cat['nombre']).'</a>';
            }
          }
        ?>
      </nav>
      <hr>
      <!-- Subcategorías hijas -->
      <div class="hijas-categorias">
        <strong>Subcategorías:</strong>
        <ul class="arbol-categorias">
          <?php
            $hijas = obtenerHijas($conn, $catId);
            if (empty($hijas)) {
              echo '<li style="color:#888;">(Sin subcategorías)</li>';
            } else {
              foreach ($hijas as $hija) {
                echo '<li class="arbol-item"><a href="categoria_usuario.php?id='.urlencode($hija['id']).'">'.htmlspecialchars($hija['nombre']).'</a></li>';
              }
            }
          ?>
        </ul>
      </div>
      <!-- Panel de filtros debajo de navegación -->
      <div class="filtros-categoria-vertical">
        <h3>Filtrar por tipo</h3>
        <form class="filtros-tipo-radio" method="get" action="categoria_usuario.php">
          <input type="hidden" name="id" value="<?php echo htmlspecialchars($catId); ?>">
          <?php
          $tipos = [
            'todos' => 'Todos',
            'imagen' => 'Imágenes',
            'video' => 'Videos',
            'sonido' => 'Sonidos'
          ];
          foreach ($tipos as $key => $label) {
            $checked = ($filtro === $key) ? 'checked' : '';
            echo '<label class="radio-btn"><input type="radio" name="filtro" value="'.$key.'" '.$checked.' onchange="this.form.submit();"><span>'.$label.'</span></label>';
          }
          ?>
        </form>
      </div>
    </aside>
  </div>
</body>
</html>