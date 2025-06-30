<?php
namespace Admin;

// CU-015 Eliminar (Contenido, Categoría, Subcategoría, Usuario)
// CLS-015 Clase para gestionar eliminaciones lógicas y físicas
// TAB-006 Contenido, TAB-004 Categoria, TAB-001 Usuario

class GestionarEliminacion
{
    private $conn;
    private $errores = [];

    // FUN-109 Constructor
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // FUN-110 Borrado lógico de contenido (cambia estado y renombra)
    public function borrarContenido($id)
    {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE Contenido 
                 SET titulo = CONCAT(titulo, '__eliminado_', EXTRACT(EPOCH FROM NOW())::bigint), 
                     estado = 'no disponible' 
                 WHERE id = :id"
            );
            return $stmt->execute(['id' => $id]);
        } catch (\Exception $e) {
            $this->errores[] = $e->getMessage();
            return false;
        }
    }

    // FUN-111 Borrado lógico de categoría (y subcategorías recursivas)
    public function borrarCategoria($id)
    {
        try {
            // Obtener la categoría actual
            $stmt = $this->conn->prepare("SELECT * FROM Categoria WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $cat = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$cat) {
                $this->errores[] = "Categoría no encontrada.";
                return false;
            }

            // Buscar subcategorías
            $stmtSub = $this->conn->prepare("SELECT id FROM Categoria WHERE padre_id = :id");
            $stmtSub->execute(['id' => $id]);
            $subcats = $stmtSub->fetchAll(\PDO::FETCH_ASSOC);

            $this->conn->beginTransaction();

            // Inactivar recursivamente subcategorías primero
            foreach ($subcats as $subcat) {
                $this->borrarCategoria($subcat['id']);
            }

            // Cambiar el nombre antes de inactivar para liberar el nombre original
            $nuevoNombre = $cat['nombre'] . '__eliminada_' . time();

            if (empty($cat['padre_id'])) {
                // Es categoría padre: inactiva la categoría y todos sus contenidos
                $this->conn->prepare("UPDATE Categoria SET nombre = :nuevoNombre, estado = 'inactiva' WHERE id = :id")
                    ->execute(['nuevoNombre' => $nuevoNombre, 'id' => $id]);
                $this->conn->prepare("UPDATE Contenido SET estado = 'no disponible' WHERE categoria_id = :id")
                    ->execute(['id' => $id]);
            } else {
                // Es subcategoría: mover sus contenidos a la categoría padre y poner estado inactiva
                $this->conn->prepare("UPDATE Contenido SET categoria_id = :padre_id WHERE categoria_id = :id")
                    ->execute(['padre_id' => $cat['padre_id'], 'id' => $id]);
                $this->conn->prepare("UPDATE Categoria SET nombre = :nuevoNombre, estado = 'inactiva' WHERE id = :id")
                    ->execute(['nuevoNombre' => $nuevoNombre, 'id' => $id]);
            }

            $this->conn->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $this->errores[] = $e->getMessage();
            return false;
        }
    }

    // FUN-112 Borrado lógico de subcategoría (alias de borrarCategoria)
    public function borrarSubcategoria($id)
    {
        return $this->borrarCategoria($id);
    }

    // FUN-113 Borrado lógico de usuario
    public function borrarUsuario($id)
    {
        // Cambiamos el estado del usuario a inactivo
        try {
            $stmt = $this->conn->prepare("UPDATE Usuario SET estado = 'inactivo' WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (\Exception $e) {
            $this->errores[] = $e->getMessage();
            return false;
        }
    }

    // FUN-114 Validar contraseña de administrador
    public function validarPasswordAdmin($correo, $password)
    {
        // Validamos la contraseña del administrador antes de realizar acciones críticas
        $stmt = $this->conn->prepare("SELECT contraseña FROM Administrador WHERE correo_electronico = :correo");
        $stmt->execute(['correo' => $correo]);
        $admin = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$admin || !password_verify($password, $admin['contraseña'])) {
            $this->errores[] = "Contraseña incorrecta.";
            return false;
        }
        return true;
    }

    // FUN-115 Obtener errores
    public function getErrores()
    {
        return $this->errores;
    }
}
?>