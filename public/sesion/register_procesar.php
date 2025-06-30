<?php
// En este archivo llamamos al register en src
// Basicamente vamos con esto a evitar que
// el code principal caiga en manos maliciosas
// Por que no es bueno que el como manejamos el backend
// sea publico ais que esto es un "wrapper"
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../src/sesion/register.php';

// Verificamos que la solicitud se haya hecho con POST, esto mas por seguridad o eso entiendo
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acceso denegado.");
}

// Instanciamos la clase de registro
$register = new Registro($conn, $_POST);
    
if (!$register->processRegistration()) {
    // Bugs?
    $_SESSION['errors'] = $register->getErrors();
    $_SESSION['old']    = $_POST;
    header("Location: register.php");
    exit();

}

// De lo demas se encarga la clase
?>