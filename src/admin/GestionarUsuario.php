<?php
namespace Admin;

// CU-018 Administrar Usuarios
// CLS-018 Clase para gestión de usuarios (admin)
// TAB-001 Usuario, VistaSaldo, Descarga

class GestionarUsuario
{
    private $conn;
    private $errores = [];

    // FUN-116 Constructor
    public function __construct($conn)
    {
        // Guardamos la conexion a la base de datos
        $this->conn = $conn;
    }

    // FUN-117 Obtener datos básicos del usuario
    public function obtenerUsuario($usuarioId)
    {
        // Obtenemos los datos principales del usuario por su id
        $stmt = $this->conn->prepare("SELECT id, nickname, correo_electronico, foto, fecha_de_registro FROM Usuario WHERE id = :id");
        $stmt->execute(['id' => $usuarioId]);
        $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$usuario) {
            $this->errores[] = "Usuario no encontrado.";
            return false;
        }
        return $usuario;
    }

    // FUN-118 Obtener saldo del usuario
    public function obtenerSaldo($usuarioId)
    {
        // Obtenemos el saldo actual del usuario desde la vista
        $stmt = $this->conn->prepare("SELECT saldo FROM VistaSaldo WHERE usuario_id = :id");
        $stmt->execute(['id' => $usuarioId]);
        return $stmt->fetchColumn();
    }

    // FUN-119 Obtener últimas descargas del usuario
    public function obtenerUltimasDescargas($usuarioId, $limite = 10)
    {
        // Obtenemos las ultimas descargas realizadas por el usuario
        $stmt = $this->conn->prepare("
            SELECT d.fecha_de_compra, d.precio_pagado, c.titulo, c.id AS contenido_id
            FROM Descarga d
            JOIN Contenido c ON d.contenido_id = c.id
            WHERE d.usuario_id = :id
            ORDER BY d.fecha_de_compra DESC
            LIMIT :limite
        ");
        $stmt->bindValue(':id', $usuarioId);
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // FUN-120 Verificar si el usuario descargó un contenido
    public function yaDescargo($usuarioId, $contenidoId)
    {
        // Verificamos si el usuario ya descargo un contenido especifico
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM Descarga WHERE usuario_id = :uid AND contenido_id = :cid");
        $stmt->execute(['uid' => $usuarioId, 'cid' => $contenidoId]);
        return $stmt->fetchColumn() > 0;
    }

    // FUN-121 Obtener errores
    public function getErrores()
    {
        // Devolvemos el arreglo de errores encontrados
        return $this->errores;
    }
}
?>