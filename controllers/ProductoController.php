<?php
require_once __DIR__ . '/../models/ProductoModel.php';

class ProductoController {
    // Mostrar catálogo o detalle
    public function listar() {
        $productos = ProductoModel::mdlMostrarProductos('productos');

        if ($productos === false || !is_array($productos) || count($productos) === 0) {
            $productos = $this->productosMock();
        }

        include __DIR__ . '/../views/catalogo.php';
    }

    // Mostrar formulario de creación/edición
    public function formulario($id = null) {
        // Protección de acceso: Solo el administrador puede ver este formulario
        if (!isset($_SESSION["iniciarSesion"]) || !in_array($_SESSION["rol"], ["admin", "trabajador"])) {
            $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Acceso denegado. Permisos insuficientes."];
            header("Location: index.php?ruta=login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action']) && $_POST['action'] === 'eliminar') {
                $this->eliminar($_POST['id']);
                exit();
            } else {
                $this->guardar();
                exit();
            }
        }

        $producto = null;
        if ($id !== null) {
            $producto = ProductoModel::mdlMostrarProductos('productos', $id);
        }
        include __DIR__ . '/../views/producto_form.php';
    }

    
    //Datos de menú de respaldo cuando no hay productos en la base de datos.

     
    private function productosMock() {
        return [
            [
                'id' => 1,
                'nombre' => 'Café Espresso Doble',
                'descripcion' => 'Café concentrado de cuerpo robusto, extraído con precisión para resaltar su aroma y crema densa de sabor pronunciado.',
                'precio' => 2.50,
                'imagen' => 'https://a.com.gt/log/imgs/2014/01/espresso-doble-y-leche-condensada.jpg',
                'categoria' => 'Bebidas',
                'stock' => 20,
            ],
            [
                'id' => 2,
                'nombre' => 'Latte Macchiato Vainilla',
                'descripcion' => 'Delicioso espresso combinado con leche emulsionada y un toque suave de esencia de vainilla natural.',
                'precio' => 3.80,
                'imagen' => 'https://tse4.mm.bing.net/th/id/OIP.ghyzu15MvcdjIqDX_3g_sAHaLG?pid=Api&P=0&h=180',
                'categoria' => 'Bebidas',
                'stock' => 15,
            ],
            [
                'id' => 3,
                'nombre' => 'Capuccino Canela y Cacao',
                'descripcion' => 'Equilibrio perfecto entre espresso, leche vaporizada y abundante espuma espolvoreada con canela y cacao en polvo.',
                'precio' => 3.50,
                'imagen' => 'https://images.unsplash.com/photo-1534778101976-62847782c213?q=80&w=600',
                'categoria' => 'Bebidas',
                'stock' => 18,
            ],
            [
                'id' => 4,
                'nombre' => 'Tarta Húmeda de Tres Leches',
                'descripcion' => 'Esponjoso bizcocho tradicional embebido en una mezcla dulce de tres leches, decorado con merengue italiano.',
                'precio' => 4.50,
                'imagen' => 'https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?q=80&w=400',
                'categoria' => 'Postres',
                'stock' => 10,
            ],
            [
                'id' => 5,
                'nombre' => 'Muffin Rústico de Arándanos',
                'descripcion' => 'Muffin de vainilla tierno y esponjoso horneado con abundantes arándanos azules frescos enteros y un toque crujiente.',
                'precio' => 2.20,
                'imagen' => 'https://images.unsplash.com/photo-1607958996333-41aef7caefaa?q=80&w=400',
                'categoria' => 'Postres',
                'stock' => 25,
            ],
            [
                'id' => 6,
                'nombre' => 'Croissant de Mantequilla Francés',
                'descripcion' => 'Clásica pieza de repostería con hojaldrado crujiente y miga suave con aroma a mantequilla pura horneada.',
                'precio' => 1.80,
                'imagen' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?q=80&w=400',
                'categoria' => 'Postres',
                'stock' => 30,
            ],
            [
                'id' => 7,
                'nombre' => 'Té Chai Latte',
                'descripcion' => 'Mezcla de té chai especiado con leche cremosa y un toque de canela para un sabor equilibrado.',
                'precio' => 3.20,
                'imagen' => 'https://www.splenda.com/wp-content/themes/bistrotheme/assets/recipe-images/vanilla-chai-latte.jpg',
                'categoria' => 'Bebidas',
                'stock' => 20,
            ],
            [
                'id' => 8,
                'nombre' => 'Brownie de Chocolate',
                'descripcion' => 'Brownie húmedo y fundente con trozos de chocolate oscuro y una textura densa y deliciosa.',
                'precio' => 2.90,
                'imagen' => 'https://tse3.mm.bing.net/th/id/OIP.WEix7zunL0mdVl3DIT527QHaEK?rs=1&pid=ImgDetMain&o=7&rm=3',
                'categoria' => 'Postres',
                'stock' => 12,
            ],
            [
                'id' => 9,
                'nombre' => 'Scone de Vainilla y Almendra',
                'descripcion' => 'Scone artesanal con suaves notas de vainilla y almendras tostadas, perfecto para acompañar tu café.',
                'precio' => 2.70,
                'imagen' => 'https://www.missblasco.com/wp-content/uploads/2020/10/SconesVainilla_portada.jpg',
                'categoria' => 'Postres',
                'stock' => 16,
            ],
        ];
    }

    // Procesar guardado (create/update)
    public function guardar() {
        // Protección de acceso
        if (!isset($_SESSION["iniciarSesion"]) || !in_array($_SESSION["rol"], ["admin", "trabajador"])) {
            header("Location: index.php?ruta=login");
            exit();
        }

        $data = $_POST;
        $conexion = Conexion::conectar();
        
        // Convertir precio de Soles (PEN) a USD para guardar en BD
        $precioUSD = floatval($data['precio']) / 3.80;
        $stock = intval($data['stock']);
        
        if (isset($data['id']) && !empty($data['id'])) {
            $stmt = $conexion->prepare('UPDATE productos SET nombre = :nombre, precio = :precio, descripcion = :descripcion, imagen = :imagen, categoria = :categoria, stock = :stock WHERE id = :id');
            $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
        } else {
            $stmt = $conexion->prepare('INSERT INTO productos (nombre, precio, descripcion, imagen, categoria, stock) VALUES (:nombre, :precio, :descripcion, :imagen, :categoria, :stock)');
        }
        
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':precio', $precioUSD);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':imagen', $data['imagen']);
        $stmt->bindParam(':categoria', $data['categoria']);
        $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Producto guardado correctamente."];
        } else {
            $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al guardar el producto."];
        }
        
        header('Location: index.php?ruta=catalogo');
    }

    // Eliminar producto
    public function eliminar($id) {
        // Protección de acceso
        if (!isset($_SESSION["iniciarSesion"]) || !in_array($_SESSION["rol"], ["admin", "trabajador"])) {
            header("Location: index.php?ruta=login");
            exit();
        }

        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare('DELETE FROM productos WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Producto eliminado correctamente."];
        } else {
            $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al eliminar el producto."];
        }
        
        header('Location: index.php?ruta=catalogo');
    }
}
?>
