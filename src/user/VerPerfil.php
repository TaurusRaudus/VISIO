<?php
// CU-004 Ver perfil de usuario
// CLS-003 Clase para ver el perfil de usuario
// TAB-001 Usuario, TAB-007 VistaSaldo

class VerPerfil
{
    private $conn;
    private $usuario_id;
    private $usuario = [];
    private $saldo = 0;

    // FUN-026 Constructor
    public function __construct($conn, $usuario_id)
    {
        // Guardamos la conexion y el id del usuario
        $this->conn = $conn;
        $this->usuario_id = $usuario_id;
    }

    // FUN-027 Obtener datos del usuario
    public function obtenerDatosUsuario()
    {
        // Obtenemos los datos principales del usuario
        $stmt = $this->conn->prepare("SELECT id, nickname, correo_electronico, foto FROM Usuario WHERE id = :id");
        $stmt->execute(['id' => $this->usuario_id]);
        $this->usuario = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $this->usuario;
    }

    // FUN-028 Obtener saldo del usuario
    public function obtenerSaldo()
    {
        // Obtenemos el saldo actual del usuario desde la vista
        $stmtSaldo = $this->conn->prepare("SELECT saldo FROM VistaSaldo WHERE usuario_id = :id");
        $stmtSaldo->execute(['id' => $this->usuario_id]);
        $this->saldo = $stmtSaldo->fetchColumn();
        return $this->saldo;
    }

    // FUN-029 Obtener todo el perfil (usuario + saldo)
    public function obtenerPerfilCompleto()
    {
        // Obtenemos todos los datos del perfil del usuario (datos + saldo)
        return [
            'usuario' => $this->obtenerDatosUsuario(),
            'saldo'   => $this->obtenerSaldo()
        ];
    }
}
?>