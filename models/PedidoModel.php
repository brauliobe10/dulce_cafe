<?php

require_once __DIR__ . '/../config/conexion.php';

/**
 * Clase PedidoModel
 *
 * Gestiona todas las operaciones de base de datos relacionadas con los pedidos
 * y sus detalles. Utiliza consultas preparadas PDO para evitar SQL Injection.
 */
class PedidoModel {

    /**
     * Obtiene todos los pedidos con datos del cliente (LEFT JOIN con usuarios).
     * El uso de LEFT JOIN garantiza que los pedidos de los invitados temporales
     * sigan siendo completamente visibles en el panel administrativo de Dulce Café.
     *
     * @return array|false Array de pedidos o false en caso de error.
     */
    public static function mdlMostrarPedidos() {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;

        try {
            // COALESCE rescata datos alternativos en caso de que el pedido sea de un Invitado y no exista relación de ID
            $stmt = $pdo->prepare(
                "SELECT p.id, p.total, p.fecha, p.estado, p.usuario_id,
                        COALESCE(u.nombre, p.invitado_nombre, 'Invitado Express') AS cliente_nombre, 
                        COALESCE(u.email, p.invitado_celular, 'Sin Correo') AS cliente_email
                 FROM pedidos p
                 LEFT JOIN usuarios u ON p.usuario_id = u.id
                 ORDER BY p.fecha DESC"
            );
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Registra un pedido y sus detalles en una transacción atómica.
     * Descuenta el stock de cada producto involucrado.
     * Soporta nativamente pedidos de Clientes e Invitados.
     *
     * @param array $datosPedido  Array con 'usuario_id', 'total' y datos temporales si es invitado.
     * @param array $detalles     Array de productos: [['id', 'cantidad', 'precio'], ...].
     * @return bool True si todo se procesó correctamente.
     */
    public static function mdlIngresarPedido($datosPedido, $detalles) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;

        try {
            $pdo->beginTransaction();

            // Verificar si el usuario es un Invitado para mapear de forma segura su ID de relación
            $usuarioId = $datosPedido['usuario_id'];
            $invitadoNombre = null;
            $invitadoCelular = null;
            $invitadoDni = null;

            // Si es un ID temporal de invitado, limpiamos la llave foránea de la base de datos
            if (strpos($usuarioId, 'INV-') === 0) {
                $usuarioId = null; // Guardar null en usuario_id para evitar conflictos de Foreign Key
                $invitadoNombre = $datosPedido['nombre'] ?? 'Invitado Express';
                $invitadoCelular = $datosPedido['celular'] ?? null;
                $invitadoDni = $datosPedido['dni'] ?? null;
            }

            // Insertar el pedido principal incluyendo las columnas adicionales de invitado (si existen en tu tabla de pedidos)
            $stmtPedido = $pdo->prepare(
                "INSERT INTO pedidos (usuario_id, total, invitado_nombre, invitado_celular, invitado_dni) 
                 VALUES (:usuario_id, :total, :invitado_nombre, :invitado_celular, :invitado_dni)"
            );
            
            $stmtPedido->execute([
                ':usuario_id' => $usuarioId,
                ':total' => $datosPedido['total'],
                ':invitado_nombre' => $invitadoNombre,
                ':invitado_celular' => $invitadoCelular,
                ':invitado_dni' => $invitadoDni
            ]);
            
            $pedidoId = $pdo->lastInsertId();

            // Insertar cada detalle y descontar stock
            $stmtDetalle = $pdo->prepare(
                "INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario)
                 VALUES (:pedido_id, :producto_id, :cantidad, :precio_unitario)"
            );
            $stmtStock = $pdo->prepare(
                "UPDATE productos SET stock = stock - :cantidad WHERE id = :id AND stock >= :cantidad"
            );

            foreach ($detalles as $item) {
                // Verificar stock disponible y actualizarlo de forma atómica
                $stmtStock->execute([
                    ':cantidad' => $item['cantidad'],
                    ':id' => $item['id']
                ]);

                if ($stmtStock->rowCount() === 0) {
                    // Stock insuficiente para este producto en específico
                    $pdo->rollBack();
                    return false;
                }

                $stmtDetalle->execute([
                    ':pedido_id' => $pedidoId,
                    ':producto_id' => $item['id'],
                    ':cantidad' => $item['cantidad'],
                    ':precio_unitario' => $item['precio']
                ]);
            }

            $pdo->commit();
            return true;

        } catch (PDOException $e) {
            $pdo->rollBack();
            return false;
        }
    }

    /**
     * Acepta un pedido cambiando su estado a 'completado'.
     *
     * @param int $id ID del pedido.
     * @return bool
     */
    public static function mdlAceptarPedido($id) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;

        try {
            $stmt = $pdo->prepare("UPDATE pedidos SET estado = 'completado' WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Elimina un pedido por su ID.
     *
     * @param int $id ID del pedido.
     * @return bool
     */
    public static function mdlEliminarPedido($id) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;

        try {
            $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Actualiza el estado y el total de un pedido.
     *
     * @param int $id ID del pedido.
     * @param string $estado Nuevo estado ('pendiente', 'completado', 'cancelado').
     * @param float $total Nuevo total del pedido.
     * @return bool
     */
    public static function mdlEditarPedido($id, $estado, $total) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;

        try {
            $stmt = $pdo->prepare("UPDATE pedidos SET estado = :estado, total = :total WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':total', $total);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtiene los datos generales de un pedido por su ID (para la boleta o reporte).
     *
     * @param int $id ID del pedido.
     * @return array|false
     */
    public static function mdlMostrarPedidoPorId($id) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;

        try {
            $stmt = $pdo->prepare(
                "SELECT p.id, p.total, p.fecha, p.estado,
                        COALESCE(u.nombre, p.invitado_nombre, 'Invitado Express') AS cliente_nombre, 
                        COALESCE(u.email, p.invitado_celular, 'Sin Correo') AS cliente_email
                 FROM pedidos p
                 LEFT JOIN usuarios u ON p.usuario_id = u.id
                 WHERE p.id = :id
                 LIMIT 1"
            );
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtiene los detalles/items de un pedido por su ID (para la boleta).
     *
     * @param int $pedidoId ID del pedido.
     * @return array|false
     */
    public static function mdlMostrarDetallesPedido($pedidoId) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;

        try {
            $stmt = $pdo->prepare(
                "SELECT dp.cantidad, dp.precio_unitario, pr.nombre, pr.categoria, pr.imagen
                 FROM detalle_pedidos dp
                 INNER JOIN productos pr ON dp.producto_id = pr.id
                 WHERE dp.pedido_id = :pedido_id"
            );
            $stmt->execute([':pedido_id' => $pedidoId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }
}