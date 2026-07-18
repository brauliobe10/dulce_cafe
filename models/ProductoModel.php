<?php

require_once __DIR__ . '/../config/conexion.php';

class ProductoModel {
    /**
     * Obtiene todos los productos (o un producto específico por ID) desde la base de datos.
     * 
     * @param string $tabla Nombre de la tabla de productos en la base de datos.
     * @param int|null $id Opcional. ID de un producto en específico.
     * @return array|bool Retorna un arreglo asociativo con los productos o false en caso de error.
     */
    public static function mdlMostrarProductos($tabla, $id = null) {
        $conexion = Conexion::conectar();
        
        // Si no se puede conectar a la base de datos, retornamos false para indicar
        // que se requiere activar el fallback de datos mock.
        if ($conexion === null) {
            return false;
        }

        try {
            if ($id !== null) {
                // Sentencia preparada para evitar SQL Injection al buscar por ID
                $stmt = $conexion->prepare("SELECT * FROM $tabla WHERE id = :id");
                $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch();
            } else {
                // Sentencia para obtener todos los productos
                $stmt = $conexion->prepare("SELECT * FROM $tabla ORDER BY creado_en DESC");
                $stmt->execute();
                return $stmt->fetchAll();
            }
        } catch (PDOException $e) {
            // En caso de que la tabla no exista o haya otro error de base de datos
            return false;
        } finally {
            $stmt = null;
        }
    }
}
