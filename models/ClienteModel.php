<?php

require_once __DIR__ . '/../config/conexion.php';

/**
 * Clase ClienteModel
 *
 * Gestiona las operaciones CRUD de la tabla clientes.
 * Utiliza consultas preparadas PDO para evitar SQL Injection.
 */
class ClienteModel {

    /**
     * Obtiene todos los clientes o uno específico por ID.
     *
     * @param int|null $id ID del cliente (null para todos).
     * @return array|false
     */
    public static function mdlMostrarClientes($id = null) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return [];
        try {
            if ($id !== null) {
                $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = :id");
                $stmt->execute([':id' => $id]);
                return $stmt->fetch() ?: false;
            } else {
                $stmt = $pdo->prepare("SELECT * FROM clientes ORDER BY nombre ASC");
                $stmt->execute();
                return $stmt->fetchAll() ?: [];
            }
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Crea un nuevo cliente.
     *
     * @param array $datos Datos del cliente.
     * @return bool
     */
    public static function mdlCrearCliente($datos) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;
        try {
            $stmt = $pdo->prepare("INSERT INTO clientes (nombre, email, telefono, direccion, puntos) 
                                   VALUES (:nombre, :email, :telefono, :direccion, :puntos)");
            return $stmt->execute($datos);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Actualiza un cliente existente.
     *
     * @param array $datos Datos del cliente incluyendo 'id'.
     * @return bool
     */
    public static function mdlEditarCliente($datos) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;
        try {
            $stmt = $pdo->prepare("UPDATE clientes SET nombre = :nombre, email = :email, telefono = :telefono, 
                                   direccion = :direccion, puntos = :puntos 
                                   WHERE id = :id");
            return $stmt->execute($datos);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Elimina un cliente por su ID.
     *
     * @param int $id ID del cliente.
     * @return bool
     */
    public static function mdlEliminarCliente($id) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;
        try {
            $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
