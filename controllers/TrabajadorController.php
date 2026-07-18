<?php

require_once __DIR__ . '/../models/TrabajadorModel.php';

class TrabajadorController {

    
    //Procesa la acción GET de eliminar trabajador.
     
    public static function procesarAccionGet($id) {
        if (TrabajadorModel::mdlEliminarTrabajador($id)) {
            $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Trabajador eliminado con éxito."];
        } else {
            $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al eliminar el trabajador."];
        }
        header("Location: index.php?ruta=admin&tab=trabajadores");
        exit();
    }

    
    //Procesa la acción POST de crear/editar trabajador.
    
    public static function procesarAccionPost() {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : null;
        $nombre = trim($_POST["nombre"]);
        $email = trim($_POST["email"]);
        $telefono = trim($_POST["telefono"]);
        $cargo = trim($_POST["cargo"]);
        $salario = floatval($_POST["salario"]);
        $fecha_ingreso = $_POST["fecha_ingreso"];

        $datos = [
            "nombre" => $nombre,
            "email" => $email,
            "telefono" => $telefono,
            "cargo" => $cargo,
            "salario" => $salario,
            "fecha_ingreso" => $fecha_ingreso
        ];

        if ($id) {
            $datos["id"] = $id;
            if (TrabajadorModel::mdlEditarTrabajador($datos)) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Trabajador actualizado correctamente."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al actualizar el trabajador."];
            }
        } else {
            if (TrabajadorModel::mdlCrearTrabajador($datos)) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Trabajador registrado correctamente."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al registrar el trabajador."];
            }
        }
        header("Location: index.php?ruta=admin&tab=trabajadores");
        exit();
    }
}
