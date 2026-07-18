<?php

require_once __DIR__ . '/../models/PedidoModel.php';

class PedidoController {

    
    //Procesa la creación de un nuevo pedido (Clientes registrados e Invitados)
     
    public static function ctrCrearPedido() {
        if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] !== "ok") {
            $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Debe iniciar sesión o acceder como invitado para finalizar un pedido."];
            header("Location: index.php?ruta=login");
            exit();
        }

        if (isset($_POST["productos_carrito"]) && isset($_POST["total_pedido"])) {
            
            // 1. Mitigación de CSRF
            if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error de validación de seguridad (CSRF). Intente nuevamente."];
                header("Location: index.php?ruta=catalogo");
                exit();
            }

            // 2. Extraer y sanitizar datos según el Rol de la sesión
            $totalPEN = floatval($_POST["total_pedido"]);
            $totalUSD = $totalPEN / 3.80; // Tipo de cambio establecido en el controlador
            $productos = $_POST["productos_carrito"]; // Cadena JSON o array de items

            if ($_SESSION["rol"] === "invitado") {
                // Datos dinámicos desde la sesión de invitado (Acceso Express)
                $usuarioId    = $_SESSION["id"]; // Temporal "INV-DNI"
                $nombreCliente = $_SESSION["nombre"];
                $dniCliente    = $_SESSION["dni"];
                $celularCliente= $_SESSION["celular"];
                $tipoCliente   = "invitado";
            } else {
                // Datos de cliente registrado
                $usuarioId    = $_SESSION["id"];
                $nombreCliente = $_SESSION["nombre"];
                $dniCliente    = $_SESSION["dni"] ?? null; // Si cuenta con DNI guardado
                $celularCliente= $_SESSION["celular"] ?? null;
                $tipoCliente   = "registrado";
            }

            // 3. Empaquetar datos para el Modelo
            $datosPedido = [
                "usuario_id" => htmlspecialchars($usuarioId),
                "nombre" => htmlspecialchars($nombreCliente),
                "dni" => htmlspecialchars($dniCliente),
                "celular" => htmlspecialchars($celularCliente),
                "total_usd" => $totalUSD,
                "productos" => $productos,
                "tipo_cliente" => $tipoCliente,
                "fecha" => date("Y-m-d H:i:s")
            ];

            // 4. Enviar al modelo para registrar la compra
            $respuesta = PedidoModel::mdlIngresarPedido("pedidos", $datosPedido);

            if ($respuesta) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "¡Pedido realizado con éxito! Su pedido está en proceso de preparación."];
                header("Location: index.php?ruta=catalogo");
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Hubo un problema al registrar su pedido. Por favor, inténtelo de nuevo."];
                header("Location: index.php?ruta=catalogo");
            }
            exit();
        }
    }

    public static function procesarAccionGet($action, $id) {
        if ($action === "aceptar") {
            if (PedidoModel::mdlAceptarPedido($id)) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Pedido #$id completado con éxito."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al aceptar el pedido #$id."];
            }
            header("Location: index.php?ruta=admin&tab=pedidos");
            exit();
        }

        if ($action === "eliminar_pedido") {
            if (PedidoModel::mdlEliminarPedido($id)) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Pedido #$id eliminado correctamente."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al eliminar el pedido #$id."];
            }
            header("Location: index.php?ruta=admin&tab=pedidos");
            exit();
        }
    }

    
    //Procesa la acción POST de editar pedido.
     
    public static function procesarAccionPost() {
        $id = intval($_POST["id"]);
        $estado = $_POST["estado"];
        $totalPEN = floatval($_POST["total"]);
        $totalUSD = $totalPEN / 3.80;

        if (PedidoModel::mdlEditarPedido($id, $estado, $totalUSD)) {
            $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Pedido #$id actualizado con éxito."];
        } else {
            $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al intentar actualizar el pedido #$id."];
        }
        header("Location: index.php?ruta=admin&tab=pedidos");
        exit();
    }
}