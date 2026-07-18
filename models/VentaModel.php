<?php

require_once __DIR__ . '/../config/conexion.php';

/**
 * Clase VentaModel
 *
 * Gestiona las consultas de reportes y estadísticas de ventas.
 * Utiliza consultas preparadas PDO para evitar SQL Injection.
 */
class VentaModel {

    /**
     * Obtiene la suma de las ventas de hoy (pedidos completados).
     *
     * @return float
     */
    public static function mdlObtenerVentasDia() {
        $pdo = Conexion::conectar();
        if ($pdo === null) return 0;
        try {
            $stmt = $pdo->prepare("SELECT SUM(total) as total FROM pedidos WHERE estado = 'completado' AND DATE(fecha) = CURDATE()");
            $stmt->execute();
            $res = $stmt->fetch();
            return floatval($res['total'] ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Obtiene la suma de las ventas de la última semana (últimos 7 días, pedidos completados).
     *
     * @return float
     */
    public static function mdlObtenerVentasSemana() {
        $pdo = Conexion::conectar();
        if ($pdo === null) return 0;
        try {
            $stmt = $pdo->prepare("SELECT SUM(total) as total FROM pedidos WHERE estado = 'completado' AND fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stmt->execute();
            $res = $stmt->fetch();
            return floatval($res['total'] ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Obtiene la suma de las ventas del mes actual (pedidos completados).
     *
     * @return float
     */
    public static function mdlObtenerVentasMes() {
        $pdo = Conexion::conectar();
        if ($pdo === null) return 0;
        try {
            $stmt = $pdo->prepare("SELECT SUM(total) as total FROM pedidos WHERE estado = 'completado' AND MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE())");
            $stmt->execute();
            $res = $stmt->fetch();
            return floatval($res['total'] ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Obtiene las ventas diarias de los últimos 7 días.
     *
     * @return array
     */
    public static function mdlObtenerVentasDiariasUltimaSemana() {
        $pdo = Conexion::conectar();
        if ($pdo === null) return [];
        try {
            $stmt = $pdo->prepare("SELECT DATE(fecha) as fecha_dia, SUM(total) as total 
                                   FROM pedidos 
                                   WHERE estado = 'completado' AND fecha >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                                   GROUP BY DATE(fecha)
                                   ORDER BY DATE(fecha) ASC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtiene el total de ventas por categoría de producto.
     *
     * @return array
     */
    public static function mdlObtenerVentasPorCategoria() {
        $pdo = Conexion::conectar();
        if ($pdo === null) return [];
        try {
            $stmt = $pdo->prepare("SELECT pr.categoria, SUM(dp.cantidad * dp.precio_unitario) as total 
                                   FROM detalle_pedidos dp
                                   INNER JOIN productos pr ON dp.producto_id = pr.id
                                   INNER JOIN pedidos p ON dp.pedido_id = p.id
                                   WHERE p.estado = 'completado'
                                   GROUP BY pr.categoria");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }
}
