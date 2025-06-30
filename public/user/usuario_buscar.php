<?php
session_start();
// Verifica que el usuario este autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../sesion/login.php");
    exit;
}
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../src/user/BuscarUsuario.php";

// Obtiene datos del usuario para el header
$usuario_id = $_SESSION['usuario'];
$stmt = $conn->prepare("SELECT nickname, foto FROM Usuario WHERE id = :id");
$stmt->execute(['id' => $usuario_id]);
$usuarioHeader = $stmt->fetch(PDO::FETCH_ASSOC);

// FUN-048 Controlador principal de busqueda
function mainBusqueda($conn) {
    global $usuarioHeader;
    $buscador = new BuscarUsuario($conn);
    list($q, $type, $page, $items_per_page, $offset) = $buscador->obtenerParametros();

    // Nuevo: obtener filtro de tipo de archivo
    $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';

    // Si no hay texto de busqueda, muestra mensaje y termina
    if (empty($q)) {
        echo "Ingrese un texto valido para buscar.";
        exit;
    }

    // Realiza la busqueda y obtiene resultados y total
    list($results, $totalResults) = $buscador->buscar($q, $type, $items_per_page, $offset);
    $totalPages = ceil($totalResults / $items_per_page);

    // Renderiza la vista de resultados
    renderVistaBusqueda($conn, $q, $type, $page, $results, $totalResults, $totalPages, $items_per_page, $usuarioHeader, $filtro);
}

// FUN-049 Renderizar la vista de busqueda
function renderVistaBusqueda($conn, $q, $type, $page, $results, $totalResults, $totalPages, $items_per_page, $usuarioHeader, $filtro) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Resultados de Búsqueda</title>
        <!-- Estilos principales de la busqueda de usuario -->
        <link rel="stylesheet" href="../css/user/usuario_buscar.css">
        <link rel="stylesheet" href="../css/user/vista_usuario.css">
        <link rel="stylesheet" href="../css/user/busqueda_usuario.css">
        <style>
        /* Estilos para los botones de filtro y paginacion */
        .filter-buttons { margin: 18px 0 18px 0; text-align: center; }
        .filter-buttons a { text-decoration: none; margin: 0 6px; }
        .filter-buttons button { padding: 10px 22px; border-radius: 8px; border: none; font-weight: 500; background: #eaeaea; color: #2c3e50; cursor: pointer; transition: background 0.2s; }
        .filter-buttons .active, .filter-buttons button:hover { background: #3498db; color: #fff; }
        .filter-tipo { margin: 0 0 18px 0; text-align: center; }
        .filter-tipo a { text-decoration: none; margin: 0 6px; }
        .filter-tipo button { padding: 8px 18px; border-radius: 8px; border: none; font-weight: 500; background: #eaeaea; color: #2c3e50; cursor: pointer; transition: background 0.2s; }
        .filter-tipo .active, .filter-tipo button:hover { background: #2980b9; color: #fff; }
        .pagination { margin: 24px 0 0 0; text-align: center; }
        .pagination a { display: inline-block; margin: 0 2px; padding: 8px 16px; border-radius: 6px; background: #eaeaea; color: #2c3e50; text-decoration: none; font-weight: 500; transition: background 0.2s; }
        .pagination a.active, .pagination a:hover { background: #3498db; color: #fff; }
        </style>
    </head>
    <body>
        <!-- Header principal con datos del usuario -->
        <header class="header">
            <span class="logo">VISIO</span>
            <div class="header-right">
                <span class="user-nick"><?php echo htmlspecialchars($usuarioHeader['nickname'] ?? 'Usuario'); ?></span>
                <img src="<?php echo !empty($usuarioHeader['foto']) ? '../uploads/' . htmlspecialchars($usuarioHeader['foto']) : '../assets/placeholder_usuario.jpg'; ?>" alt="Avatar" class="user-avatar">
                <a href="mi_perfil.php" class="header-btn">Mi Perfil</a>
                <a href="logout.php" class="header-btn">Cerrar sesión</a>
                <a href="vista_usuario.php" class="header-btn volver-btn">Volver</a>
            </div>
        </header>
        <!-- Contenedor principal de la busqueda -->
        <div class="main-user-layout">
            <div class="busqueda-container">
                <?php renderizarFormularioYFiltros($q, $type, $filtro); ?>
                <?php if ($type === 'contenido' || $type === 'autor'): ?>
                <!-- Filtros por tipo de archivo -->
                <div class="filter-tipo">
                    <?php
                    // Botones de filtro por tipo de contenido
                    $tipos = [
                        'todos' => 'Todos',
                        'imagen' => 'Imágenes',
                        'video' => 'Videos',
                        'sonido' => 'Sonidos'
                    ];
                    foreach ($tipos as $key => $label) {
                        $active = ($filtro === $key) ? 'active' : '';
                        echo '<a href="usuario_buscar.php?q=' . urlencode($q) . '&type=' . urlencode($type) . '&filtro=' . $key . '"><button class="' . $active . '">' . $label . '</button></a>';
                    }
                    ?>
                </div>
                <?php endif; ?>
                <!-- Titulo de resultados -->
                <h1>Resultados para: "<?php echo htmlspecialchars($q); ?>"</h1>
                <?php if ($totalResults == 0): ?>
                    <!-- Mensaje si no hay resultados -->
                    <p>No se pudo encontrar el elemento deseado, error.</p>
                <?php else: ?>
                    <!-- Cards de resultados -->
                    <div class="cards">
                        <?php renderizarResultados($conn, $results, $type, $filtro); ?>
                    </div>
                    <!-- Paginacion numerica -->
                    <?php renderizarPaginacion($q, $type, $totalPages, $page, $filtro); ?>
                <?php endif; ?>
            </div>
        </div>
    </body>
    </html>
    <?php
}

// FUN-050 Renderizar formulario y filtros de busqueda
function renderizarFormularioYFiltros($q, $type, $filtro) {
    ?>
    <!-- Formulario de busqueda y botones de filtro -->
    <form action="usuario_buscar.php" method="get" style="text-align:center; margin-bottom:10px;">
        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Buscar..." style="padding:8px; border-radius:6px; border:1px solid #ccc; width:220px;">
        <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
        <input type="hidden" name="filtro" value="<?php echo htmlspecialchars($filtro); ?>">
        <button type="submit" class="btn btn-primary">Buscar</button>
    </form>
    <div class="filter-buttons">
        <a href="usuario_buscar.php?q=<?php echo urlencode($q); ?>&type=contenido&filtro=<?php echo urlencode($filtro); ?>">
            <button class="<?php echo $type === 'contenido' ? 'active' : ''; ?>">Contenido</button>
        </a>
        <a href="usuario_buscar.php?q=<?php echo urlencode($q); ?>&type=categoria&filtro=<?php echo urlencode($filtro); ?>">
            <button class="<?php echo $type === 'categoria' ? 'active' : ''; ?>">Categoría</button>
        </a>
        <a href="usuario_buscar.php?q=<?php echo urlencode($q); ?>&type=autor&filtro=<?php echo urlencode($filtro); ?>">
            <button class="<?php echo $type === 'autor' ? 'active' : ''; ?>">Autor</button>
        </a>
    </div>
    <?php
}

// FUN-051 Renderizar resultados de busqueda
function renderizarResultados($conn, $results, $type, $filtro) {
    // Filtrado por tipo de archivo
    $extensiones_imagen = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extensiones_video = ['mp4', 'mov', 'avi', 'mkv', 'webm'];
    $extensiones_audio = ['mp3', 'wav', 'ogg', 'aac', 'flac'];

    if ($type === 'contenido' || $type === 'autor') {
        foreach ($results as $contenido) {
            $archivo = isset($contenido['archivo']) ? $contenido['archivo'] : '';
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

            $tipo = isset($contenido['nombre_del_tipo']) ? strtolower($contenido['nombre_del_tipo']) : '';
            $img = '';
            // Detecta si el archivo es una imagen valida (jpg, jpeg, png, gif, webp)
            $isImage = in_array($extension, $extensiones_imagen);
            if (!empty($archivo) && $isImage && file_exists(__DIR__ . "/../uploads/" . $archivo)) {
                $img = "../uploads/" . htmlspecialchars($archivo);
            } elseif ($filtro === 'sonido' || $tipo === 'sonido') {
                $img = "../assets/Sonido.png";
            } else {
                $img = "../assets/placeholder_contenido.jpg";
            }
            // Card de resultado de contenido
            echo '<div class="card">';
            echo '<a href="contenido_usuario.php?id=' . urlencode($contenido['id']) . '">';
            echo '<img src="' . $img . '" alt="Imagen de ' . htmlspecialchars($contenido['titulo']) . '">';
            echo '<h3>' . htmlspecialchars($contenido['titulo']) . '</h3>';
            echo '</a>';
            echo '<p>Autor: ' . htmlspecialchars($contenido['autor']) . '</p>';
            echo '<p>Precio: $' . number_format($contenido['precio_original'], 2) . '</p>';
            echo '</div>';
        }
    } elseif ($type === 'categoria') {
        foreach ($results as $categoria) {
            // Busca una imagen de contenido para la categoria
            $stmtImg = $conn->prepare("SELECT archivo FROM Contenido WHERE categoria_id = :catid AND estado = 'disponible' AND archivo IS NOT NULL ORDER BY fecha_de_subida DESC LIMIT 1");
            $stmtImg->execute(['catid' => $categoria['id']]);
            $imgRow = $stmtImg->fetch(PDO::FETCH_ASSOC);
            $img = (!empty($imgRow['archivo']) && file_exists(__DIR__ . "/../uploads/" . $imgRow['archivo']))
                ? "../uploads/" . htmlspecialchars($imgRow['archivo'])
                : "../assets/placeholder_categoria.jpg";
            // Card de resultado de categoria
            echo '<div class="card">';
            echo '<a href="categoria_usuario.php?id=' . urlencode($categoria['id']) . '">';
            echo '<img src="' . $img . '" alt="Imagen de ' . htmlspecialchars($categoria['nombre']) . '">';
            echo '<h3>' . htmlspecialchars($categoria['nombre']) . '</h3>';
            echo '</a>';
            echo '<p>' . htmlspecialchars($categoria['descripcion']) . '</p>';
            echo '</div>';
        }
    }
}

// FUN-052 Renderizar paginacion
function renderizarPaginacion($q, $type, $totalPages, $page, $filtro) {
    if ($totalPages > 1) {
        // Contenedor de paginacion numerica
        echo '<div class="pagination">';
        for ($p = 1; $p <= $totalPages; $p++) {
            $active = $p == $page ? 'active' : '';
            echo '<a href="usuario_buscar.php?q=' . urlencode($q) . '&type=' . urlencode($type) . '&page=' . $p . '&filtro=' . urlencode($filtro) . '" class="' . $active . '">' . $p . '</a>';
        }
        echo '</div>';
    }
}

// Ejecuta el controlador principal
mainBusqueda($conn);