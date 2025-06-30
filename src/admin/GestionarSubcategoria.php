<?php
namespace Admin;

// CU-014 Agregar Subcategoría
// CLS-014 Clase para gestionar subcategorías
// TAB-004 Categoria

class GestionarSubcategoria
{
    private $conn;
    private $errores = [];
    private $mensaje = '';

    // FUN-101 Constructor
    public function __construct($conn)
    {
        // Guardamos la conexion a la base de datos
        $this->conn = $conn;
    }

    // FUN-102 Validar nombre de la subcategoría
    public function validarNombre($nombre)
    {
        // Validamos que el nombre tenga la longitud y caracteres permitidos
        if (strlen($nombre) < 3 || strlen($nombre) > 50) {
            $this->errores[] = "El nombre debe tener entre 3 y 50 caracteres.";
            return false;
        }
        if (!preg_match('/^[a-zA-ZáéíóúñÑ\s]+$/', $nombre)) {
            $this->errores[] = "El nombre solo debe contener letras y espacios.";
            return false;
        }
        return true;
    }

    // FUN-103 Verificar existencia de la categoría padre
    public function existePadre($padre_id)
    {
        // Verificamos que la categoria padre exista antes de agregar la subcategoria
        $stmt = $this->conn->prepare("SELECT id FROM Categoria WHERE id = :padre_id");
        $stmt->execute(['padre_id' => $padre_id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $this->errores[] = "La categoría padre no existe.";
            return false;
        }
        return true;
    }

    // FUN-104 Verificar duplicidad de subcategoría bajo el mismo padre
    public function esDuplicada($nombre, $padre_id)
    {
        // Verificamos si ya existe una subcategoria con ese nombre bajo el mismo padre
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS cuenta FROM Categoria WHERE nombre = :nombre AND padre_id = :padre_id");
        $stmt->execute(['nombre' => $nombre, 'padre_id' => $padre_id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($result && $result['cuenta'] > 0) {
            $this->errores[] = "Error: ya existe una subcategoría con ese nombre bajo esta categoría padre.";
            return true;
        }
        return false;
    }

    // FUN-105 Insertar subcategoría
    public function insertarSubcategoria($nombre, $padre_id)
    {
        // Insertamos la nueva subcategoria en la base de datos
        $stmt = $this->conn->prepare("INSERT INTO Categoria (nombre, descripcion, padre_id) VALUES (:nombre, '', :padre_id)");
        $stmt->execute(['nombre' => $nombre, 'padre_id' => $padre_id]);
        return $this->conn->lastInsertId();
    }

    // FUN-106 Agregar subcategoría (flujo principal)
    public function agregarSubcategoria($nombre, $padre_id)
    {
        // Ejecutamos todas las validaciones y agregamos la subcategoria si es valido
        if (!$this->existePadre($padre_id)) {
            return false;
        }
        if (!$this->validarNombre($nombre)) {
            return false;
        }
        if ($this->esDuplicada($nombre, $padre_id)) {
            return false;
        }
        $id = $this->insertarSubcategoria($nombre, $padre_id);
        if ($id) {
            $this->mensaje = "Subcategoría agregada correctamente.";
            return $id;
        } else {
            $this->errores[] = "Error al agregar la subcategoría.";
            return false;
        }
    }

    // FUN-107 Obtener errores
    public function getErrores()
    {
        // Devolvemos el arreglo de errores encontrados
        return $this->errores;
    }

    // FUN-108 Obtener mensaje de éxito
    public function getMensaje()
    {
        // Devolvemos el mensaje de exito
        return $this->mensaje;
    }
}
?>