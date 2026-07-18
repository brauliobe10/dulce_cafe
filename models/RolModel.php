<?php

require_once __DIR__ . '/../config/conexion.php';

/**
 * Clase RolModel
 *
 * Gestiona las operaciones CRUD de la tabla roles.
 * Utiliza consultas preparadas PDO para evitar SQL Injection.
 */
class RolModel {

    /**
     * Obtiene todos los roles o uno específico por ID.
     *
     * @param int|null $id ID del rol (null para todos).
     * @return array|false
     */
    public static function mdlMostrarRoles($id = null) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return [];
        try {
            if ($id !== null) {
                $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = :id");
                $stmt->execute([':id' => $id]);
                return $stmt->fetch() ?: false;
            } else {
                $stmt = $pdo->prepare("SELECT * FROM roles ORDER BY nombre ASC");
                $stmt->execute();
                return $stmt->fetchAll() ?: [];
            }
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Crea un nuevo rol.
     *
     * @param array $datos Datos del rol.
     * @return bool
     */
    public static function mdlCrearRol($datos) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;
        try {
            $stmt = $pdo->prepare("INSERT INTO roles (nombre, descripcion) VALUES (:nombre, :descripcion)");
            return $stmt->execute($datos);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Actualiza un rol existente.
     *
     * @param array $datos Datos del rol incluyendo 'id'.
     * @return bool
     */
    public static function mdlEditarRol($datos) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;
        try {
            $stmt = $pdo->prepare("UPDATE roles SET nombre = :nombre, descripcion = :descripcion WHERE id = :id");
            return $stmt->execute($datos);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Elimina un rol por su ID.
     *
     * @param int $id ID del rol.
     * @return bool
     */
    public static function mdlEliminarRol($id) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;
        try {
            $stmt = $pdo->prepare("DELETE FROM roles WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
