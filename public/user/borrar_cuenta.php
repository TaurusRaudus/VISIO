<?php

session_start();
// Verifica que el usuario este autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../sesion/login.php");
    exit;
}
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../src/user/BorrarCuenta.php";

// FUN-043 Controlador para borrar la cuenta y limpiar sesión
function procesarBorradoCuenta($conn) {
    $usuario_id = $_SESSION['usuario'];
    $borrador = new BorrarCuenta($conn, $usuario_id);
    $resultado = $borrador->borrar();

    if ($resultado) {
        // Limpia la sesion y destruye la cookie de sesion
        session_unset();
        session_destroy();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                      $params["path"], $params["domain"],
                      $params["secure"], $params["httponly"]
            );
        }
        // Redirige al login con mensaje de exito
        header("Location: /../sesion/login.php?msg=" . urlencode($borrador->getMensaje()));
        exit;
    } else {
        // Mostrar mensaje en ventana emergente y redirigir a mi_perfil.php
        echo "<script>alert('" . addslashes($borrador->getMensaje()) . "'); window.location.href='mi_perfil.php';</script>";
        exit;
    }
}

// Ejecuta el procesamiento de borrado de cuenta
procesarBorradoCuenta($conn);