<?php

require_once __DIR__ . '/../models/ProductoModel.php';

class InventarioController {

    
    // la acción POST de actualizar stock de un producto.
     
    public static function procesarAccionPost() {
        $id = intval($_POST["id"]);
        $stock = intval($_POST["stock"]);

        $pdo = Conexion::conectar();
        if ($pdo === null) {
            $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error de conexión a la base de datos."];
            header("Location: index.php?ruta=admin&tab=inventario");
            exit();
        }

        try {
            $stmt = $pdo->prepare("UPDATE productos SET stock = :stock WHERE id = :id");
            $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Stock del producto #$id actualizado correctamente."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al actualizar el stock."];
            }
        } catch (PDOException $e) {
            $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al actualizar el stock."];
        }

        header("Location: index.php?ruta=admin&tab=inventario");
        exit();
    }
}
