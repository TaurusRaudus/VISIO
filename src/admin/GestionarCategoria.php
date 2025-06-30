<?php
namespace Admin;

// CU-013 Agregar Categoría
// CLS-013 Clase para gestionar categorías
// TAB-004 Categoria

class GestionarCategoria
{
    private $conn;
    private $errores = [];
    private $mensaje = '';

    // FUN-088 Constructor
    public function __construct($conn)
    {
        // Guarda la conexion a la base de datos
        $this->conn = $conn;
    }

    // FUN-089 Validar nombre de la categoría
    public function validarNombre($nombre)
    {
        // Valida que el nombre tenga la longitud y caracteres permitidos
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

    // FUN-090 Verificar duplicidad de categoría padre
    public function esDuplicada($nombre)
    {
        // Verifica si ya existe una categoria padre con ese nombre
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS cuenta FROM Categoria WHERE nombre = :nombre AND padre_id IS NULL");
        $stmt->execute(['nombre' => $nombre]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($result && $result['cuenta'] > 0) {
            $this->errores[] = "Error categoría padre existente";
            return true;
        }
        return false;
    }

    // FUN-091 Insertar categoría padre
    public function insertarCategoriaPadre($nombre)
    {
        // Inserta una nueva categoria padre en la base de datos
        $stmt = $this->conn->prepare("INSERT INTO Categoria (nombre, descripcion, padre_id, estado) VALUES (:nombre, '', NULL, 'activa')");
        $stmt->execute(['nombre' => $nombre]);
        return $this->conn->lastInsertId();
    }

    // FUN-092 Agregar categoría (flujo principal)
    public function agregarCategoria($nombre)
    {
        // Valida el nombre y verifica duplicidad antes de insertar
        if (!$this->validarNombre($nombre)) {
            return false;
        }
        if ($this->esDuplicada($nombre)) {
            return false;
        }
        $id = $this->insertarCategoriaPadre($nombre);
        if ($id) {
            $this->mensaje = "Categoría agregada";
            return $id;
        } else {
            $this->errores[] = "Error al agregar la categoría.";
            return false;
        }
    }

    // FUN-093 Obtener errores
    public function getErrores()
    {
        // Devuelve el arreglo de errores
        return $this->errores;
    }

    // FUN-094 Obtener mensaje de éxito
    public function getMensaje()
    {
        // Devuelve el mensaje de exito
        return $this->mensaje;
    }
}
?>