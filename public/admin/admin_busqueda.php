<?php
// Inicia la sesión y verifica que el usuario sea administrador
session_start();
if (!isset($_SESSION['admin'])) {
    // Si no hay sesión de admin, redirige al login
    header("Location: ../sesion/login.php");
    exit;
}
// Obtiene el nombre a mostrar del admin desde la sesión, o usa un valor por defecto
$displayName = isset($_SESSION['displayName']) ? $_SESSION['displayName'] : "Administrador";

require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../src/admin/AdminBusqueda.php";

// FUN-075 Controlador principal de búsqueda para admin
// Orquesta la obtención de parámetros, ejecución de búsqueda y renderizado de la vista
function mainBusquedaAdmin($conn, $displayName) {
    $buscador = new AdminBusqueda($conn);
    // Obtiene los parámetros de búsqueda y paginación
    list($q, $type, $page, $items_per_page, $offset, $mensaje_busqueda) = $buscador->obtenerParametros();

    $results = [];
    $totalResults = 0;
    // Si no hay mensaje de error y hay tipo de búsqueda, ejecuta la búsqueda
    if (empty($mensaje_busqueda) && !empty($type)) {
        list($results, $totalResults) = $buscador->buscar($q, $type, $items_per_page, $offset);
    }
    $totalPages = ceil($totalResults / $items_per_page);

    // Renderiza la vista con los resultados
    renderVistaBusquedaAdmin($conn, $displayName, $q, $type, $page, $results, $totalResults, $totalPages, $items_per_page, $mensaje_busqueda);
}

// FUN-076 Renderizar la vista de búsqueda para admin
// Muestra la interfaz de búsqueda, filtros, resultados y paginación
function renderVistaBusquedaAdmin($conn, $displayName, $q, $type, $page, $results, $totalResults, $totalPages, $items_per_page, $mensaje_busqueda) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administrador</title>
    <link rel="stylesheet" href="../css/admin/admin_header.css">
    <link rel="stylesheet" href="../css/admin/admin.css">
</head>
<body>
    <!-- Encabezado del panel de administrador -->
    <header class="admin-header">
        <span class="admin-logo">VISIO</span>
        <div class="admin-header-right">
            <span class="admin-nick"><?php echo htmlspecialchars($displayName); ?></span>
            <!-- Botón para cerrar sesión -->
            <a href="../sesion/logout.php" class="admin-header-btn">Cerrar sesión</a>
        </div>
    </header>
    <div class="admin-busqueda-layout">
        <div class="admin-busqueda-main">
            <h1>Bienvenido, <?php echo htmlspecialchars($displayName); ?></h1>
            <!-- Sección de búsqueda y acciones rápidas -->
            <div class="search-section">
                <!-- Barra de búsqueda -->
                <form action="admin_busqueda.php" method="get" class="search-bar">
                    <input type="text" name="q" placeholder="Buscar contenido, categoría o usuario..." 
                           value="<?php echo htmlspecialchars($q); ?>" required>
                </form>
                <!-- Botones de acción para agregar contenido, categoría o cerrar sesión -->
                <div class="action-buttons">
                    <form action="agregar_contenido.php" method="get"> 
                        <button type="submit">Agregar Contenido</button>
                    </form>
                    <form action="agregar_categoria.php" method="get">
                        <button type="submit">Agregar Categoría</button>
                    </form>
                    <form action="../sesion/logout.php" method="get">
                        <button type="submit">Cerrar Sesion</button>
                    </form>
                </div>
            </div>
            <!-- Filtros para tipo de búsqueda -->
            <div class="filter-buttons">
                <a href="admin_busqueda.php?q=<?php echo urlencode($q); ?>&type=contenido<?php echo isset($_GET['filtro']) ? '&filtro=' . urlencode($_GET['filtro']) : ''; ?>">
                    <button <?php if($type === 'contenido') echo 'style="background-color:#007bff; color:white;"'; ?>>Contenido</button>
                </a>
                <a href="admin_busqueda.php?q=<?php echo urlencode($q); ?>&type=categoria">
                    <button <?php if($type === 'categoria') echo 'style="background-color:#007bff; color:white;"'; ?>>Categoría</button>
                </a>
                <a href="admin_busqueda.php?q=<?php echo urlencode($q); ?>&type=usuario">
                    <button <?php if($type === 'usuario') echo 'style="background-color:#007bff; color:white;"'; ?>>Usuario</button>
                </a>
            </div>
            <!-- Sección de resultados de búsqueda -->
            <div class="results-section">
                <h1>Resultados de Busqueda para: "<?php echo htmlspecialchars($q); ?>"</h1>
                <?php
                // Muestra mensaje de error si existe
                if (!empty($mensaje_busqueda)) {
                    echo '<p class="error-message">' . htmlspecialchars($mensaje_busqueda) . '</p>';
                } else {
                    if (!empty($type)) {
                        // Si no hay resultados, muestra mensaje
                        if (empty($results)) {
                            echo '<p class="no-results">No se pudo encontrar el elemento deseado, error.</p>';
                        } else {
                            // Renderiza las tarjetas de resultados según el tipo
                            echo '<div class="cards">';
                            // IMPORTANTE
                            if ($type === 'contenido') {
                                // Filtrado por tipo de archivo
                                $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';
                                $extensiones_imagen = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                $extensiones_video = ['mp4', 'mov', 'avi', 'mkv', 'webm'];
                                $extensiones_audio = ['mp3', 'wav', 'ogg', 'aac', 'flac'];
                                foreach ($results as $contenido) {
                                    $archivo = $contenido['archivo'] ?? '';
                                    $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
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
                                    $img = !empty($contenido['archivo']) ? "../uploads/" . htmlspecialchars($contenido['archivo'])
                                                                         : "../assets/placeholder_contenido.jpg";
                                    echo '<div class="card">';
                                    echo '<a href="contenido_admin.php?id=' . urlencode($contenido['id']) . '">';
                                    echo '<img src="' . $img . '" alt="Imagen de ' . htmlspecialchars($contenido['titulo']) . '">';
                                    echo '<h3>' . htmlspecialchars($contenido['titulo']) . '</h3>';
                                    echo '</a>';
                                    echo '<p>Autor: ' . htmlspecialchars($contenido['autor']) . '</p>';
                                    echo '<p>Precio: ' . htmlspecialchars($contenido['precio_original']) . '</p>';
                                    echo '</div>';
                                }
                            } elseif ($type === 'categoria') {
                                foreach ($results as $categoria) {
                                    // Busca una imagen de contenido para la categoría
                                    $stmtImg = $conn->prepare("SELECT archivo FROM Contenido WHERE categoria_id = :catid AND tipo_archivo_id = '1' AND estado = 'disponible' ORDER BY fecha_de_subida DESC LIMIT 1");
                                    $stmtImg->execute(['catid' => $categoria['id']]);
                                    $imgRow = $stmtImg->fetch(PDO::FETCH_ASSOC);
                                    $img = (!empty($imgRow['archivo']))
                                        ? "../uploads/" . htmlspecialchars($imgRow['archivo'])
                                        : "../assets/placeholder_categoria.jpg";
                                    echo '<div class="card">';
                                    echo '<a href="categoria_admin.php?id=' . urlencode($categoria['id']) . '">';
                                    echo '<img src="' . $img . '" alt="Imagen de ' . htmlspecialchars($categoria['nombre']) . '">';
                                    echo '<h3>' . htmlspecialchars($categoria['nombre']) . '</h3>';
                                    echo '</a>';
                                    echo '<p>' . (is_null($categoria['padre_id']) ? 'Categoría' : 'SubCategoría') . '</p>';
                                    echo '</div>';
                                }
                            } elseif ($type === 'usuario') {
                                foreach ($results as $usuario) {
                                    $img = !empty($usuario['foto']) ? "../uploads/" . htmlspecialchars($usuario['foto'])
                                                                    : "../assets/placeholder_usuario.jpg";
                                    echo '<div class="card">';
                                    echo '<a href="administrar_usuario.php?id=' . urlencode($usuario['id']) . '">';
                                    echo '<img src="' . $img . '" alt="Imagen de ' . htmlspecialchars($usuario['nickname']) . '">';
                                    echo '<h3>' . htmlspecialchars($usuario['nickname']) . '</h3>';
                                    echo '</a>';
                                    echo '<p>' . htmlspecialchars($usuario['correo_electronico']) . '</p>';
                                    echo '</div>';
                                }
                            }
                            echo '</div>';
                            // Paginación si hay más de una página
                            if ($totalPages > 1) {
                                echo '<div class="pagination">';
                                for ($p = 1; $p <= $totalPages; $p++) {
                                    $class = ($p == $page) ? 'active' : '';
                                    $filtroParam = ($type === 'contenido' && isset($_GET['filtro'])) ? '&filtro=' . urlencode($_GET['filtro']) : '';
                                    echo '<a href="admin_busqueda.php?q=' . urlencode($q) . '&type=' . urlencode($type) . $filtroParam . '&page=' . $p . '" class="' . $class . '">' . $p . '</a>';
                                }
                                echo '</div>';
                            }
                        }
                    } else {
                        // Si no se ha seleccionado filtro, muestra mensaje
                        echo '<p>Seleccione una opción de filtro para ver los resultados.</p>';
                    }
                }
                ?>
            </div>
        </div>
        <?php if ($type === 'contenido'): ?>
        <aside class="admin-filtros-ranking">
            <h3>Filtrar por tipo de archivo</h3>
            <form class="admin-filtros-tipo-radio" method="get" action="admin_busqueda.php">
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($q); ?>">
                <input type="hidden" name="type" value="contenido">
                <?php
                $tipos = [
                    'todos' => 'Todos',
                    'imagen' => 'Imágenes',
                    'video' => 'Videos',
                    'sonido' => 'Sonidos'
                ];
                $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';
                foreach ($tipos as $key => $label) {
                    $checked = ($filtro === $key) ? 'checked' : '';
                    echo '<label class="admin-radio-btn"><input type="radio" name="filtro" value="'.$key.'" '.$checked.' onchange="this.form.submit();"><span>'.$label.'</span></label>';
                }
                ?>
            </form>
        </aside>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
}

// Ejecuta el controlador principal de búsqueda para admin
mainBusquedaAdmin($conn, $displayName);