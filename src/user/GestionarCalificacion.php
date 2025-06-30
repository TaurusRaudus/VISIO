<?php
namespace User;

// CU-016 Calificar Contenido
// CLS-016 Clase para gestionar calificaciones de contenido

class GestionarCalificacion
{
    private $conn;
    private $errores = [];
    private $mensaje = '';

    // FUN-125 Constructor
    public function __construct($conn)
    {
        // Guardamos la conexion recibida
        $this->conn = $conn;
    }

    // FUN-126 Verificar si el usuario tiene acceso al contenido (descargado)
    public function usuarioTieneAcceso($usuario_id, $contenido_id)
    {
        // Verificamos si el usuario ha descargado el contenido antes de permitir calificar
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM Descarga WHERE usuario_id = :uid AND contenido_id = :cid");
        $stmt->execute(['uid' => $usuario_id, 'cid' => $contenido_id]);
        if ($stmt->fetchColumn() == 0) {
            $this->errores[] = "No tienes acceso a este contenido.";
            return false;
        }
        return true;
    }

    // FUN-127 Obtener calificación previa (si existe)
    public function obtenerCalificacion($usuario_id, $contenido_id)
    {
        // Obtenemos la calificacion previa del usuario para el contenido si existe
        $stmt = $this->conn->prepare("SELECT nota, mensaje FROM Calificacion WHERE usuario_id = :uid AND contenido_id = :cid");
        $stmt->execute(['uid' => $usuario_id, 'cid' => $contenido_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // FUN-128 Verificar si ya existe calificación
    public function yaCalificado($usuario_id, $contenido_id)
    {
        // Verificamos si el usuario ya califico este contenido
        $stmt = $this->conn->prepare("SELECT id FROM Calificacion WHERE usuario_id = :uid AND contenido_id = :cid");
        $stmt->execute(['uid' => $usuario_id, 'cid' => $contenido_id]);
        return $stmt->fetchColumn() ? true : false;
    }

    // FUN-129 Guardar o actualizar calificación
    public function guardarCalificacion($usuario_id, $contenido_id, $nota, $mensaje)
    {
        // Guardamos o actualizamos la calificacion del usuario para el contenido
        if (!$this->usuarioTieneAcceso($usuario_id, $contenido_id)) {
            return false;
        }
        if ($this->yaCalificado($usuario_id, $contenido_id)) {
            // Actualizamos la calificacion existente
            $stmt = $this->conn->prepare("UPDATE Calificacion SET nota = :nota, mensaje = :mensaje, fecha_de_calificacion = NOW() WHERE usuario_id = :uid AND contenido_id = :cid");
            $stmt->execute([
                'nota' => $nota,
                'mensaje' => $mensaje,
                'uid' => $usuario_id,
                'cid' => $contenido_id
            ]);
            $this->mensaje = "Calificación actualizada";
        } else {
            // Insertamos una nueva calificacion
            $stmt = $this->conn->prepare("INSERT INTO Calificacion (fecha_de_calificacion, nota, mensaje, usuario_id, contenido_id) VALUES (NOW(), :nota, :mensaje, :uid, :cid)");
            $stmt->execute([
                'nota' => $nota,
                'mensaje' => $mensaje,
                'uid' => $usuario_id,
                'cid' => $contenido_id
            ]);
            $this->mensaje = "Calificación guardada";
        }
        return true;
    }

    // FUN-130 Obtener datos del contenido (para mostrar imagen y título)
    public function obtenerDatosContenido($contenido_id)
    {
        // Obtenemos los datos del contenido para mostrar imagen y titulo
        $stmt = $this->conn->prepare("SELECT archivo, titulo FROM Contenido WHERE id = :id");
        $stmt->execute(['id' => $contenido_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // FUN-131 Obtener errores
    public function getErrores()
    {
        // Devolvemos el arreglo de errores encontrados
        return $this->errores;
    }

    // FUN-132 Obtener mensaje de éxito
    public function getMensaje()
    {
        // Devolvemos el mensaje de exito
        return $this->mensaje;
    }
}
?>