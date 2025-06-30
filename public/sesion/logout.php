<?php
// CU-003 Cerrar sesion

// FUN-021 Iniciar o continuar sesión
function iniciarSesionLogout() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// FUN-022 Destruir variables de sesión
function destruirVariablesSesion() {
    session_unset(); // Limpia las variables de sesión
}

// FUN-023 Destruir la sesión completamente
function destruirSesion() {
    session_destroy();
}

// FUN-024 Redirigir al inicio
function redirigirAlInicio() {
    header("Location: ../index.html");
    exit;
}

// FUN-025 Proceso principal de logout
function mainLogout() {
    iniciarSesionLogout();
    destruirVariablesSesion();
    destruirSesion();
    redirigirAlInicio();
}

mainLogout();
?>