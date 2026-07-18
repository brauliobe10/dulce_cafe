<?php

require_once __DIR__ . '/../config/conexion.php';

/**
 * Clase UsuarioModel
 *
 * Provee métodos para registrar usuarios, validar credenciales,
 * gestionar pedidos y actualizar información del usuario.
 * Utiliza consultas preparadas PDO para evitar SQL Injection.
 */
class UsuarioModel {

    /**
     * Busca un usuario en la base de datos por un campo específico.
     *
     * @param string $tabla  Nombre de la tabla.
     * @param string $campo  Columna por la que se filtra (ej: 'email', 'id').
     * @param mixed  $valor  Valor a buscar.
     * @return array|false   Array asociativo del usuario o false si no existe.
     */
    public static function mdlMostrarUsuario($tabla, $campo, $valor) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;

        try {
            $stmt = $pdo->prepare("SELECT * FROM `$tabla` WHERE `$campo` = :valor LIMIT 1");
            $stmt->execute([':valor' => $valor]);
            $result = $stmt->fetch();
            return $result ?: false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Registra un nuevo usuario en la base de datos.
     *
     * @param string $tabla Nombre de la tabla.
     * @param array  $datos Array con keys: nombre, email, password, rol.
     * @return bool True si se insertó correctamente, false en caso contrario.
     */
    public static function mdlRegistroUsuario($tabla, $datos) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO `$tabla` (nombre, email, password, rol)
                 VALUES (:nombre, :email, :password, :rol)"
            );
            $stmt->bindParam(':nombre',   $datos['nombre']);
            $stmt->bindParam(':email',    $datos['email']);
            $stmt->bindParam(':password', $datos['password']);
            $stmt->bindParam(':rol',      $datos['rol']);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Actualiza un campo específico de un registro en la tabla de usuarios.
     *
     * @param string $tabla       Nombre de la tabla.
     * @param string $campoSet    Columna a actualizar.
     * @param mixed  $valorSet    Nuevo valor.
     * @param string $campoWhere  Columna de condición (ej: 'id').
     * @param mixed  $valorWhere  Valor de la condición.
     * @return bool
     */
    public static function mdlActualizarUsuario($tabla, $campoSet, $valorSet, $campoWhere, $valorWhere) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;

        try {
            $stmt = $pdo->prepare(
                "UPDATE `$tabla` SET `$campoSet` = :valorSet WHERE `$campoWhere` = :valorWhere"
            );
            $stmt->bindParam(':valorSet',   $valorSet);
            $stmt->bindParam(':valorWhere', $valorWhere);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtiene todos los pedidos con datos del cliente (JOIN con usuarios).
     *
     * @return array|false Array de pedidos o false en caso de error.
     */
    public static function mdlMostrarPedidos() {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;

        try {
            $stmt = $pdo->prepare(
                "SELECT p.id, p.total, p.fecha, p.estado,
                        u.nombre AS cliente_nombre, u.email AS cliente_email
                 FROM pedidos p
                 INNER JOIN usuarios u ON p.usuario_id = u.id
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
     *
     * @param array $datosPedido  Array con 'usuario_id' y 'total'.
     * @param array $detalles     Array de productos: [['id', 'cantidad', 'precio'], ...].
     * @return bool True si todo se procesó correctamente.
     */
    public static function mdlIngresarPedido($datosPedido, $detalles) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;

        try {
            $pdo->beginTransaction();

            // Insertar el pedido principal
            $stmtPedido = $pdo->prepare(
                "INSERT INTO pedidos (usuario_id, total) VALUES (:usuario_id, :total)"
            );
            $stmtPedido->execute([
                ':usuario_id' => $datosPedido['usuario_id'],
                ':total' => $datosPedido['total']
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
                // Verificar stock disponible
                $stmtStock->execute([
                    ':cantidad' => $item['cantidad'],
                    ':id' => $item['id']
                ]);

                if ($stmtStock->rowCount() === 0) {
                    // Stock insuficiente para este producto
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
     * Obtiene los datos del perfil de un usuario por su ID.
     *
     * @param int $id ID del usuario.
     * @return array|false
     */
    public static function obtenerPerfil($id) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;

        $stmt = $pdo->prepare("SELECT id, nombre, email, creado_en FROM usuarios WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
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
     * Obtiene los datos generales de un pedido por su ID (para la boleta).
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
                        u.nombre AS cliente_nombre, u.email AS cliente_email
                 FROM pedidos p
                 INNER JOIN usuarios u ON p.usuario_id = u.id
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

    /**
     * Obtiene la suma de las ventas de hoy (pedidos completados).
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

    /**
     * Gestión de Proveedores
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

    /**
     * Gestión de Trabajadores
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

    /**
     * Gestión de Clientes
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

    /**
     * Gestión de Roles
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

    /**
     * Gestión Integral de Usuarios
     */
    public static function mdlMostrarUsuariosAll() {
        $pdo = Conexion::conectar();
        if ($pdo === null) return [];
        try {
            $stmt = $pdo->prepare("SELECT * FROM usuarios ORDER BY creado_en DESC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    public static function mdlCrearUsuario($datos) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;
        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (:nombre, :email, :password, :rol)");
            return $stmt->execute($datos);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function mdlEditarUsuario($datos) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;
        try {
            if (!empty($datos['password'])) {
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = :nombre, email = :email, password = :password, rol = :rol WHERE id = :id");
                return $stmt->execute($datos);
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = :nombre, email = :email, rol = :rol WHERE id = :id");
                unset($datos['password']);
                return $stmt->execute($datos);
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function mdlEliminarUsuario($id) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;
        try {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Gestión de Inventario
     */
    public static function mdlActualizarStockProducto($id, $stock) {
        $pdo = Conexion::conectar();
        if ($pdo === null) return false;
        try {
            $stmt = $pdo->prepare("UPDATE productos SET stock = :stock WHERE id = :id");
            $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
