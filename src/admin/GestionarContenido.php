<?php
namespace Admin;

// CU-011 Agregar Contenido
// CLS-011 Clase para gestionar contenido (alta, validación, archivo)
// TAB-006 Contenido, TAB-004 Categoria, TAB-007 TipoArchivo

class GestionarContenido {
    private $conn;
    private $errors = [];

    // FUN-077 Constructor
    public function __construct($conn) {
        // Guardamos la conexion a la base de datos
        $this->conn = $conn;
    }

    // FUN-078 Validar datos del formulario de contenido
    public function validarDatos($data, $file) {
        // Validamos los datos recibidos del formulario y el archivo subido
        $titulo = trim($data['titulo'] ?? '');
        $autor = trim($data['autor'] ?? '');
        $precio = trim($data['precio'] ?? '');
        $formato = trim($data['formato'] ?? '');
        $categoria = $data['categoria'] ?? null;

        if (empty($titulo) || empty($autor) || $precio === '' || empty($formato) || !$categoria) {
            $this->errors[] = "Todos los campos requeridos deben ser llenados.";
        }
        if (strlen($titulo) < 5 || strlen($titulo) > 60) {
            $this->errors[] = "El título debe tener entre 5 y 60 caracteres.";
        }
        if (!is_numeric($precio) || floatval($precio) < 0) {
            $this->errors[] = "El precio debe ser mayor o igual a 0.";
        }
        if (!in_array($formato, ['Imagen', 'Audio', 'Video'])) {
            $this->errors[] = "Formato inválido.";
        }
        if ($categoria <= 0) {
            $this->errors[] = "Seleccione una categoría válida.";
        }
        if (!isset($file['archivo']) || $file['archivo']['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = "Debe subir un archivo válido.";
        } else {
            $maxSize = 100 * 1024 * 1024; // 100MB
            if ($file['archivo']['size'] > $maxSize) {
                $this->errors[] = "El archivo excede el tamaño máximo permitido (100MB).";
            }
        }
        return empty($this->errors);
    }

    // FUN-079 Verificar duplicidad de título
    public function verificarDuplicado($titulo) {
        // Verificamos si ya existe un contenido con el mismo titulo
        $stmtDup = $this->conn->prepare("SELECT COUNT(*) AS cuenta FROM Contenido WHERE titulo = :titulo");
        $stmtDup->execute(['titulo' => $titulo]);
        $resultDup = $stmtDup->fetch(\PDO::FETCH_ASSOC);
        if ($resultDup['cuenta'] > 0) {
            $this->errors[] = "Contenido duplicado, error.";
            return false;
        }
        return true;
    }

    // FUN-080 Obtener mapping dinámico de mimetypes permitidos
    public function getAllowedMimeTypes($conn = null) {
        // Obtenemos los mimetypes permitidos para cada tipo de archivo
        $conn = $conn ?: $this->conn;
        $stmt = $conn->query("SELECT nombre_del_tipo, mimetype FROM TipoArchivo");
        $tipos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $mapping = [
            'Imagen' => [],
            'Audio' => [],
            'Video' => []
        ];
        foreach ($tipos as $tipo) {
            $nombre = strtolower($tipo['nombre_del_tipo']);
            if (in_array($nombre, ['jpeg', 'png', 'jpg', 'gif'])) {
                $mapping['Imagen'][] = $tipo['mimetype'];
            } elseif (in_array($nombre, ['mpeg', 'mp3', 'wav'])) {
                $mapping['Audio'][] = $tipo['mimetype'];
            } elseif (in_array($nombre, ['quicktime', 'mp4', 'm4v', 'avi', 'mov', 'mkv'])) {
                $mapping['Video'][] = $tipo['mimetype'];
            }
        }
        return $mapping;
    }

    // FUN-081 Obtener mapping de mimetype a ID de tipo_archivo
    public function obtenerMappingTipoArchivo() {
        // Obtenemos el mapping de mimetype a id y nombre de tipo de archivo
        $stmt = $this->conn->query("SELECT id, nombre_del_tipo, mimetype FROM TipoArchivo");
        $mapping = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $mapping[$row['mimetype']] = [
                'id' => $row['id'],
                'nombre' => $row['nombre_del_tipo']
            ];
        }
        return $mapping;
    }

    // FUN-082 Guardar archivo subido y devolver nombre
    public function guardarArchivo($file) {
        // Guardamos el archivo subido en la carpeta uploads y devuelve el nombre generado
        $nombreOriginal = $file['archivo']['name'];
        $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'mp3', 'wav', 'mp4', 'avi', 'mov', 'mkv'];
        if (!in_array($ext, $permitidas)) {
            $this->errors[] = "Tipo de archivo no permitido.";
            return false;
        }
        $nuevoNombre = uniqid("contenido_") . "_" . $nombreOriginal;
        $rutaDestino = __DIR__ . "/../../public/uploads/" . $nuevoNombre;
        if (!move_uploaded_file($file['archivo']['tmp_name'], $rutaDestino)) {
            $this->errors[] = "Error al guardar el archivo.";
            return false;
        }
        return $nuevoNombre;
    }

    // FUN-083 Agregar contenido a la base de datos
    public function agregarContenido($postData, $fileData) {
        // Agregamos un nuevo contenido a la base de datos si pasa todas las validaciones
        if (!$this->validarDatos($postData, $fileData)) {
            return false;
        }
        $titulo         = trim($postData['titulo']);
        $autor          = trim($postData['autor']);
        $precio         = trim($postData['precio']);
        $descripcion    = trim($postData['descripcion'] ?? '');
        $idCategoria    = $postData['categoria'];
        $formatoElegido = trim($postData['formato']);

        if (!$this->verificarDuplicado($titulo)) {
            return false;
        }

        // Detectar mimetype real
        $tmpName  = $fileData['archivo']['tmp_name'];
        $origName = basename($fileData['archivo']['name']);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $tmpName);
        finfo_close($finfo);

        $allowedMapping = $this->getAllowedMimeTypes();
        if (!isset($allowedMapping[$formatoElegido]) || !in_array($mimeType, $allowedMapping[$formatoElegido])) {
            $this->errors[] = "El archivo subido no corresponde al formato seleccionado (se esperaba '$formatoElegido' y se detectó '$mimeType').";
            return false;
        }

        $maxSize = 100 * 1024 * 1024;
        if ($fileData['archivo']['size'] > $maxSize) {
            $this->errors[] = "El archivo excede el tamaño máximo permitido de 100 MB.";
            return false;
        }

        $nuevoNombre = $this->guardarArchivo($fileData);
        if (!$nuevoNombre) {
            return false;
        }

        $tamanoMB = round($fileData['archivo']['size'] / (1024 * 1024), 2);

        $mappingTipoArchivo = $this->obtenerMappingTipoArchivo();
        if (!isset($mappingTipoArchivo[$mimeType])) {
            $this->errors[] = "No se encontró el registro en TipoArchivo para el mimetype detectado.";
            return false;
        }
        $tipoArchivoId = $mappingTipoArchivo[$mimeType]['id'];

        try {
            // NOTA: la columna correcta es "tamaño_mb" (minúsculas)
            $stmt = $this->conn->prepare("
                INSERT INTO Contenido
                (titulo, autor, descripcion, precio_original, \"tamaño_mb\", fecha_de_subida, tipo_archivo_id, categoria_id, promocion_id, archivo, estado)
                VALUES
                (:titulo, :autor, :descripcion, :precio, :tamanoMB, NOW(), :tipo_archivo_id, :categoria, NULL, :archivo, 'disponible')
            ");
            $params = [
                'titulo'          => $titulo,
                'autor'           => $autor,
                'descripcion'     => $descripcion,
                'precio'          => $precio,
                'tamanoMB'        => $tamanoMB,
                'tipo_archivo_id' => $tipoArchivoId,
                'categoria'       => $idCategoria,
                'archivo'         => $nuevoNombre
            ];
            $result = $stmt->execute($params);
            if (!$result) {
                $this->errors[] = "Error al insertar el contenido en la base de datos.";
                return false;
            }
            return $this->conn->lastInsertId();
        } catch (\Exception $e) {
            $this->errors[] = "Error al agregar contenido: " . $e->getMessage();
            return false;
        }
    }

    // FUN-084 Obtener errores
    public function getErrors() {
        // Devolvemos el arreglo de errores encontrados
        return $this->errors;
    }
}
?>