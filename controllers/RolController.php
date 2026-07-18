<?php

require_once __DIR__ . '/../models/RolModel.php';

class RolController {

    public static function procesarAccionGet($id) {
        if (RolModel::mdlEliminarRol($id)) {
            $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Rol eliminado con éxito."];
        } else {
            $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al eliminar el rol."];
        }
        header("Location: index.php?ruta=admin&tab=roles");
        exit();
    }

    
    //Procesa la acción POST de crear/editar rol.
     
    public static function procesarAccionPost() {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : null;
        $nombre = trim($_POST["nombre"]);
        $descripcion = trim($_POST["descripcion"]);

        $datos = [
            "nombre" => $nombre,
            "descripcion" => $descripcion
        ];

        if ($id) {
            $datos["id"] = $id;
            if (RolModel::mdlEditarRol($datos)) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Rol actualizado correctamente."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al actualizar el rol."];
            }
        } else {
            if (RolModel::mdlCrearRol($datos)) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Rol creado correctamente."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al crear el rol."];
            }
        }
        header("Location: index.php?ruta=admin&tab=roles");
        exit();
    }
}
