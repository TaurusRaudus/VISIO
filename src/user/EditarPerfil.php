<?php
// CU-005 Editar Perfil de usuario
// CLS-004 Clase para editar el perfil de usuario
// TAB-001 Usuario

class EditarPerfil
{
    private $conn;
    private $usuario_id;
    private $errores = [];
    private $mensaje = '';
    private $cambioRealizado = false;

    // FUN-032 Constructor
    public function __construct($conn, $usuario_id)
    {
        // Guardamos la conexion y el id del usuario
        $this->conn = $conn;
        $this->usuario_id = $usuario_id;
    }

    // FUN-033 Procesar la foto de perfil
    public function procesarFoto($foto)
    {
        // Procesamos la foto de perfil subida por el usuario
        $ext = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg', 'jpeg', 'png'];
        if (!in_array($ext, $permitidos) || $foto['size'] > 8*1024*1024) {
            $this->errores[] = "formato no válido";
            return false;
        }
        $nombreFoto = uniqid("foto_") . "." . $ext;
        // Movemos la foto al directorio de uploads
        if (!move_uploaded_file($foto['tmp_name'], __DIR__ . "/../../public/uploads/" . $nombreFoto)) {
            $this->errores[] = "Error al guardar la foto";
            return false;
        }
        // Actualizamos la foto en la base de datos
        $stmt = $this->conn->prepare("UPDATE Usuario SET foto = :foto WHERE id = :id");
        $stmt->execute(['foto' => $nombreFoto, 'id' => $this->usuario_id]);
        $this->mensaje = "Foto guardada";
        $this->cambioRealizado = true;
        return true;
    }

    // FUN-034 Procesar el cambio de nickname
    public function procesarNickname($nickname)
    {
        // Procesamos el cambio de nickname del usuario
        if (strlen($nickname) < 5 || strlen($nickname) > 60 || !preg_match('/^[a-zA-Z0-9áéíóúñÑ\s]+$/', $nickname)) {
            $this->errores[] = "Formato no válido.";
            return false;
        }
        // Verificar duplicidad
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM Usuario WHERE nickname = :nickname AND id != :id");
        $stmt->execute(['nickname' => $nickname, 'id' => $this->usuario_id]);
        if ($stmt->fetchColumn() > 0) {
            $this->errores[] = "Ese nombre ya está siendo utilizado";
            return false;
        }
        // Actualizamos el nickname en la base de datos
        $stmt = $this->conn->prepare("UPDATE Usuario SET nickname = :nickname WHERE id = :id");
        $stmt->execute(['nickname' => $nickname, 'id' => $this->usuario_id]);
        $this->mensaje = "Nombre guardado";
        $this->cambioRealizado = true;
        return true;
    }

    // FUN-035 Procesar la edición del perfil (foto y nickname)
    public function procesarEdicion($foto, $nickname)
    {
        // Procesamos la edicion del perfil, tanto foto como nickname
        if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
            $this->procesarFoto($foto);
        }
        if ($nickname !== null && $nickname !== '') {
            $this->procesarNickname($nickname);
        }
        // Si no se realizaron cambios y no hay errores, informamos al usuario
        if (!$this->cambioRealizado && empty($this->errores)) {
            $this->mensaje = "No se realizaron cambios.";
        }
        return $this->cambioRealizado;
    }

    // FUN-036 Obtener mensaje de resultado
    public function getMensaje()
    {
        // Devolvemos el mensaje de exito o el primer error
        return $this->mensaje ?: (isset($this->errores[0]) ? $this->errores[0] : '');
    }

    // FUN-037 Obtener errores
    public function getErrores()
    {
        // Devolvemos el arreglo de errores encontrados
        return $this->errores;
    }
}
?>