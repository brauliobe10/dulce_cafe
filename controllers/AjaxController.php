<?php

// Inicializar la sesión para comprobar si el usuario está autenticado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusiones obligatorias de la arquitectura MVC
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

class AjaxController {
    //Procesa la compra asíncrona enviada por el cliente.

    public function ctrProcesarCompra() {
        // Establecer cabecera de respuesta en formato JSON
        header('Content-Type: application/json; charset=utf-8');

        // 1. Validar autenticación
        if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] !== "ok") {
            echo json_encode([
                "status" => "error",
                "message" => "Debe iniciar sesión para poder realizar un pedido."
            ]);
            exit();
        }

        // 2. Obtener el flujo de entrada crudo de la petición AJAX (JSON payload)
        $jsonInput = file_get_contents("php://input");
        $datosCarrito = json_decode($jsonInput, true);

        if (empty($datosCarrito) || !is_array($datosCarrito)) {
            echo json_encode([
                "status" => "error",
                "message" => "El carrito de compras está vacío o es inválido."
            ]);
            exit();
        }

        // 3. Procesar montos y validar estructura
        $usuarioId = $_SESSION["id"];
        $total = 0;
        $detalles = [];

        foreach ($datosCarrito as $producto) {
            $id = filter_var($producto["id"], FILTER_VALIDATE_INT);
            $cantidad = filter_var($producto["cantidad"], FILTER_VALIDATE_INT);
            $precio = filter_var($producto["precio"], FILTER_VALIDATE_FLOAT);

            if ($id === false || $cantidad === false || $precio === false || $cantidad <= 0 || $precio <= 0) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Datos de productos inconsistentes en el carrito."
                ]);
                exit();
            }

            $total += $precio * $cantidad;
            
            $detalles[] = [
                "id" => $id,
                "cantidad" => $cantidad,
                "precio" => $precio
            ];
        }

        $datosPedido = [
            "usuario_id" => $usuarioId,
            "total" => $total
        ];

        // 4. Registrar pedido en la base de datos de manera atómica transaccional (restando stock)
        $respuesta = UsuarioModel::mdlIngresarPedido($datosPedido, $detalles);

        if ($respuesta) {
            echo json_encode([
                "status" => "success",
                "message" => "¡Pedido registrado con éxito! El stock ha sido actualizado."
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al registrar el pedido. Es posible que alguno de los productos seleccionados no cuente con suficiente stock disponible."
            ]);
        }
        exit();
    }
}

// Inicializar el controlador y procesar la petición si es un llamado directo AJAX
$ajax = new AjaxController();
$ajax->ctrProcesarCompra();
