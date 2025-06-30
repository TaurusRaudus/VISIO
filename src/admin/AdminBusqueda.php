<?php
// CU-007 Buscar (Admin)
// CLS-010 Clase para búsqueda de administrador
// TAB-006 Contenido, TAB-004 Categoria, TAB-001 Usuario

class AdminBusqueda
{
    private $conn;

    // FUN-072 Constructor
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // FUN-073 Obtener y validar parámetros de búsqueda
    public function obtenerParametros()
    {
        $q = trim($_GET['q'] ?? '');
        $type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : '';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        if ($page < 1) $page = 1;
        $items_per_page = 10;
        $offset = ($page - 1) * $items_per_page;

        $mensaje_busqueda = "";
        if (empty($q)) {
            $mensaje_busqueda = "Ingrese un texto valido para buscar.";
        } elseif (strlen($q) < 3) {
            $mensaje_busqueda = "Ingrese al menos 3 letras para buscar.";
        } elseif (strlen($q) > 60) {
            $mensaje_busqueda = "El texto de busqueda no debe ser mayor a 60 caracteres.";
        }
        return [$q, $type, $page, $items_per_page, $offset, $mensaje_busqueda];
    }

    // FUN-074 Ejecutar búsqueda según tipo
    public function buscar($q, $type, $items_per_page, $offset)
    {
        // Prepara el string para busqueda con comodines
        $q_like = '%' . $q . '%';
        $results = [];
        $totalResults = 0;

        if ($type === 'contenido') {
            // Busca contenidos por titulo
            $stmtCont = $this->conn->prepare("SELECT id, titulo, autor, precio_original, archivo 
                                    FROM Contenido 
                                    WHERE titulo ILIKE :q AND estado = 'disponible'
                                    ORDER BY fecha_de_subida DESC 
                                    LIMIT :limit OFFSET :offset");
            $stmtCont->bindValue(':q', $q_like, \PDO::PARAM_STR);
            $stmtCont->bindValue(':limit', $items_per_page, \PDO::PARAM_INT);
            $stmtCont->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmtCont->execute();
            $results = $stmtCont->fetchAll(\PDO::FETCH_ASSOC);

            $stmtCount = $this->conn->prepare("SELECT COUNT(*) as total FROM Contenido WHERE titulo ILIKE :q AND estado = 'disponible'");
            $stmtCount->execute(['q' => $q_like]);
            $totalResults = $stmtCount->fetch(\PDO::FETCH_ASSOC)['total'];
        } elseif ($type === 'categoria') {
            // Busca categorias por nombre
            $stmtCat = $this->conn->prepare("SELECT id, nombre, padre_id 
                                   FROM Categoria 
                                   WHERE nombre ILIKE :q AND estado = 'activa'
                                   ORDER BY id DESC 
                                   LIMIT :limit OFFSET :offset");
            $stmtCat->bindValue(':q', $q_like, \PDO::PARAM_STR);
            $stmtCat->bindValue(':limit', $items_per_page, \PDO::PARAM_INT);
            $stmtCat->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmtCat->execute();
            $results = $stmtCat->fetchAll(\PDO::FETCH_ASSOC);

            $stmtCount = $this->conn->prepare("SELECT COUNT(*) as total FROM Categoria WHERE nombre ILIKE :q AND estado = 'activa'");
            $stmtCount->execute(['q' => $q_like]);
            $totalResults = $stmtCount->fetch(\PDO::FETCH_ASSOC)['total'];
        } elseif ($type === 'usuario') {
            // Busca usuarios por nickname
            $stmtUser = $this->conn->prepare("SELECT id, nickname, correo_electronico, foto 
                                    FROM Usuario 
                                    WHERE nickname ILIKE :q 
                                    ORDER BY id DESC 
                                    LIMIT :limit OFFSET :offset");
            $stmtUser->bindValue(':q', $q_like, \PDO::PARAM_STR);
            $stmtUser->bindValue(':limit', $items_per_page, \PDO::PARAM_INT);
            $stmtUser->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmtUser->execute();
            $results = $stmtUser->fetchAll(\PDO::FETCH_ASSOC);

            $stmtCount = $this->conn->prepare("SELECT COUNT(*) as total FROM Usuario WHERE nickname ILIKE :q");
            $stmtCount->execute(['q' => $q_like]);
            $totalResults = $stmtCount->fetch(\PDO::FETCH_ASSOC)['total'];
        }
        // Devuelve los resultados y el total de resultados encontrados
        return [$results, $totalResults];
    }
}
?>