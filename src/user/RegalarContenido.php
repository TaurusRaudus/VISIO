<?php
// CU-009 Regalar contenido
// CLS-008 Clase para regalar contenido
// TAB-001 Usuario, TAB-006 Contenido, TAB-009 Descarga, TAB-015 VistaSaldo

class RegalarContenido
{
    private $conn;
    private $remitente_id;
    private $errores = [];
    private $mensaje = '';
    private $tipo_mensaje = '';

    // FUN-058 Constructor
    public function __construct($conn, $remitente_id)
    {
        $this->conn = $conn;
        $this->remitente_id = $remitente_id;
    }

    // FUN-059 Verificar existencia del destinatario
    public function obtenerDestinatarioId($nickname)
    {
        $stmt = $this->conn->prepare("SELECT id FROM Usuario WHERE nickname = :nick");
        $stmt->execute(['nick' => trim($nickname)]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $this->errores[] = "Destinatario no encontrado.";
            $this->tipo_mensaje = "error";
            return false;
        }
        return $row['id'];
    }

    // FUN-060 Verificar límite de regalos diarios
    public function verificarLimiteRegalos()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM Descarga WHERE usuario_id = :uid AND es_regalo = TRUE AND fecha_de_compra::date = CURRENT_DATE");
        $stmt->execute(['uid' => $this->remitente_id]);
        $regalos_hoy = $stmt->fetchColumn();
        if ($regalos_hoy >= 3) {
            $this->errores[] = "Límite alcanzado, podrás regalar mañana.";
            $this->tipo_mensaje = "error";
            return false;
        }
        return true;
    }

    // FUN-061 Verificar saldo suficiente
    public function verificarSaldo($precio)
    {
        // Verificamos si el remitente tiene saldo suficiente para regalar el contenido
        $stmt = $this->conn->prepare("SELECT saldo FROM VistaSaldo WHERE usuario_id = :uid");
        $stmt->execute(['uid' => $this->remitente_id]);
        $saldo = $stmt->fetchColumn();
        if ($saldo < $precio) {
            $this->errores[] = "Saldo insuficiente. ¿Recargar ahora?";
            $this->tipo_mensaje = "saldo";
            return false;
        }
        return true;
    }

    // FUN-062 Obtener precio del contenido
    public function obtenerPrecioContenido($contenido_id)
    {
        // Obtenemos el precio original del contenido antes de regalar
        $stmt = $this->conn->prepare("SELECT precio_original FROM Contenido WHERE id = :id AND estado = 'disponible'");
        $stmt->execute(['id' => $contenido_id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $this->errores[] = "Contenido no disponible.";
            $this->tipo_mensaje = "error";
            return false;
        }
        return $row['precio_original'];
    }

    // FUN-063 Procesar el regalo (transacción)
    public function procesarRegalo($contenido_id, $destinatario_id, $precio)
    {
        // Procesamos el regalo de contenido realizando todas las inserciones necesarias y controlando la transaccion
        try {
            $this->conn->beginTransaction();

            // 1. Registramos la descarga como regalo para el destinatario (precio_pagado = 0)
            $stmt = $this->conn->prepare("
                INSERT INTO Descarga 
                  (fecha_de_compra, precio_pagado, aplica_descuento, es_regalo, usuario_id, contenido_id)
                VALUES (NOW(), 0, FALSE, TRUE, :destinatario, :contenido)
            ");
            $stmt->execute([
                'destinatario' => $destinatario_id,
                'contenido'    => $contenido_id
            ]);

            // 2. Registramos el movimiento de saldo para el remitente (no da acceso al contenido)
            $stmt = $this->conn->prepare("
                INSERT INTO Descarga 
                  (fecha_de_compra, precio_pagado, aplica_descuento, es_regalo, usuario_id, contenido_id)
                VALUES (NOW(), :precio, FALSE, TRUE, :remitente, NULL)
            ");
            $stmt->execute([
                'precio'     => $precio,
                'remitente'  => $this->remitente_id
            ]);

            // 3. TRACKING: Insertamos en Regala
            $stmt = $this->conn->prepare("
                INSERT INTO Regala
                  (donante_id, receptor_id, contenido_id)
                VALUES
                  (:donante, :receptor, :contenido)
            ");
            $stmt->execute([
                'donante'    => $this->remitente_id,
                'receptor'   => $destinatario_id,
                'contenido'  => $contenido_id
            ]);

            $this->conn->commit();
            $this->mensaje = "Regalo enviado";
            $this->tipo_mensaje = "exito";
            return true;

        } catch (\Exception $e) {
            // Si ocurre un error, revertimos la transaccion y guardamos el error
            $this->conn->rollBack();
            $this->errores[] = "Error al procesar el regalo.";
            $this->tipo_mensaje = "error";
            return false;
        }
    }

    // FUN-064 Obtener mensaje de resultado
    public function getMensaje()
    {
        // Devolvemos el mensaje de exito o el primer error
        return $this->mensaje ?: ($this->errores[0] ?? '');
    }

    // FUN-065 Obtener tipo de mensaje
    public function getTipoMensaje()
    {
        // Devolvemos el tipo de mensaje para mostrar en la interfaz
        return $this->tipo_mensaje;
    }

    // FUN-066 Obtener errores
    public function getErrores()
    {
        // Devolvemos el arreglo de errores encontrados
        return $this->errores;
    }
}
?>