<?php

// Aqui vamos a ver la generacion de los usuarios

// CU - 001 Registrarse
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/db.php'; // Este archivo debe proveer la conexión en $conn

// Funcion para generar un UUID de manera aleatoria

// FUN-001 GenerarUUID
// Esta funcion genera un UUID con mt_rand
function GenerarUUID() {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// Aqui vamos a gestionar el registro
// CLS - 001 Registro
class Registro {

    private $conn;          // Conexion a la base de datos
    private $data;          // Datos del formulario
    private $errors = [];   // Array de errores de validacion
    
    // FUN - 002 Constructor
    public function __construct($conn, $postData) {
        $this->conn = $conn;
        $this->data = $postData;
    }
    
    // Validacion de los datos ingresados ///////////////////////////////////////////////////////////////////////////////////////
    // FUN - 003 VALIDAR
    // En esta funcion verificamos que los datos ingresados sean validos
    public function VALIDAR() {



        // VALIDAR NICKNAME
        $nombre = trim($this->data['username'] ?? '');
        if (strlen($nombre) < 5) {
            $this->errors['username'] = "El nickname debe tener al menos 5 caracteres.";
        }
        if (strlen($nombre) > 60) {
            $this->errors['username'] = "El nickname no puede tener más de 60 caracteres.";
        }


        
        // VALIDAR CORREO
        $correo = trim($this->data['email'] ?? '');
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = "Correo electronico no valido.";
        }


        
        // VALIDAR CONTRASEÑA
        $contraseña = $this->data['password'] ?? '';
        if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[!"#$%&\'()*+,\-.\/:;<=>?@\[\]^_`{|}~]).{5,}$/', $contraseña)) {
            $this->errors['password'] = "La contraseña debe tener al menos 5 caracteres, una letra mayúscula, un número y un caracter especial.";
        }
        if (strlen($contraseña) > 60) {
            $this->errors['password'] = "La contraseña no puede tener más de 60 caracteres.";
        }


        
        // VALIDAR CONFIRMACION DE LA CONTRASEÑA
        $confirm_password = $this->data['confirm_password'] ?? '';
        if ($contraseña !== $confirm_password) {
            $this->errors['confirm_password'] = "Las contraseñas no coinciden.";
        }



        
        // VALIDAR TERMINOS Y CONDICIONES
        if (!isset($this->data['terms'])) {
            $this->errors['terms'] = "Se deben aceptar los términos y condiciones.";
        }
        
        return empty($this->errors);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    




    // Bugs? //////////////////////////////////
    // FUN - 004 getErrors
    // Esta funcion se encarga de guardar los errores que haya, para asi mostrarlos in situ en la pagina en si
    public function getErrors() {
        return $this->errors;
    }
    ///////////////////////////////////////////
    



    // VALIDAR CORREO NUEVO /////////////////////////////////////////////////////////////////////////////////
    // FUN - 005 ExisteCorreo()
    // En esta funcion nos encargamos de verificar que el correo sea nuevo
    private function ExisteCorreo($correo) {
        $stmt = $this->conn->prepare("SELECT * FROM Usuario WHERE correo_electronico = :correo");
        $stmt->execute([ 'correo' => $correo ]);
        return ($stmt->rowCount() > 0);
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    


    // VALIDAR NICKNAME /////////////////////////////////////////////////////////////////////////////////////
    // FUN - 006 ExisteUsername($nombre)
    // En esta funcion nos encargamos de verificar que el nickname sea nuevo
    private function ExisteUsername($nombre) {
        $stmt = $this->conn->prepare("SELECT * FROM Usuario WHERE nickname = :nickname");
        $stmt->execute([ 'nickname' => $nombre ]);
        return ($stmt->rowCount() > 0);
    }
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    



    // REGISTRAMOS AL USUARIO ///////////////////////////////////////////////////////////////////////////////
    // FUN - 007 Registrar
    // En esta funcion registramos al usuario
    public function Registrar() {
        $nombre = trim($this->data['username']);
        $correo = trim($this->data['email']);
        $contraseña = $this->data['password'];
        
        // Validaciones:
        // Llamamos a validar al usuario
        if ($this->ExisteUsername($nombre)) {
            $this->errors['username'] = "Nickname no disponible";
            return false;
        }
        if ($this->ExisteCorreo($correo)) {
            $this->errors['email'] = "Correo electrónico ya en uso.";
            return false;
        }
        
        // Hashear la contraseña
        
        $hashed_password = password_hash($contraseña, PASSWORD_BCRYPT);
        
        // Generar un UUID para el nuevo usuario
        // FUN - 001
        $uuid = GenerarUUID();
        
        // Insertamos el Usuario
        $stmt = $this->conn->prepare("
            INSERT INTO Usuario (id, nickname, correo_electronico, contraseña, fecha_de_registro)
            VALUES (:id, :nickname, :correo, :pass, CURRENT_TIMESTAMP)
        ");
        
        return $stmt->execute([
            'id'       => $uuid,
            'nickname' => $nombre,
            'correo'   => $correo,
            'pass'     => $hashed_password
        ]);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////

    // FUN - 009 LoguearUsuario
    // En esta funcion se autentica automaticamente al usuario despues de registrarse
    private function LoguearUsuario($correo) {
        $stmt = $this->conn->prepare("SELECT id, nickname, correo_electronico, fecha_de_registro FROM Usuario WHERE correo_electronico = :correo LIMIT 1");
        $stmt->execute(['correo' => $correo]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['errors'] = ['register' => 'No se pudo obtener la informacion del usuario.'];
            header("Location: ../public/sesion/register.php");
            exit();
        }

        // Se almacena el ID y el nickname en la sesion
        $_SESSION['usuario'] = $user['id'];
        $_SESSION['user'] = $user['nickname'];
    }

    
    // Procesa el registro: valida, inserta y redirige
    // FUN - 008 procesarRegistro
    // En esta funcion procesamos el registro
    public function processRegistration() {
        // Si no se valida
        if (!$this->VALIDAR()) {
            $_SESSION['errors'] = $this->getErrors();
            $_SESSION['old'] = $this->data;
            header("Location: ../sesion/register.php");
            exit();
        }

        // Si no se puede registrar
        if (!$this->Registrar()) {
            $_SESSION['errors'] = $this->getErrors();
            $_SESSION['old'] = $this->data;
            header("Location: ../sesion/register.php");
            exit();
        }

        // LOGEO AUTOMATICO

        $this->LoguearUsuario($this->data['email']);
        header("Location: ../user/vista_usuario.php");
        exit();
    }
}
?>
