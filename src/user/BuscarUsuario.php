<?php
// CU-007 Buscar contenido, categoría o autor
// CLS-006 Clase para búsqueda de usuario
// TAB-006 Contenido, TAB-004 Categoria

class BuscarUsuario
{
    private $conn;

    // FUN-045 Constructor
    public function __construct($conn)
    {
        // Guardamos la conexion recibida
        $this->conn = $conn;
    }

    // FUN-046 Obtener parámetros de búsqueda y filtro
    public function obtenerParametros()
    {
        // Obtenemos los parametros de busqueda y paginacion desde GET o sesion
        $q = trim($_GET['q'] ?? '');
        $type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : 'contenido';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        if ($page < 1) $page = 1;
        $items_per_page = 10;
        $offset = ($page - 1) * $items_per_page;

        if (empty($q) && isset($_SESSION['last_search'])) {
            $q = trim($_SESSION['last_search']);
        }
        if (!empty($q)) {
            $_SESSION['last_search'] = $q;
        }
        return [$q, $type, $page, $items_per_page, $offset];
    }

    // FUN-047 Ejecutar búsqueda según tipo
    public function buscar($q, $type, $items_per_page, $offset)
    {
        // Ejecutamos la busqueda segun el tipo solicitado
        $q_like = '%' . $q . '%';
        $results = [];
        $totalResults = 0;

        if ($type === 'contenido') {
            // Buscamos contenidos por titulo
            $stmtCont = $this->conn->prepare("
                SELECT c.id, c.titulo, c.autor, c.precio_original, c.archivo, t.nombre_del_tipo
                FROM Contenido c
                JOIN TipoArchivo t ON c.tipo_archivo_id = t.id
                WHERE c.titulo ILIKE :q AND c.estado = 'disponible'
                ORDER BY c.fecha_de_subida DESC
                LIMIT :lim OFFSET :off
            ");
            $stmtCont->bindValue(':q', $q_like, \PDO::PARAM_STR);
            $stmtCont->bindValue(':lim', $items_per_page, \PDO::PARAM_INT);
            $stmtCont->bindValue(':off', $offset, \PDO::PARAM_INT);
            $stmtCont->execute();
            $results = $stmtCont->fetchAll(\PDO::FETCH_ASSOC);

            $stmtCount = $this->conn->prepare("SELECT COUNT(*) FROM Contenido WHERE titulo ILIKE :q AND estado = 'disponible'");
            $stmtCount->execute(['q' => $q_like]);
            $totalResults = $stmtCount->fetchColumn();
        } elseif ($type === 'categoria') {
            // Buscamos categorias por nombre
            $stmtCat = $this->conn->prepare("SELECT id, nombre, descripcion FROM Categoria WHERE nombre ILIKE :q AND estado = 'activa' ORDER BY id DESC LIMIT :lim OFFSET :off");
            $stmtCat->bindValue(':q', $q_like, \PDO::PARAM_STR);
            $stmtCat->bindValue(':lim', $items_per_page, \PDO::PARAM_INT);
            $stmtCat->bindValue(':off', $offset, \PDO::PARAM_INT);
            $stmtCat->execute();
            $results = $stmtCat->fetchAll(\PDO::FETCH_ASSOC);

            $stmtCount = $this->conn->prepare("SELECT COUNT(*) FROM Categoria WHERE nombre ILIKE :q AND estado = 'activa'");
            $stmtCount->execute(['q' => $q_like]);
            $totalResults = $stmtCount->fetchColumn();
        } elseif ($type === 'autor') {
            // Buscamos contenidos por autor
            $stmtCont = $this->conn->prepare("
                SELECT c.id, c.titulo, c.autor, c.precio_original, c.archivo, t.nombre_del_tipo
                FROM Contenido c
                JOIN TipoArchivo t ON c.tipo_archivo_id = t.id
                WHERE c.autor ILIKE :q AND c.estado = 'disponible'
                ORDER BY c.fecha_de_subida DESC
                LIMIT :lim OFFSET :off
            ");
            $stmtCont->bindValue(':q', $q_like, \PDO::PARAM_STR);
            $stmtCont->bindValue(':lim', $items_per_page, \PDO::PARAM_INT);
            $stmtCont->bindValue(':off', $offset, \PDO::PARAM_INT);
            $stmtCont->execute();
            $results = $stmtCont->fetchAll(\PDO::FETCH_ASSOC);

            $stmtCount = $this->conn->prepare("SELECT COUNT(*) FROM Contenido WHERE autor ILIKE :q AND estado = 'disponible'");
            $stmtCount->execute(['q' => $q_like]);
            $totalResults = $stmtCount->fetchColumn();
        }
        // Devolvemos los resultados y el total de resultados encontrados
        return [$results, $totalResults];
    }
}
?>