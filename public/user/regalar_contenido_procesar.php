<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: ../sesion/login.php"); exit; }
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../src/user/RegalarContenido.php";

// FUN-067 Controlador para procesar el regalo de contenido
function procesarRegaloContenido($conn) {
    $remitente_id = $_SESSION['usuario'];
    $contenido_id = $_POST['contenido_id'] ?? '';
    $destinatario_nick = trim($_POST['destinatario'] ?? '');

    $regalo = new RegalarContenido($conn, $remitente_id);

    // Validar existencia de contenido y obtener precio
    $precio = $regalo->obtenerPrecioContenido($contenido_id);
    if ($precio === false) {
        $_SESSION['mensaje'] = $regalo->getMensaje();
        $_SESSION['tipo_mensaje'] = $regalo->getTipoMensaje();
        header("Location: regalar_contenido.php?id=" . urlencode($contenido_id));
        exit;
    }

    // Validar destinatario
    $destinatario_id = $regalo->obtenerDestinatarioId($destinatario_nick);
    if ($destinatario_id === false || $destinatario_id == $remitente_id) {
        $_SESSION['mensaje'] = $destinatario_id === false ? $regalo->getMensaje() : "No puedes regalarte contenido a ti mismo.";
        $_SESSION['tipo_mensaje'] = $regalo->getTipoMensaje();
        header("Location: regalar_contenido.php?id=" . urlencode($contenido_id));
        exit;
    }

    // Verificar límite de regalos diarios
    if (!$regalo->verificarLimiteRegalos()) {
        $_SESSION['mensaje'] = $regalo->getMensaje();
        $_SESSION['tipo_mensaje'] = $regalo->getTipoMensaje();
        header("Location: regalar_contenido.php?id=" . urlencode($contenido_id));
        exit;
    }

    // Verificar saldo suficiente
    if (!$regalo->verificarSaldo($precio)) {
        $_SESSION['mensaje'] = $regalo->getMensaje();
        $_SESSION['tipo_mensaje'] = $regalo->getTipoMensaje();
        header("Location: regalar_contenido.php?id=" . urlencode($contenido_id));
        exit;
    }

    // Procesar el regalo
    if ($regalo->procesarRegalo($contenido_id, $destinatario_id, $precio)) {
        $_SESSION['mensaje'] = $regalo->getMensaje();
        $_SESSION['tipo_mensaje'] = $regalo->getTipoMensaje();
        header("Location: regalar_contenido.php?id=" . urlencode($contenido_id));
        exit;
    } else {
        $_SESSION['mensaje'] = $regalo->getMensaje();
        $_SESSION['tipo_mensaje'] = $regalo->getTipoMensaje();
        header("Location: regalar_contenido.php?id=" . urlencode($contenido_id));
        exit;
    }
}

procesarRegaloContenido($conn);