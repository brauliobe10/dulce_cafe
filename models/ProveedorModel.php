<?php

require_once __DIR__ . '/../config/conexion.php';

/**
 * Clase ProveedorModel
 *
 * Gestiona las operaciones CRUD de la tabla proveedores.
 * Utiliza consultas preparadas PDO para evitar SQL Injection.
 */
class ProveedorModel {

    /**
     * Obtiene todos los proveedores o uno específico por ID.
     *
     * @param int|null $id ID del proveedor (null para todos).
     * @return array|false
     */
    public static function mdlMostrarProveedores($id = null) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return [];
        try {
            if ($id !== null) {
                $stmt = $pdo->prepare("SELECT * FROM proveedores WHERE id = :id");
                $stmt->execute([':id' => $id]);
                return $stmt->fetch() ?: false;
            } else {
                $stmt = $pdo->prepare("SELECT * FROM proveedores ORDER BY nombre ASC");
                $stmt->execute();
                return $stmt->fetchAll() ?: [];
            }
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Crea un nuevo proveedor.
     *
     * @param array $datos Datos del proveedor.
     * @return bool
     */
    public static function mdlCrearProveedor($datos) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;
        try {
            $stmt = $pdo->prepare("INSERT INTO proveedores (nombre, contacto, email, telefono, direccion, producto_principal) 
                                   VALUES (:nombre, :contacto, :email, :telefono, :direccion, :producto_principal)");
            return $stmt->execute($datos);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Actualiza un proveedor existente.
     *
     * @param array $datos Datos del proveedor incluyendo 'id'.
     * @return bool
     */
    public static function mdlEditarProveedor($datos) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;
        try {
            $stmt = $pdo->prepare("UPDATE proveedores SET nombre = :nombre, contacto = :contacto, email = :email, 
                                   telefono = :telefono, direccion = :direccion, producto_principal = :producto_principal 
                                   WHERE id = :id");
            return $stmt->execute($datos);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Elimina un proveedor por su ID.
     *
     * @param int $id ID del proveedor.
     * @return bool
     */
    public static function mdlEliminarProveedor($id) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;
        try {
            $stmt = $pdo->prepare("DELETE FROM proveedores WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
