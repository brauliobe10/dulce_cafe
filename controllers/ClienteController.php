<?php

require_once __DIR__ . '/../models/ClienteModel.php';

class ClienteController {

    public static function procesarAccionGet($id) {
        if (ClienteModel::mdlEliminarCliente($id)) {
            $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Cliente eliminado con éxito."];
        } else {
            $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al eliminar el cliente."];
        }
        header("Location: index.php?ruta=admin&tab=clientes");
        exit();
    }

    
    //Procesa la acción POST de crear/editar cliente.
     
    public static function procesarAccionPost() {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : null; //CONTRA ATAQUE DE HACKERS: Validar y sanear el ID recibido del formulario
        $nombre = trim($_POST["nombre"]);
        $email = trim($_POST["email"]);
        $telefono = trim($_POST["telefono"]);
        $direccion = trim($_POST["direccion"]);
        $puntos = intval($_POST["puntos"]);

        $datos = [
            "nombre" => $nombre,
            "email" => $email,
            "telefono" => $telefono,
            "direccion" => $direccion,
            "puntos" => $puntos
        ];

        if ($id) {
            $datos["id"] = $id;
            if (ClienteModel::mdlEditarCliente($datos)) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Cliente actualizado correctamente."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al actualizar el cliente."];
            }
        } else {
            if (ClienteModel::mdlCrearCliente($datos)) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Cliente registrado correctamente."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al registrar el cliente (el email ya existe)."];
            }
        }
        header("Location: index.php?ruta=admin&tab=clientes");
        exit();
    }
}
