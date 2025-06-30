<?php
// CU-008 Descargar contenido
// CLS-007 Clase para gestionar la descarga de contenido
// TAB-006 Contenido, TAB-009 Descarga, TAB-015 VistaSaldo

class DescargarContenido
{
    private $conn;
    private $usuario_id;

    // FUN-048 Constructor
    public function __construct($conn, $usuario_id)
    {
        // Guardamos la conexion y el id del usuario
        $this->conn = $conn;
        $this->usuario_id = $usuario_id;
    }

    // FUN-049 Obtener información del contenido y calcular precio final
    public function obtenerInfoContenido($contenido_id)
    {
        // Obtenemos la informacion del contenido y calculamos el precio final segun promociones o gastos
        $stmt = $this->conn->prepare("SELECT c.*, p.porcentaje_de_descuento, p.fecha_inicio, p.fecha_fin
            FROM Contenido c
            LEFT JOIN Promocion p ON c.promocion_id = p.id
            WHERE c.id = :id AND c.estado = 'disponible'");
        $stmt->execute(['id' => $contenido_id]);
        $contenido = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$contenido) return [null, null, null, null];

        $precio = floatval($contenido['precio_original']);
        $precio_final = $precio;
        $promo = false;

        if ($contenido['porcentaje_de_descuento'] && strtotime($contenido['fecha_inicio']) <= time() && strtotime($contenido['fecha_fin']) >= time()) {
            $precio_final = $precio * (1 - $contenido['porcentaje_de_descuento'] / 100);
            $promo = true;
        } else {
            // Verificamos gastos acumulados del usuario en el ultimo año
            $stmtGastos = $this->conn->prepare("SELECT COALESCE(SUM(precio_pagado),0) FROM Descarga WHERE usuario_id = :uid AND fecha_de_compra >= NOW() - INTERVAL '1 year'");
            $stmtGastos->execute(['uid' => $this->usuario_id]);
            $gastos = floatval($stmtGastos->fetchColumn());
            if ($gastos >= 100) {
                $precio_final = $precio * 0.8;
            }
        }
        return [$contenido, $precio, $precio_final, $promo];
    }

    // FUN-050 Obtener saldo actual del usuario
    public function obtenerSaldo()
    {
        // Obtenemos el saldo actual del usuario desde la vista
        $stmtSaldo = $this->conn->prepare("SELECT saldo FROM VistaSaldo WHERE usuario_id = :id");
        $stmtSaldo->execute(['id' => $this->usuario_id]);
        return floatval($stmtSaldo->fetchColumn());
    }

    // FUN-051 Verificar si el usuario ya descargó el contenido
    public function yaDescargado($contenido_id)
    {
        // Verificamos si el usuario ya descargo el contenido
        $stmtCheck = $this->conn->prepare("SELECT COUNT(*) FROM Descarga WHERE usuario_id = :uid AND contenido_id = :cid");
        $stmtCheck->execute(['uid' => $this->usuario_id, 'cid' => $contenido_id]);
        return $stmtCheck->fetchColumn() > 0;
    }

    // FUN-052 Procesar compra y registrar descarga
    public function procesarCompra($contenido_id, $precio_final, $precio_original)
    {
        // Procesamos la compra y registramos la descarga si el usuario tiene saldo suficiente
        $saldo = $this->obtenerSaldo();
        if ($saldo < $precio_final) {
            return "Saldo insuficiente.";
        }
        if ($this->yaDescargado($contenido_id)) {
            return "Ya has adquirido este contenido. Puedes descargarlo de nuevo.";
        }
        try {
            $this->conn->beginTransaction();
            $aplica_descuento = ($precio_final < $precio_original) ? 'true' : 'false';
            $stmt = $this->conn->prepare("INSERT INTO Descarga (fecha_de_compra, precio_pagado, aplica_descuento, es_regalo, usuario_id, contenido_id) VALUES (NOW(), :precio, :desc, false, :uid, :cid)");
            $stmt->execute([
                'precio' => $precio_final,
                'desc' => $aplica_descuento,
                'uid' => $this->usuario_id,
                'cid' => $contenido_id
            ]);
            $this->conn->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return "Error al procesar la descarga: " . $e->getMessage();
        }
    }

    // FUN-053 Obtener nombre de archivo para descarga directa
    public function obtenerArchivo($contenido_id)
    {
        // Obtenemos el nombre del archivo para la descarga directa
        $stmtArchivo = $this->conn->prepare("SELECT archivo FROM Contenido WHERE id = :id");
        $stmtArchivo->execute(['id' => $contenido_id]);
        return $stmtArchivo->fetchColumn();
    }
}
?>