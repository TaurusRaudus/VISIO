<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../sesion/login.php");
    exit;
}

// Se espera recibir desde la interfaz de contenido el id del contenido a promocionar.
$contenidoId = $_GET['id'] ?? null;
if (!$contenidoId) {
    $_SESSION['mensaje'] = "Contenido no especificado para agregar promoción.";
    header("Location: admin.php");
    exit;
}

// Agregamos validacion de fechas antes de mostrar el formulario o procesar el POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
    $fecha_fin = trim($_POST['fecha_fin'] ?? '');
    // Forzamos la zona horaria a la del servidor para evitar desfases
    date_default_timezone_set('America/Mexico_City'); // Cambia a tu zona si es necesario
    $hoy = date('Y-m-d');

    // Convertimos a objetos DateTime para comparar correctamente y limpiamente
    $dt_inicio = DateTime::createFromFormat('Y-m-d', $fecha_inicio);
    $dt_fin = DateTime::createFromFormat('Y-m-d', $fecha_fin);
    $dt_hoy = DateTime::createFromFormat('Y-m-d', $hoy);

    // Validamos que las fechas sean válidas
    if (!$dt_inicio || !$dt_fin) {
        $_SESSION['mensaje_promocion'] = "Formato de fecha inválido.";
        header("Location: agregar_promocion.php?id=" . urlencode($contenidoId));
        exit;
    }

    // Comparamos usando timestamps para evitar cualquier problema de formato/hora
    if ($dt_inicio->setTime(0,0,0)->getTimestamp() < $dt_hoy->setTime(0,0,0)->getTimestamp()) {
        $_SESSION['mensaje_promocion'] = "La fecha de inicio no puede ser anterior al dia de hoy.";
        header("Location: agregar_promocion.php?id=" . urlencode($contenidoId));
        exit;
    }
    if ($dt_fin->setTime(0,0,0)->getTimestamp() < $dt_hoy->setTime(0,0,0)->getTimestamp()) {
        $_SESSION['mensaje_promocion'] = "La fecha de fin no puede ser anterior al dia de hoy.";
        header("Location: agregar_promocion.php?id=" . urlencode($contenidoId));
        exit;
    }
    if ($dt_fin->getTimestamp() < $dt_inicio->getTimestamp()) {
        $_SESSION['mensaje_promocion'] = "La fecha de fin no puede ser anterior a la fecha de inicio.";
        header("Location: agregar_promocion.php?id=" . urlencode($contenidoId));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Promoción</title>
    <link rel="stylesheet" href="../css/admin/agregar_promocion.css">
</head>
<body>
    <h1>Agregar Promoción</h1>
    
    <form action="agregar_promocion_wrapper.php" method="post">
        <!-- Enviamos el id del contenido como campo oculto -->
        <input type="hidden" name="contenidoId" value="<?php echo htmlspecialchars($contenidoId); ?>">
        
        <label for="fecha_inicio">Fecha de Inicio:</label>
        <input type="date" name="fecha_inicio" id="fecha_inicio" required>
        <br>
        
        <label for="fecha_fin">Fecha de Fin:</label>
        <input type="date" name="fecha_fin" id="fecha_fin" required>
        <br>
        
        <label for="descuento">Porcentaje de Descuento (10-90%):</label>
        <input type="number" name="descuento" id="descuento" min="10" max="90" required>
        <br>
        
        <label for="razon">Razón (opcional):</label>
        <textarea name="razon" id="razon" rows="3" cols="50" placeholder="Ingrese la razón si lo desea"></textarea>
        <br>
        
        <div class="form-actions">
            <button type="submit">Agregar Promoción</button>
            <a href="contenido_admin.php?id=<?php echo urlencode($contenidoId); ?>" class="btn-volver">Volver al Contenido</a>
        </div>
    </form>

    <?php
    // Mostramos el mensaje de validacion si existe
    if (isset($_SESSION['mensaje_promocion'])) {
        echo '<div class="mensaje">' . htmlspecialchars($_SESSION['mensaje_promocion']) . '</div>';
        unset($_SESSION['mensaje_promocion']);
    }
    ?>
</body>
</html>