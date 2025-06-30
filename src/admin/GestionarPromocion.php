<?php
namespace Admin;

// CU-012 Agregar Promoción
// CLS-012 Clase para gestionar promociones
// TAB-008 Promocion, TAB-006 Contenido

class GestionarPromocion {
    private $conn;
    private $errors = [];

    // FUN-095 Constructor
    public function __construct($conn) {
        // Guardamos la conexion a la base de datos
        $this->conn = $conn;
    }

    // FUN-096 Validar si el contenido ya tiene promoción activa
    public function contenidoConPromocion($contenidoId) {
        // Verificamos si el contenido ya tiene una promocion activa
        $stmt = $this->conn->prepare("SELECT promocion_id FROM Contenido WHERE id = :id");
        $stmt->execute(['id' => $contenidoId]);
        $contenido = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($contenido && !is_null($contenido['promocion_id'])) {
            $this->errors[] = "Error, el contenido ya tiene una promoción activa.";
            return true;
        }
        return false;
    }

    // FUN-097 Insertar promoción en la tabla Promocion
    public function insertarPromocion($descuento, $fechaInicio, $fechaFin) {
        // Insertamos una nueva promocion en la tabla Promocion
        $stmt = $this->conn->prepare("
            INSERT INTO Promocion (porcentaje_de_descuento, fecha_inicio, fecha_fin)
            VALUES (:descuento, :fecha_inicio, :fecha_fin)
        ");
        $stmt->execute([
            'descuento'    => $descuento,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin'    => $fechaFin
        ]);
        return $this->conn->lastInsertId();
    }

    // FUN-098 Asociar promoción a contenido
    public function asociarPromocionAContenido($contenidoId, $promocionId) {
        // Asociamos la promocion creada al contenido correspondiente
        $stmt = $this->conn->prepare("
            UPDATE Contenido
            SET promocion_id = :promocion_id
            WHERE id = :id
        ");
        $stmt->execute([
            'promocion_id' => $promocionId,
            'id'           => $contenidoId
        ]);
    }

    // FUN-099 Agregar promoción (flujo principal)
    public function agregarPromocion($contenidoId, $fechaInicio, $fechaFin, $descuento, $razon = '') {
        // Agregamos una promocion a un contenido si no tiene una activa
        if ($this->contenidoConPromocion($contenidoId)) {
            return false;
        }
        try {
            $promocionId = $this->insertarPromocion($descuento, $fechaInicio, $fechaFin);
            $this->asociarPromocionAContenido($contenidoId, $promocionId);
            // Razon Opcional
            return $promocionId;
        } catch (\PDOException $e) {
            $this->errors[] = "Error al insertar la promoción: " . $e->getMessage();
            return false;
        }
    }

    // FUN-100 Obtener errores
    public function getErrors() {
        // Devolvemos el arreglo de errores encontrados
        return $this->errors;
    }
}
?>