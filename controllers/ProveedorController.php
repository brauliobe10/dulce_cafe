<?php

require_once __DIR__ . '/../models/ProveedorModel.php';

class ProveedorController {

    public static function procesarAccionGet($id) {
        if (ProveedorModel::mdlEliminarProveedor($id)) {
            $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Proveedor eliminado con éxito."];
        } else {
            $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al eliminar el proveedor."];
        }
        header("Location: index.php?ruta=admin&tab=proveedores");
        exit();
    }

    
    //Procesa la acción POST de crear/editar proveedor.

    public static function procesarAccionPost() {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : null;
        $nombre = trim($_POST["nombre"]);
        $contacto = trim($_POST["contacto"]);
        $email = trim($_POST["email"]);
        $telefono = trim($_POST["telefono"]);
        $direccion = trim($_POST["direccion"]);
        $producto_principal = trim($_POST["producto_principal"]);

        $datos = [
            "nombre" => $nombre,
            "contacto" => $contacto,
            "email" => $email,
            "telefono" => $telefono,
            "direccion" => $direccion,
            "producto_principal" => $producto_principal
        ];

        if ($id) {
            $datos["id"] = $id;
            if (ProveedorModel::mdlEditarProveedor($datos)) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Proveedor actualizado correctamente."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al actualizar el proveedor."];
            }
        } else {
            if (ProveedorModel::mdlCrearProveedor($datos)) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Proveedor registrado correctamente."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al registrar el proveedor."];
            }
        }
        header("Location: index.php?ruta=admin&tab=proveedores");
        exit();
    }
}
