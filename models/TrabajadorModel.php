<?php

require_once __DIR__ . '/../config/conexion.php';

/**
 * Clase TrabajadorModel
 *
 * Gestiona las operaciones CRUD de la tabla trabajadores.
 * Utiliza consultas preparadas PDO para evitar SQL Injection.
 */
class TrabajadorModel {

    /**
     * Obtiene todos los trabajadores o uno específico por ID.
     *
     * @param int|null $id ID del trabajador (null para todos).
     * @return array|false
     */
    public static function mdlMostrarTrabajadores($id = null) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return [];
        try {
            if ($id !== null) {
                $stmt = $pdo->prepare("SELECT * FROM trabajadores WHERE id = :id");
                $stmt->execute([':id' => $id]);
                return $stmt->fetch() ?: false;
            } else {
                $stmt = $pdo->prepare("SELECT * FROM trabajadores ORDER BY nombre ASC");
                $stmt->execute();
                return $stmt->fetchAll() ?: [];
            }
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Crea un nuevo trabajador.
     *
     * @param array $datos Datos del trabajador.
     * @return bool
     */
    public static function mdlCrearTrabajador($datos) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;
        try {
            $stmt = $pdo->prepare("INSERT INTO trabajadores (nombre, email, telefono, cargo, salario, fecha_ingreso) 
                                   VALUES (:nombre, :email, :telefono, :cargo, :salario, :fecha_ingreso)");
            return $stmt->execute($datos);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Actualiza un trabajador existente.
     *
     * @param array $datos Datos del trabajador incluyendo 'id'.
     * @return bool
     */
    public static function mdlEditarTrabajador($datos) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;
        try {
            $stmt = $pdo->prepare("UPDATE trabajadores SET nombre = :nombre, email = :email, telefono = :telefono, 
                                   cargo = :cargo, salario = :salario, fecha_ingreso = :fecha_ingreso 
                                   WHERE id = :id");
            return $stmt->execute($datos);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Elimina un trabajador por su ID.
     *
     * @param int $id ID del trabajador.
     * @return bool
     */
    public static function mdlEliminarTrabajador($id) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;
        try {
            $stmt = $pdo->prepare("DELETE FROM trabajadores WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
