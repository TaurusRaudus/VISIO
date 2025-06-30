<?php
// UI - 03 Inicio de Sesion
// CU-002 Iniciar sesion
// 1. El usuario debe poder llenar los campos correo electronico y contraseña
// 2. Si los datos son correctos, se buscara en la base de datos el registro
// 3. Si el registro existe, se redirige a la vista principal, ya sea de usuario (UI-15) o de administrador (UI-05)
// 4. En caso de error se muestran mensajes y se regresa al formulario

require_once __DIR__ . '/../../config/db.php';

// FUN-010 Iniciar o continuar sesión
function iniciarSesion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// CLS-002 Clase para iniciar Sesion
class IniciarSesion {
    private $conn;          // Conexion a la base de datos
    private $data;          // Datos del formulario
    private $errors = [];   // Array de errores

    // FUN-011 Constructor
    public function __construct($conn, $postData) {
        $this->conn = $conn;
        $this->data = $postData;
    }

    // FUN-012 Validacion de los datos ingresados
    // TAB-001 Usuario
    public function comprobarDatos() {
        $email = trim($this->data['email'] ?? '');
        $password = trim($this->data['password'] ?? '');

        if (empty($email)) {
            $this->errors['email'] = "El correo electronico es requerido.";
        }
        if (empty($password)) {
            $this->errors['password'] = "La contrasena es requerida.";
        }
        return empty($this->errors);
    }
    
    // FUN-013 Autenticacion del usuario o administrador
    // TAB-001 Usuario, TAB-002 Administrador
    public function authenticate() {
        if (!$this->comprobarDatos()) {
            return false;
        }
        
        $email = strtolower(trim($this->data['email']));
        $password = $this->data['password'];
        
        // Caso Administrador
        $stmt = $this->conn->prepare("SELECT * FROM Administrador WHERE LOWER(correo_electronico) = :correo");
        $stmt->execute(['correo' => $email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            if (password_verify($password, $admin['contraseña'])) {
                // FUN-014 Setea variables de sesion para el administrador
                $_SESSION['admin'] = $admin['correo_electronico'];
                $_SESSION['admin_id'] = $admin['id'];
                header("Location: ../admin/admin.php");
                exit();
            } else {
                $this->errors['password'] = "La contrasena es incorrecta";
                return false;
            }
        }
        
        // Caso Usuario
        $stmt = $this->conn->prepare("SELECT * FROM Usuario WHERE LOWER(correo_electronico) = :correo");
        $stmt->execute(['correo' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['contraseña'])) {
                // FUN-015 Setea variables de sesion para el usuario
                $_SESSION['usuario'] = $user['id'];
                $_SESSION['user'] = $user['nickname'];
                header("Location: ../user/vista_usuario.php");
                exit();
            } else {
                $this->errors['password'] = "La contrasena es incorrecta.";
                return false;
            }
        } else {
            $this->errors['email'] = "El correo electronico es incorrecto.";
            return false;
        }
        
        // Si llega aca, se define un error general
        $this->errors['general'] = "Correo o contrasena incorrectos.";
        return false;
    }
    
    // FUN-016 Retorna los errores encontrados
    public function getErrors() {
        return $this->errors;
    }
    
    // FUN-017 Procesa el login
    public function processLogin() {
        return $this->authenticate();
    }
}

// FUN-018 Proceso principal del login
function procesarLogin($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $login = new IniciarSesion($conn, $_POST);
        
        if (!$login->processLogin()) {
            $_SESSION['errors'] = $login->getErrors();
            $_SESSION['old'] = $_POST;
            header("Location: login.php");
            exit();
        }
    }
}

// FUN-019 Carga valores antiguos y errores para el formulario
function cargarErroresYValores() {
    $errors = $_SESSION['errors'] ?? [];
    $old = $_SESSION['old'] ?? [];
    session_unset(); // Limpiar para no repetir errores en siguientes visitas
    return [$errors, $old];
}

// FUN-020 Punto de entrada del script
function mainLogin($conn) {
    iniciarSesion();
    procesarLogin($conn);
    list($errors, $old) = cargarErroresYValores();
    // Aquí podrías incluir el HTML del formulario o requerir otro archivo
    // require 'login_form.php';
    // Por ahora, solo se cargan los errores y valores antiguos en variables
}

mainLogin($conn);
?>
