<?php
namespace User;

// CU-017 Ver Rankings
// CLS-017 Clase para gestionar rankings de contenido

class GestionarRanking
{
    private $conn;
    private $errores = [];

    // FUN-138 Constructor
    public function __construct($conn)
    {
        // Guardamos la conexion recibida
        $this->conn = $conn;
    }

    /**
     * FUN-144 Ranking de contenidos más descargados (top 10)
     */
    public function obtenerRankingDescargados()
    {
        // Obtenemos el top 10 de contenidos mas descargados
        $stmt = $this->conn->prepare("
            SELECT c.id, c.titulo, c.archivo,
                COUNT(d.id) AS total_descargas
            FROM Contenido c
            LEFT JOIN Descarga d ON d.contenido_id = c.id
            WHERE c.estado = 'disponible'
            GROUP BY c.id
            HAVING COUNT(d.id) > 0
            ORDER BY total_descargas DESC, c.id ASC
            LIMIT 10
        ");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * FUN-145 Ranking de contenidos mejor valorados (top 10)
     */
    public function obtenerRankingValorados()
    {
        // Obtenemos el top 10 de contenidos mejor valorados
        $stmt = $this->conn->prepare("
            SELECT c.id, c.titulo, c.archivo,
                AVG(cal.nota) AS promedio_calificacion,
                COUNT(cal.id) AS total_calificaciones
            FROM Contenido c
            LEFT JOIN Calificacion cal ON cal.contenido_id = c.id
            WHERE c.estado = 'disponible'
            GROUP BY c.id
            HAVING COUNT(cal.id) > 0
            ORDER BY promedio_calificacion DESC, c.id ASC
            LIMIT 10
        ");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * FUN-146 Obtener errores
     */
    public function getErrores()
    {
        // Devolvemos el arreglo de errores encontrados
        return $this->errores;
    }
}
?>