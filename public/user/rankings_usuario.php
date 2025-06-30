<?php
// Inicia la sesion y verifica autenticacion del usuario
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../sesion/login.php");
    exit;
}
// Incluye la conexion a la bd
require_once __DIR__ . "/../../config/db.php";
// Obtiene datos del usuario para el header
$usuario_id = $_SESSION['usuario'];
$stmt = $conn->prepare("SELECT nickname, foto FROM Usuario WHERE id = :id");
$stmt->execute(['id' => $usuario_id]);
$usuarioHeader = $stmt->fetch(PDO::FETCH_ASSOC);
// Incluye la clase para gestionar ranking
require_once __DIR__ . "/../../src/user/GestionarRanking.php";

use User\GestionarRanking;

/**
 * FUN-142 Obtener el tipo de ranking desde GET.
 * Devuelve 'valorados' o 'descargados' (por defecto 'descargados').
 */
function obtenerTipoRanking()
{
    // Retorna el tipo de ranking segun el parametro GET
    return (isset($_GET['tipo']) && $_GET['tipo'] === 'valorados') ? 'valorados' : 'descargados';
}

/**
 * FUN-143 Obtener el ranking de contenidos segun el tipo.
 * Devuelve un array de resultados.
 */
function obtenerRankingContenidos($ranking, $tipo)
{
    // Llama al metodo correspondiente segun el tipo
    if ($tipo === 'descargados') {
        return $ranking->obtenerRankingDescargados();
    } else {
        return $ranking->obtenerRankingValorados();
    }
}

// Obtiene el filtro de tipo de archivo desde GET
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';

// --- Controlador principal ---

$tipo = obtenerTipoRanking();
$ranking = new GestionarRanking($conn);
$resultados = obtenerRankingContenidos($ranking, $tipo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ranking de Contenidos</title>
    <!-- Carga los estilos principales para rankings -->
    <link rel="stylesheet" href="../css/user/rankings_usuario.css">
    <link rel="stylesheet" href="../css/user/busqueda_usuario.css">
    <link rel="stylesheet" href="../css/user/vista_usuario.css">
    <style>
    /* Estilos para los botones de ranking */
    .ranking-buttons { margin-bottom: 28px; }
    .ranking-buttons .btn { padding: 10px 28px; border-radius: 8px; border: none; font-weight: 600; background: #eaeaea; color: #2c3e50; cursor: pointer; margin: 0 10px; font-size: 1.1em; transition: background 0.2s; text-decoration: none; display: inline-block;}
    .ranking-buttons .active, .ranking-buttons .btn:hover { background: #3498db; color: #fff; }
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
            <a href="../sesion/logout.php" class="header-btn">Cerrar sesión</a>
            <a href="vista_usuario.php" class="header-btn volver-btn">Volver</a>
        </div>
    </header>
    <!-- Layout principal de rankings -->
    <div class="rankings-layout">
        <div class="rankings-spacer"></div>
        <div class="rankings-container">
            <h1>Ranking de Contenidos</h1>
            <!-- Botones para cambiar tipo de ranking -->
            <div class="ranking-buttons">
                <a href="?tipo=descargados<?php echo $filtro !== 'todos' ? '&filtro=' . urlencode($filtro) : ''; ?>" class="btn<?php echo $tipo === 'descargados' ? ' active' : ''; ?>">Más descargados</a>
                <a href="?tipo=valorados<?php echo $filtro !== 'todos' ? '&filtro=' . urlencode($filtro) : ''; ?>" class="btn<?php echo $tipo === 'valorados' ? ' active' : ''; ?>">Mejor valorados</a>
            </div>
            <!-- Galeria de resultados de ranking -->
            <div class="galeria">
                <?php
                // Filtrado por tipo de archivo
                $extensiones_imagen = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $extensiones_video = ['mp4', 'mov', 'avi', 'mkv', 'webm'];
                $extensiones_audio = ['mp3', 'wav', 'ogg', 'aac', 'flac'];
                $hayContenido = false;
                if (empty($resultados)): ?>
                    <!-- Mensaje si no hay resultados -->
                    <div class="galeria-vacio sin-contenido">No hay resultados para mostrar.</div>
                <?php else:
                    foreach ($resultados as $i => $row):
                        $archivo = $row['archivo'] ?? '';
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
                ?>
                    <!-- Card de resultado de ranking -->
                    <div class="galeria-item">
                        <div class="puesto"><?php echo $i + 1; ?></div>
                        <?php
                        $tipoArchivo = strtolower($row['nombre_del_tipo'] ?? '');
                        $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $archivo);
                        if (!empty($archivo) && $isImage && file_exists(__DIR__ . "/../uploads/" . $archivo)) {
                            $img = "../uploads/" . htmlspecialchars($archivo);
                        } elseif ($tipoArchivo === 'sonido' || ($filtro === 'sonido' && in_array($extension, $extensiones_audio))) {
                            $img = "../assets/Sonido.png";
                        } elseif ($filtro === 'video' && in_array($extension, $extensiones_video)) {
                            $img = "../assets/Video.png";
                        } else {
                            $img = "../assets/placeholder_contenido.jpg";
                        }
                        ?>
                        <img src="<?php echo $img; ?>"
                             alt="<?php echo htmlspecialchars($row['titulo']); ?>" class="galeria-thumb">
                        <h3><?php echo htmlspecialchars($row['titulo']); ?></h3>
                        <div class="dato-ranking">
                            <?php if ($tipo === 'descargados'): ?>
                                <?php echo $row['total_descargas']; ?> descargas
                            <?php else: ?>
                                <?php echo number_format($row['promedio_calificacion'], 2); ?>/10 (<?php echo $row['total_calificaciones']; ?> calificaciones)
                            <?php endif; ?>
                        </div>
                        <a href="contenido_usuario.php?id=<?php echo urlencode($row['id']); ?>" class="btn">Ver</a>
                    </div>
                <?php endforeach;
                endif;
                // Muestra mensaje si no hay resultados para el filtro seleccionado
                if (!$hayContenido && !empty($resultados)) {
                    echo '<div class="galeria-vacio sin-contenido">No hay resultados para este filtro.</div>';
                }
                ?>
            </div>
        </div>
        <!-- Sidebar de filtros por tipo de archivo -->
        <aside class="filtros-ranking">
            <h3>Filtrar por tipo</h3>
            <form class="filtros-tipo-radio" method="get" action="rankings_usuario.php">
                <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($tipo); ?>">
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
        </aside>
    </div>
</body>
</html>