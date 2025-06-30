<?php
// CU-010 Recargar saldo (Usuario)
// CLS-009 Clase para recargar saldo de usuario
// TAB-011 Recarga, TAB-015 VistaSaldo

class RecargarSaldo
{
    private $conn;
    private $usuario_id;
    private $errores = [];
    private $mensaje = '';

    // FUN-068 Constructor
    public function __construct($conn, $usuario_id)
    {
        // Guardamos la conexion y el id del usuario
        $this->conn = $conn;
        $this->usuario_id = $usuario_id;
    }

    // FUN-069 Validar datos de recarga
    public function validarDatos($nombre, $apellidos, $tarjeta, $cvv, $monto)
    {
        // Validamos los datos ingresados antes de procesar la recarga
        if (empty($nombre) || empty($apellidos) || empty($tarjeta) || empty($cvv)) {
            $this->errores[] = "Todos los campos son obligatorios.";
            return false;
        }
        if (!preg_match('/^\d{16}$/', $tarjeta)) {
            $this->errores[] = "Número de tarjeta inválido.";
            return false;
        }
        if (!preg_match('/^\d{3,4}$/', $cvv)) {
            $this->errores[] = "CVV inválido.";
            return false;
        }
        if ($monto < 5 || $monto > 500) {
            $this->errores[] = "El monto debe estar entre \$5 y \$500.";
            return false;
        }
        return true;
    }

    // FUN-070 Procesar recarga de saldo
    public function procesarRecarga($nombre, $apellidos, $tarjeta, $cvv, $monto)
    {
        // Procesamos la recarga de saldo si los datos son validos
        if (!$this->validarDatos($nombre, $apellidos, $tarjeta, $cvv, $monto)) {
            $this->mensaje = $this->errores[0];
            return false;
        }

        // Simulamos la recarga
        try {
            $stmt = $this->conn->prepare("INSERT INTO Recarga (monto, fecha_de_recarga, usuario_id) VALUES (:monto, NOW(), :usuario_id)");
            $stmt->execute([
                'monto' => $monto,
                'usuario_id' => $this->usuario_id
            ]);
            $this->mensaje = "Recarga exitosa. Se han añadido \${$monto} a tu saldo.";
            return true;
        } catch (\Exception $e) {
            // Si ocurre un error, guardamos el mensaje de error
            $this->mensaje = "Error al procesar la recarga.";
            return false;
        }
    }

    // FUN-071 Obtener mensaje de resultado
    public function getMensaje()
    {
        // Devolvemos el mensaje de exito o error
        return $this->mensaje;
    }
}
?>