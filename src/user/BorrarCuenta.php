<?php
// CU-006 Borrar cuenta de usuario
// CLS-005 Clase para borrar la cuenta de usuario
// TAB-001 Usuario, TAB-015 VistaSaldo

class BorrarCuenta
{
    private $conn;
    private $usuario_id;
    private $errores = [];
    private $mensaje = '';

    // FUN-039 Constructor
    public function __construct($conn, $usuario_id)
    {
        // Guardamos la conexion y el id del usuario
        $this->conn = $conn;
        $this->usuario_id = $usuario_id;
    }

    // FUN-040 Ejecutar el borrado de la cuenta (solo si saldo = 0)
    public function borrar()
    {
        // Verificamos el saldo antes de borrar la cuenta
        $stmtSaldo = $this->conn->prepare("SELECT saldo FROM VistaSaldo WHERE usuario_id = :usuario_id");
        $stmtSaldo->execute(['usuario_id' => $this->usuario_id]);
        $saldo = $stmtSaldo->fetchColumn();

        if ($saldo === false || floatval($saldo) != 0.0) {
            $this->mensaje = "No puedes eliminar tu cuenta hasta que tu saldo sea $0.00.";
            return false;
        }

        try {
            // Obtenemos la foto y correo del usuario antes de borrar
            $stmtFoto = $this->conn->prepare("SELECT foto, correo_electronico FROM Usuario WHERE id = :usuario_id");
            $stmtFoto->execute(['usuario_id' => $this->usuario_id]);
            $resultadoFoto = $stmtFoto->fetch(\PDO::FETCH_ASSOC);

            // Iniciamos la transaccion para el borrado
            $this->conn->beginTransaction();

            // Generamos un nuevo correo anonimo para el usuario borrado
            $nuevoCorreo = 'deleted_' . time() . '_' . $resultadoFoto['correo_electronico'];

            // Actualizamos los datos del usuario para anonimizarlo y desactivar acceso
            $stmt = $this->conn->prepare("UPDATE Usuario 
                SET nickname = CONCAT('Ex-Cliente_', to_char(CURRENT_TIMESTAMP, 'YYYYMMDDHH24MISS')), 
                    correo_electronico = :nuevoCorreo,
                    contraseña = md5(random()::text)
                WHERE id = :usuario_id");
            $stmt->execute([
                'nuevoCorreo' => $nuevoCorreo,
                'usuario_id' => $this->usuario_id
            ]);
            $this->conn->commit();

            // FUN-041 Eliminar foto de perfil si existe
            // Si el usuario tenia foto de perfil, la eliminamos del sistema de archivos
            if ($resultadoFoto && !empty($resultadoFoto['foto'])) {
                $rutaFoto = __DIR__ . "/../../public/uploads/" . $resultadoFoto['foto'];
                if (file_exists($rutaFoto)) {
                    unlink($rutaFoto);
                }
            }
            // Guardamos el mensaje de exito
            $this->mensaje = "Cuenta borrada con éxito";
            return true;
        } catch (\Exception $e) {
            // Si ocurre un error, revertimos la transaccion y guardamos el error
            $this->conn->rollBack();
            $this->errores[] = "Error al borrar la cuenta: " . $e->getMessage();
            return false;
        }
    }

    // FUN-042 Obtener mensaje de resultado
    public function getMensaje()
    {
        // Devolvemos el mensaje de exito o el primer error
        return $this->mensaje ?: (isset($this->errores[0]) ? $this->errores[0] : '');
    }
}
?>