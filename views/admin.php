<?php
// Protección de acceso: Solo el administrador o trabajador pueden ver este panel
if (!isset($_SESSION["iniciarSesion"]) || !in_array($_SESSION["rol"], ["admin", "trabajador"])) {
    $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Acceso denegado. Debes iniciar sesión."];
    header("Location: index.php?ruta=login");
    exit();
}

require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/ProductoModel.php';

$rol = $_SESSION["rol"];

// Pestañas permitidas según el rol
$pestanasPermitidas = [
    'admin' => ['dashboard', 'ventas', 'pedidos', 'productos', 'inventario', 'usuarios', 'clientes', 'trabajadores', 'roles', 'proveedores'],
    'trabajador' => ['ventas', 'pedidos', 'productos']
];

// Determinar la pestaña activa
$tab = $_GET['tab'] ?? ($rol === 'admin' ? 'dashboard' : 'ventas');
if (!in_array($tab, $pestanasPermitidas[$rol])) {
    // Si intenta acceder a una pestaña no permitida, forzar la primera permitida
    $tab = $pestanasPermitidas[$rol][0];
}

// ==========================================
// --- PROCESAMIENTO DE ACCIONES DE GET ---
// ==========================================
if (isset($_GET["action"]) && isset($_GET["id"])) {
    $action = $_GET["action"];
    $id = intval($_GET["id"]);

    // Aceptar Pedido
    if ($action === "aceptar") {
        if (UsuarioModel::mdlAceptarPedido($id)) {
            $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Pedido #$id completado con éxito."];
        } else {
            $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al aceptar el pedido #$id."];
        }
        header("Location: index.php?ruta=admin&tab=pedidos");
        exit();
    }

    // Cancelar/Eliminar Pedido
    if ($action === "eliminar_pedido") {
        if (UsuarioModel::mdlEliminarPedido($id)) {
            $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Pedido #$id eliminado correctamente."];
        } else {
            $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al eliminar el pedido #$id."];
        }
        header("Location: index.php?ruta=admin&tab=pedidos");
        exit();
    }

    // Eliminar Usuario
    if ($action === "eliminar_usuario" && $rol === "admin") {
        if (UsuarioModel::mdlEliminarUsuario($id)) {
            $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Usuario eliminado con éxito."];
        } else {
            $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al eliminar el usuario."];
        }
        header("Location: index.php?ruta=admin&tab=usuarios");
        exit();
    }

    // Eliminar Cliente
    if ($action === "eliminar_cliente" && $rol === "admin") {
        if (UsuarioModel::mdlEliminarCliente($id)) {
            $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Cliente eliminado con éxito."];
        } else {
            $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al eliminar el cliente."];
        }
        header("Location: index.php?ruta=admin&tab=clientes");
        exit();
    }

    // Eliminar Trabajador
    if ($action === "eliminar_trabajador" && $rol === "admin") {
        if (UsuarioModel::mdlEliminarTrabajador($id)) {
            $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Trabajador eliminado con éxito."];
        } else {
            $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al eliminar el trabajador."];
        }
        header("Location: index.php?ruta=admin&tab=trabajadores");
        exit();
    }

    // Eliminar Proveedor
    if ($action === "eliminar_proveedor" && $rol === "admin") {
        if (UsuarioModel::mdlEliminarProveedor($id)) {
            $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Proveedor eliminado con éxito."];
        } else {
            $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al eliminar el proveedor."];
        }
        header("Location: index.php?ruta=admin&tab=proveedores");
        exit();
    }

    // Eliminar Rol
    if ($action === "eliminar_rol" && $rol === "admin") {
        if (UsuarioModel::mdlEliminarRol($id)) {
            $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Rol eliminado con éxito."];
        } else {
            $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al eliminar el rol."];
        }
        header("Location: index.php?ruta=admin&tab=roles");
        exit();
    }
}

// ==========================================
// --- PROCESAMIENTO DE ACCIONES DE POST ---
// ==========================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
    $action = $_POST["action"];

    // Editar Pedido
    if ($action === "editar_pedido") {
        $id = intval($_POST["id"]);
        $estado = $_POST["estado"];
        $totalPEN = floatval($_POST["total"]);
        $totalUSD = $totalPEN / 3.80;

        if (UsuarioModel::mdlEditarPedido($id, $estado, $totalUSD)) {
            $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Pedido #$id actualizado con éxito."];
        } else {
            $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al intentar actualizar el pedido #$id."];
        }
        header("Location: index.php?ruta=admin&tab=pedidos");
        exit();
    }

    // Actualizar Stock de Producto
    if ($action === "actualizar_stock" && $rol === "admin") {
        $id = intval($_POST["id"]);
        $stock = intval($_POST["stock"]);

        if (UsuarioModel::mdlActualizarStockProducto($id, $stock)) {
            $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Stock del producto #$id actualizado correctamente."];
        } else {
            $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al actualizar el stock."];
        }
        header("Location: index.php?ruta=admin&tab=inventario");
        exit();
    }

    // Crear/Editar Usuario
    if ($action === "guardar_usuario" && $rol === "admin") {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : null;
        $nombre = trim($_POST["nombre"]);
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);
        $usuarioRol = $_POST["rol"];

        $datos = [
            "nombre" => $nombre,
            "email" => $email,
            "rol" => $usuarioRol
        ];

        if ($id) {
            $datos["id"] = $id;
            if (!empty($password)) {
                $datos["password"] = password_hash($password, PASSWORD_BCRYPT);
            }
            if (UsuarioModel::mdlEditarUsuario($datos)) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Usuario actualizado correctamente."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al actualizar el usuario (puede que el email ya exista)."];
            }
        } else {
            $datos["password"] = password_hash(!empty($password) ? $password : '123456', PASSWORD_BCRYPT);
            if (UsuarioModel::mdlCrearUsuario($datos)) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Usuario creado correctamente."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al crear el usuario (el email ya existe)."];
            }
        }
        header("Location: index.php?ruta=admin&tab=usuarios");
        exit();
    }

    // Crear/Editar Cliente
    if ($action === "guardar_cliente" && $rol === "admin") {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : null;
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
            if (UsuarioModel::mdlEditarCliente($datos)) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Cliente actualizado correctamente."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al actualizar el cliente."];
            }
        } else {
            if (UsuarioModel::mdlCrearCliente($datos)) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Cliente registrado correctamente."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al registrar el cliente (el email ya existe)."];
            }
        }
        header("Location: index.php?ruta=admin&tab=clientes");
        exit();
    }

    // Crear/Editar Trabajador
    if ($action === "guardar_trabajador" && $rol === "admin") {
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
            if (UsuarioModel::mdlEditarTrabajador($datos)) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Trabajador actualizado correctamente."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al actualizar el trabajador."];
            }
        } else {
            if (UsuarioModel::mdlCrearTrabajador($datos)) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Trabajador registrado correctamente."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al registrar el trabajador."];
            }
        }
        header("Location: index.php?ruta=admin&tab=trabajadores");
        exit();
    }

    // Crear/Editar Proveedor
    if ($action === "guardar_proveedor" && $rol === "admin") {
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
            if (UsuarioModel::mdlEditarProveedor($datos)) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Proveedor actualizado correctamente."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al actualizar el proveedor."];
            }
        } else {
            if (UsuarioModel::mdlCrearProveedor($datos)) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Proveedor registrado correctamente."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al registrar el proveedor."];
            }
        }
        header("Location: index.php?ruta=admin&tab=proveedores");
        exit();
    }

    // Crear/Editar Rol
    if ($action === "guardar_rol" && $rol === "admin") {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : null;
        $nombre = trim($_POST["nombre"]);
        $descripcion = trim($_POST["descripcion"]);

        $datos = [
            "nombre" => $nombre,
            "descripcion" => $descripcion
        ];

        if ($id) {
            $datos["id"] = $id;
            if (UsuarioModel::mdlEditarRol($datos)) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Rol actualizado correctamente."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al actualizar el rol."];
            }
        } else {
            if (UsuarioModel::mdlCrearRol($datos)) {
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Rol creado correctamente."];
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al crear el rol."];
            }
        }
        header("Location: index.php?ruta=admin&tab=roles");
        exit();
    }
}

// Cargar listas comunes
$pedidos = UsuarioModel::mdlMostrarPedidos() ?: [];
$productos = ProductoModel::mdlMostrarProductos('productos') ?: [];

// Calcular ventas por período
$ventasDia = UsuarioModel::mdlObtenerVentasDia();
$ventasSemana = UsuarioModel::mdlObtenerVentasSemana();
$ventasMes = UsuarioModel::mdlObtenerVentasMes();
$ventasTotales = 0;
foreach ($pedidos as $pedido) {
    if ($pedido["estado"] === "completado") {
        $ventasTotales += $pedido["total"];
    }
}
?>

<!-- Contenedor del ERP Administrativo -->
<div class="admin-erp-container">
    
    <!-- Sidebar de Navegación Lateral -->
    <aside class="admin-sidebar">
        <div class="sidebar-user-card">
            <div class="user-avatar">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <div class="user-details">
                <h4 class="user-name"><?php echo htmlspecialchars($_SESSION["nombre"]); ?></h4>
                <span class="user-role-badge <?php echo $rol === 'admin' ? 'role-admin' : 'role-worker'; ?>">
                    <i class="fa-solid <?php echo $rol === 'admin' ? 'fa-shield-halved' : 'fa-clipboard-user'; ?>"></i>
                    <?php echo $rol === 'admin' ? 'Administrador' : 'Trabajador'; ?>
                </span>
            </div>
        </div>

        <nav class="sidebar-menu">
            <span class="menu-group-title">Módulos</span>
            <ul>
                <?php if ($rol === 'admin'): ?>
                    <li>
                        <a href="index.php?ruta=admin&tab=dashboard" class="menu-link <?php echo $tab === 'dashboard' ? 'active' : ''; ?>">
                            <i class="fa-solid fa-chart-pie"></i> Dashboard
                        </a>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="index.php?ruta=admin&tab=ventas" class="menu-link <?php echo $tab === 'ventas' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-hand-holding-dollar"></i> Ventas
                    </a>
                </li>
                <li>
                    <a href="index.php?ruta=admin&tab=pedidos" class="menu-link <?php echo $tab === 'pedidos' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-receipt"></i> Pedidos
                    </a>
                </li>
                <li>
                    <a href="index.php?ruta=admin&tab=productos" class="menu-link <?php echo $tab === 'productos' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-mug-hot"></i> Productos
                    </a>
                </li>
                <?php if ($rol === 'admin'): ?>
                    <li>
                        <a href="index.php?ruta=admin&tab=inventario" class="menu-link <?php echo $tab === 'inventario' ? 'active' : ''; ?>">
                            <i class="fa-solid fa-boxes-stacked"></i> Inventario
                        </a>
                    </li>
                    <span class="menu-group-title">Administración</span>
                    <li>
                        <a href="index.php?ruta=admin&tab=usuarios" class="menu-link <?php echo $tab === 'usuarios' ? 'active' : ''; ?>">
                            <i class="fa-solid fa-users-gear"></i> Usuarios
                        </a>
                    </li>
                    <li>
                        <a href="index.php?ruta=admin&tab=clientes" class="menu-link <?php echo $tab === 'clientes' ? 'active' : ''; ?>">
                            <i class="fa-solid fa-users"></i> Clientes
                        </a>
                    </li>
                    <li>
                        <a href="index.php?ruta=admin&tab=trabajadores" class="menu-link <?php echo $tab === 'trabajadores' ? 'active' : ''; ?>">
                            <i class="fa-solid fa-id-card"></i> Trabajadores
                        </a>
                    </li>
                    <li>
                        <a href="index.php?ruta=admin&tab=roles" class="menu-link <?php echo $tab === 'roles' ? 'active' : ''; ?>">
                            <i class="fa-solid fa-user-lock"></i> Roles
                        </a>
                    </li>
                    <li>
                        <a href="index.php?ruta=admin&tab=proveedores" class="menu-link <?php echo $tab === 'proveedores' ? 'active' : ''; ?>">
                            <i class="fa-solid fa-truck-field"></i> Proveedores
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </aside>

    <!-- Contenido del ERP -->
    <main class="admin-content">
        
        <!-- Mensajes Flash de Notificación -->
        <?php
        $msgFlash = $_SESSION["msg_flash"] ?? null;
        unset($_SESSION["msg_flash"]);
        if ($msgFlash): ?>
            <div class="alert alert-<?php echo htmlspecialchars($msgFlash['tipo']); ?>" style="margin-bottom: 2rem;">
                <i class="fa-solid <?php echo $msgFlash['tipo'] === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
                <?php echo htmlspecialchars($msgFlash['texto']); ?>
            </div>
        <?php endif; ?>

        <!-- CABECERA -->
        <div class="admin-content-header">
            <div>
                <h2 class="admin-page-title">
                    <?php
                    $titulos = [
                        'dashboard' => 'Dashboard de Control',
                        'ventas' => 'Reporte de Ventas',
                        'pedidos' => 'Gestión de Pedidos',
                        'productos' => 'Catálogo de Productos',
                        'inventario' => 'Control de Inventario',
                        'usuarios' => 'Gestión de Usuarios',
                        'clientes' => 'Directorio de Clientes',
                        'trabajadores' => 'Plantilla de Trabajadores',
                        'roles' => 'Roles de Acceso',
                        'proveedores' => 'Registro de Proveedores'
                    ];
                    echo $titulos[$tab] ?? 'Administración';
                    ?>
                </h2>
                <p class="admin-page-subtitle">Gestión operativa del sistema Dulce Café.</p>
            </div>
            
            <?php if ($tab === 'ventas' || $tab === 'pedidos'): ?>
                <div class="admin-actions">
                    <a href="index.php?ruta=reporte_excel" class="btn-reporte btn-excel" title="Exportar historial de ventas a Excel">
                        <i class="fa-solid fa-file-excel"></i> Exportar Excel
                    </a>
                    <a href="index.php?ruta=reporte_pdf" target="_blank" class="btn-reporte btn-pdf" title="Ver reporte en PDF para imprimir">
                        <i class="fa-solid fa-file-pdf"></i> Imprimir PDF
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- ========================================== -->
        <!-- PESTAÑA: DASHBOARD (ADMIN ONLY) -->
        <!-- ========================================== -->
        <?php if ($tab === 'dashboard' && $rol === 'admin'): ?>
            
            <!-- KPIs Generales -->
            <div class="kpi-grid">
                <div class="kpi-card kpi-ventas">
                    <div class="kpi-icon"><i class="fa-solid fa-sack-dollar"></i></div>
                    <div class="kpi-data">
                        <span class="kpi-label">Ventas del Mes</span>
                        <strong class="kpi-value">S/<?php echo number_format($ventasMes * 3.80, 2); ?></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-pedidos">
                    <div class="kpi-icon"><i class="fa-solid fa-receipt"></i></div>
                    <div class="kpi-data">
                        <span class="kpi-label">Pedidos Totales</span>
                        <strong class="kpi-value"><?php echo count($pedidos); ?></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-productos">
                    <div class="kpi-icon"><i class="fa-solid fa-mug-saucer"></i></div>
                    <div class="kpi-data">
                        <span class="kpi-label">Productos Menú</span>
                        <strong class="kpi-value"><?php echo count($productos); ?></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-alertas">
                    <div class="kpi-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div class="kpi-data">
                        <span class="kpi-label">Stock Crítico</span>
                        <strong class="kpi-value">
                            <?php
                            $critico = 0;
                            foreach ($productos as $p) { if ($p['stock'] <= 5) $critico++; }
                            echo $critico;
                            ?>
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Gráficos de Ventas -->
            <div class="dashboard-charts-grid">
                <div class="chart-card">
                    <h3><i class="fa-solid fa-chart-line"></i> Evolución de Ventas (Últimos 7 días)</h3>
                    <div class="chart-container">
                        <canvas id="salesWeekChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3><i class="fa-solid fa-chart-pie"></i> Ventas por Categoría</h3>
                    <div class="chart-container">
                        <canvas id="salesCategoryChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Accesos Rápidos y Alertas de Inventario -->
            <div class="dashboard-details-grid">
                <div class="detail-card">
                    <h3><i class="fa-solid fa-bolt"></i> Atajos Rápidos</h3>
                    <div class="quick-links-grid">
                        <a href="index.php?ruta=producto" class="quick-link-btn">
                            <i class="fa-solid fa-circle-plus"></i> Nuevo Producto
                        </a>
                        <a href="index.php?ruta=admin&tab=pedidos" class="quick-link-btn">
                            <i class="fa-solid fa-list-check"></i> Bandeja de Pedidos
                        </a>
                        <a href="index.php?ruta=admin&tab=inventario" class="quick-link-btn">
                            <i class="fa-solid fa-boxes-stacked"></i> Ver Stock
                        </a>
                        <a href="index.php?ruta=admin&tab=usuarios" class="quick-link-btn">
                            <i class="fa-solid fa-user-shield"></i> Control de Accesos
                        </a>
                    </div>
                </div>
                <div class="detail-card">
                    <h3><i class="fa-solid fa-hourglass-half"></i> Pedidos Recientes</h3>
                    <div class="recent-table-responsive">
                        <table class="recent-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $contador = 0;
                                foreach ($pedidos as $p): 
                                    if ($contador >= 4) break;
                                ?>
                                    <tr>
                                        <td>#<?php echo $p['id']; ?></td>
                                        <td><?php echo htmlspecialchars($p['cliente_nombre']); ?></td>
                                        <td>S/<?php echo number_format($p['total'] * 3.80, 2); ?></td>
                                        <td>
                                            <span class="estado-badge estado-<?php echo $p['estado']; ?>">
                                                <?php echo ucfirst($p['estado']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php 
                                    $contador++;
                                endforeach; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <!-- ========================================== -->
        <!-- PESTAÑA: VENTAS (ADMIN & WORKER) -->
        <!-- ========================================== -->
        <?php elseif ($tab === 'ventas'): ?>
            <div class="kpi-grid">
                <div class="kpi-card kpi-dia">
                    <div class="kpi-icon"><i class="fa-solid fa-calendar-day"></i></div>
                    <div class="kpi-data">
                        <span class="kpi-label">Ventas del Día (Hoy)</span>
                        <strong class="kpi-value">S/<?php echo number_format($ventasDia * 3.80, 2); ?></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-semana">
                    <div class="kpi-icon"><i class="fa-solid fa-calendar-week"></i></div>
                    <div class="kpi-data">
                        <span class="kpi-label">Ventas de la Semana</span>
                        <strong class="kpi-value">S/<?php echo number_format($ventasSemana * 3.80, 2); ?></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-mes">
                    <div class="kpi-icon"><i class="fa-solid fa-calendar-days"></i></div>
                    <div class="kpi-data">
                        <span class="kpi-label">Ventas del Mes</span>
                        <strong class="kpi-value">S/<?php echo number_format($ventasMes * 3.80, 2); ?></strong>
                    </div>
                </div>
            </div>

            <div class="admin-table-section" style="margin-top: 2rem;">
                <h3 class="admin-section-title"><i class="fa-solid fa-money-check-dollar"></i> Reporte Diario de Cierre</h3>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Fecha del Pedido</th>
                                <th>Cantidad Pedidos</th>
                                <th>Estado</th>
                                <th>Ingreso Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $ventasAgrupadas = [];
                            foreach ($pedidos as $p) {
                                $diaKey = date("d/m/Y", strtotime($p["fecha"]));
                                if (!isset($ventasAgrupadas[$diaKey])) {
                                    $ventasAgrupadas[$diaKey] = ['cant' => 0, 'total' => 0, 'estado' => $p['estado']];
                                }
                                $ventasAgrupadas[$diaKey]['cant']++;
                                if ($p['estado'] === 'completado') {
                                    $ventasAgrupadas[$diaKey]['total'] += $p['total'];
                                }
                            }
                            if (count($ventasAgrupadas) > 0):
                                foreach ($ventasAgrupadas as $fechaDia => $datosDia):
                            ?>
                                <tr>
                                    <td><strong><?php echo $fechaDia; ?></strong></td>
                                    <td><?php echo $datosDia['cant']; ?> pedidos</td>
                                    <td><span class="estado-badge estado-completado">Completado / Activo</span></td>
                                    <td class="td-total">S/<?php echo number_format($datosDia['total'] * 3.80, 2); ?></td>
                                </tr>
                            <?php 
                                endforeach;
                            else: ?>
                                <tr><td colspan="4" class="text-center">No hay registros de ventas para mostrar.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ========================================== -->
        <!-- PESTAÑA: PEDIDOS (ADMIN & WORKER) -->
        <!-- ========================================== -->
        <?php elseif ($tab === 'pedidos'): ?>
            <div class="admin-table-section">
                <div class="section-header-flex">
                    <h3 class="admin-section-title"><i class="fa-solid fa-receipt"></i> Bandeja General de Pedidos</h3>
                </div>

                <?php if (count($pedidos) > 0): ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>#ID</th>
                                    <th>Cliente</th>
                                    <th>Email</th>
                                    <th>Total</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th style="text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedidos as $p): ?>
                                    <tr>
                                        <td><strong>#<?php echo $p["id"]; ?></strong></td>
                                        <td><?php echo htmlspecialchars($p["cliente_nombre"]); ?></td>
                                        <td><?php echo htmlspecialchars($p["cliente_email"]); ?></td>
                                        <td class="td-total">S/<?php echo number_format($p["total"] * 3.80, 2); ?></td>
                                        <td><?php echo date("d/m/Y H:i", strtotime($p["fecha"])); ?></td>
                                        <td>
                                            <span class="estado-badge estado-<?php echo $p["estado"]; ?>">
                                                <?php echo ucfirst($p["estado"]); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="pedido-acciones" style="display: flex; gap: 0.4rem; justify-content: center; flex-wrap: wrap;">
                                                <a href="index.php?ruta=reporte_pdf&id=<?php echo $p['id']; ?>" target="_blank" class="btn-grid btn-view-pdf" title="Descargar Boleta PDF">
                                                    <i class="fa-solid fa-file-pdf"></i> Boleta
                                                </a>
                                                
                                                <?php if ($p["estado"] === "pendiente"): ?>
                                                    <a href="index.php?ruta=admin&action=aceptar&id=<?php echo $p['id']; ?>" class="btn-grid btn-complete" title="Aceptar y Completar">
                                                        <i class="fa-solid fa-circle-check"></i> Aceptar
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <button type="button" class="btn-grid btn-edit-order" 
                                                        data-id="<?php echo $p['id']; ?>" 
                                                        data-estado="<?php echo $p['estado']; ?>" 
                                                        data-total="<?php echo number_format($p['total'] * 3.80, 2, '.', ''); ?>" 
                                                        onclick="abrirModalEditarPedido(this)">
                                                    <i class="fa-solid fa-pen"></i> Editar
                                                </button>
                                                
                                                <a href="index.php?ruta=admin&action=eliminar_pedido&id=<?php echo $p['id']; ?>" class="btn-grid btn-delete" onclick="return confirm('¿Desea eliminar permanentemente este pedido?')">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-inbox font-empty"></i>
                        <p>No se encontraron pedidos registrados.</p>
                    </div>
                <?php endif; ?>
            </div>

        <!-- ========================================== -->
        <!-- PESTAÑA: PRODUCTOS (ADMIN & WORKER) -->
        <!-- ========================================== -->
        <?php elseif ($tab === 'productos'): ?>
            <div class="admin-table-section">
                <div class="section-header-flex">
                    <h3 class="admin-section-title"><i class="fa-solid fa-mug-hot"></i> Gestión de Catálogo Gourmet</h3>
                    <a href="index.php?ruta=producto" class="btn-agregar-entidad">
                        <i class="fa-solid fa-circle-plus"></i> Agregar Nuevo Producto
                    </a>
                </div>

                <?php if (count($productos) > 0): ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Imagen</th>
                                    <th>Nombre del Producto</th>
                                    <th>Categoría</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th style="text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $prod): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo htmlspecialchars($prod['imagen']); ?>" alt="Producto" class="prod-table-thumb" onerror="this.src='assets/img/default_coffee.png'">
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($prod["nombre"]); ?></strong></td>
                                        <td><?php echo htmlspecialchars($prod["categoria"]); ?></td>
                                        <td class="td-total">S/<?php echo number_format($prod["precio"] * 3.80, 2); ?></td>
                                        <td>
                                            <span class="stock-badge <?php echo $prod["stock"] <= 5 ? 'stock-bajo' : 'stock-ok'; ?>">
                                                <?php echo htmlspecialchars($prod["stock"]); ?> uds.
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 0.4rem; justify-content: center;">
                                                <a href="index.php?ruta=producto&id=<?php echo $prod['id']; ?>" class="btn-grid btn-edit-order" style="background: var(--cafe-700);">
                                                    <i class="fa-solid fa-pen-to-square"></i> Modificar / Eliminar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        <!-- ========================================== -->
        <!-- PESTAÑA: INVENTARIO (ADMIN ONLY) -->
        <!-- ========================================== -->
        <?php elseif ($tab === 'inventario' && $rol === 'admin'): ?>
            <div class="admin-table-section">
                <h3 class="admin-section-title"><i class="fa-solid fa-boxes-stacked"></i> Control y Reposición de Inventario</h3>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Stock Actual</th>
                                <th>Estado Stock</th>
                                <th style="text-align: center;">Acción Rápida</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $prod): ?>
                                <tr>
                                    <td>#<?php echo $prod['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($prod['nombre']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($prod['categoria']); ?></td>
                                    <td><strong><?php echo $prod['stock']; ?> unidades</strong></td>
                                    <td>
                                        <span class="stock-badge <?php echo $prod['stock'] <= 5 ? 'stock-bajo' : 'stock-ok'; ?>">
                                            <?php echo $prod['stock'] <= 5 ? 'Crítico (Reposición)' : 'Óptimo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form action="index.php?ruta=admin&tab=inventario" method="POST" style="display: flex; gap: 0.4rem; justify-content: center; align-items: center;">
                                            <input type="hidden" name="action" value="actualizar_stock">
                                            <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                            <input type="number" name="stock" value="<?php echo $prod['stock']; ?>" min="0" class="form-input" style="width: 80px; padding: 0.3rem; margin: 0;" required>
                                            <button type="submit" class="btn-grid btn-complete" style="padding: 0.35rem 0.6rem;" title="Guardar Stock">
                                                <i class="fa-solid fa-floppy-disk"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ========================================== -->
        <!-- PESTAÑA: USUARIOS (ADMIN ONLY) -->
        <!-- ========================================== -->
        <?php elseif ($tab === 'usuarios' && $rol === 'admin'): 
            $usuariosAll = UsuarioModel::mdlMostrarUsuariosAll();
        ?>
            <div class="admin-table-section">
                <div class="section-header-flex">
                    <h3 class="admin-section-title"><i class="fa-solid fa-users-gear"></i> Usuarios en el Sistema</h3>
                    <button type="button" class="btn-agregar-entidad" onclick="abrirModalUsuario()">
                        <i class="fa-solid fa-circle-plus"></i> Registrar Usuario
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol de Acceso</th>
                                <th>Fecha de Creación</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuariosAll as $u): ?>
                                <tr>
                                    <td>#<?php echo $u['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($u['nombre']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td>
                                        <span class="user-role-badge <?php echo $u['rol'] === 'admin' ? 'role-admin' : ($u['rol'] === 'trabajador' ? 'role-worker' : 'role-client'); ?>">
                                            <?php echo ucfirst($u['rol']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date("d/m/Y H:i", strtotime($u['creado_en'])); ?></td>
                                    <td>
                                        <div style="display: flex; gap: 0.4rem; justify-content: center;">
                                            <button class="btn-grid btn-edit-order"
                                                    data-id="<?php echo $u['id']; ?>"
                                                    data-nombre="<?php echo htmlspecialchars($u['nombre']); ?>"
                                                    data-email="<?php echo htmlspecialchars($u['email']); ?>"
                                                    data-rol="<?php echo $u['rol']; ?>"
                                                    onclick="abrirModalUsuario(this)">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <a href="index.php?ruta=admin&action=eliminar_usuario&id=<?php echo $u['id']; ?>" class="btn-grid btn-delete" onclick="return confirm('¿Seguro que deseas eliminar este usuario?')">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ========================================== -->
        <!-- PESTAÑA: CLIENTES (ADMIN ONLY) -->
        <!-- ========================================== -->
        <?php elseif ($tab === 'clientes' && $rol === 'admin'): 
            $clientesAll = UsuarioModel::mdlMostrarClientes();
        ?>
            <div class="admin-table-section">
                <div class="section-header-flex">
                    <h3 class="admin-section-title"><i class="fa-solid fa-users"></i> Directorio de Clientes</h3>
                    <button class="btn-agregar-entidad" onclick="abrirModalCliente()">
                        <i class="fa-solid fa-circle-plus"></i> Registrar Cliente
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Dirección</th>
                                <th>Puntos Acumulados</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($clientesAll) > 0): ?>
                                <?php foreach ($clientesAll as $c): ?>
                                    <tr>
                                        <td>#<?php echo $c['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($c['nombre']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($c['email']); ?></td>
                                        <td><?php echo htmlspecialchars($c['telefono']); ?></td>
                                        <td><?php echo htmlspecialchars($c['direccion']); ?></td>
                                        <td><span class="stock-badge stock-ok"><?php echo $c['puntos']; ?> pts</span></td>
                                        <td>
                                            <div style="display: flex; gap: 0.4rem; justify-content: center;">
                                                <button class="btn-grid btn-edit-order"
                                                        data-id="<?php echo $c['id']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($c['nombre']); ?>"
                                                        data-email="<?php echo htmlspecialchars($c['email']); ?>"
                                                        data-telefono="<?php echo htmlspecialchars($c['telefono']); ?>"
                                                        data-direccion="<?php echo htmlspecialchars($c['direccion']); ?>"
                                                        data-puntos="<?php echo $c['puntos']; ?>"
                                                        onclick="abrirModalCliente(this)">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <a href="index.php?ruta=admin&action=eliminar_cliente&id=<?php echo $c['id']; ?>" class="btn-grid btn-delete" onclick="return confirm('¿Seguro que deseas eliminar este cliente?')">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center">No hay clientes registrados en la tabla adicional.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ========================================== -->
        <!-- PESTAÑA: TRABAJADORES (ADMIN ONLY) -->
        <!-- ========================================== -->
        <?php elseif ($tab === 'trabajadores' && $rol === 'admin'): 
            $trabajadoresAll = UsuarioModel::mdlMostrarTrabajadores();
        ?>
            <div class="admin-table-section">
                <div class="section-header-flex">
                    <h3 class="admin-section-title"><i class="fa-solid fa-id-card"></i> Nómina de Trabajadores</h3>
                    <button class="btn-agregar-entidad" onclick="abrirModalTrabajador()">
                        <i class="fa-solid fa-circle-plus"></i> Registrar Trabajador
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Cargo</th>
                                <th>Salario Mensual</th>
                                <th>Fecha Ingreso</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($trabajadoresAll) > 0): ?>
                                <?php foreach ($trabajadoresAll as $t): ?>
                                    <tr>
                                        <td>#<?php echo $t['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($t['nombre']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($t['email']); ?></td>
                                        <td><?php echo htmlspecialchars($t['telefono']); ?></td>
                                        <td><span class="user-role-badge role-worker"><?php echo htmlspecialchars($t['cargo']); ?></span></td>
                                        <td class="td-total">S/<?php echo number_format($t['salario'], 2); ?></td>
                                        <td><?php echo date("d/m/Y", strtotime($t['fecha_ingreso'])); ?></td>
                                        <td>
                                            <div style="display: flex; gap: 0.4rem; justify-content: center;">
                                                <button class="btn-grid btn-edit-order"
                                                        data-id="<?php echo $t['id']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($t['nombre']); ?>"
                                                        data-email="<?php echo htmlspecialchars($t['email']); ?>"
                                                        data-telefono="<?php echo htmlspecialchars($t['telefono']); ?>"
                                                        data-cargo="<?php echo htmlspecialchars($t['cargo']); ?>"
                                                        data-salario="<?php echo $t['salario']; ?>"
                                                        data-fecha_ingreso="<?php echo $t['fecha_ingreso']; ?>"
                                                        onclick="abrirModalTrabajador(this)">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <a href="index.php?ruta=admin&action=eliminar_trabajador&id=<?php echo $t['id']; ?>" class="btn-grid btn-delete" onclick="return confirm('¿Seguro que deseas eliminar este trabajador?')">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center">No hay trabajadores registrados.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ========================================== -->
        <!-- PESTAÑA: ROLES (ADMIN ONLY) -->
        <!-- ========================================== -->
        <?php elseif ($tab === 'roles' && $rol === 'admin'): 
            $rolesAll = UsuarioModel::mdlMostrarRoles();
        ?>
            <div class="admin-table-section">
                <div class="section-header-flex">
                    <h3 class="admin-section-title"><i class="fa-solid fa-user-lock"></i> Definición de Roles</h3>
                    <button class="btn-agregar-entidad" onclick="abrirModalRol()">
                        <i class="fa-solid fa-circle-plus"></i> Crear Nuevo Rol
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre Rol</th>
                                <th>Descripción</th>
                                <th>Fecha Registro</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rolesAll as $r): ?>
                                <tr>
                                    <td>#<?php echo $r['id']; ?></td>
                                    <td>
                                        <span class="user-role-badge  <?php echo $r['nombre'] === 'admin' ? 'role-admin' : ($r['nombre'] === 'trabajador' ? 'role-worker' : 'role-client'); ?>">
                                            <?php echo htmlspecialchars($r['nombre']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($r['descripcion']); ?></td>
                                    <td><?php echo date("d/m/Y", strtotime($r['creado_en'])); ?></td>
                                    <td>
                                        <div style="display: flex; gap: 0.4rem; justify-content: center;">
                                            <button class="btn-grid btn-edit-order"
                                                    data-id="<?php echo $r['id']; ?>"
                                                    data-nombre="<?php echo htmlspecialchars($r['nombre']); ?>"
                                                    data-descripcion="<?php echo htmlspecialchars($r['descripcion']); ?>"
                                                    onclick="abrirModalRol(this)">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <a href="index.php?ruta=admin&action=eliminar_rol&id=<?php echo $r['id']; ?>" class="btn-grid btn-delete" onclick="return confirm('¿Seguro que deseas eliminar este rol?')">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ========================================== -->
        <!-- PESTAÑA: PROVEEDORES (ADMIN ONLY) -->
        <!-- ========================================== -->
        <?php elseif ($tab === 'proveedores' && $rol === 'admin'): 
            $proveedoresAll = UsuarioModel::mdlMostrarProveedores();
        ?>
            <div class="admin-table-section">
                <div class="section-header-flex">
                    <h3 class="admin-section-title"><i class="fa-solid fa-truck-field"></i> Directorio de Proveedores Homologados</h3>
                    <button class="btn-agregar-entidad" onclick="abrirModalProveedor()">
                        <i class="fa-solid fa-circle-plus"></i> Registrar Proveedor
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Proveedor</th>
                                <th>Contacto</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Dirección</th>
                                <th>Producto Principal</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($proveedoresAll) > 0): ?>
                                <?php foreach ($proveedoresAll as $prov): ?>
                                    <tr>
                                        <td>#<?php echo $prov['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($prov['nombre']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($prov['contacto']); ?></td>
                                        <td><?php echo htmlspecialchars($prov['email']); ?></td>
                                        <td><?php echo htmlspecialchars($prov['telefono']); ?></td>
                                        <td><?php echo htmlspecialchars($prov['direccion']); ?></td>
                                        <td><span class="stock-badge stock-ok"><?php echo htmlspecialchars($prov['producto_principal']); ?></span></td>
                                        <td>
                                            <div style="display: flex; gap: 0.4rem; justify-content: center;">
                                                <button class="btn-grid btn-edit-order"
                                                        data-id="<?php echo $prov['id']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($prov['nombre']); ?>"
                                                        data-contacto="<?php echo htmlspecialchars($prov['contacto']); ?>"
                                                        data-email="<?php echo htmlspecialchars($prov['email']); ?>"
                                                        data-telefono="<?php echo htmlspecialchars($prov['telefono']); ?>"
                                                        data-direccion="<?php echo htmlspecialchars($prov['direccion']); ?>"
                                                        data-producto_principal="<?php echo htmlspecialchars($prov['producto_principal']); ?>"
                                                        onclick="abrirModalProveedor(this)">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <a href="index.php?ruta=admin&action=eliminar_proveedor&id=<?php echo $prov['id']; ?>" class="btn-grid btn-delete" onclick="return confirm('¿Seguro que deseas eliminar este proveedor?')">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center">No hay proveedores registrados.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php endif; ?>

    </main>
</div>

<!-- ======================================================= -->
<!-- MODALES PARA OPERACIONES CRUD DE LA CONSOLA ERP -->
<!-- ======================================================= -->

<!-- Modal: Editar Pedido -->
<div id="modal-editar-pedido" class="modal-backdrop">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fa-solid fa-receipt"></i> Editar Pedido <span id="modal-id-label"></span></h3>
            <button class="close-modal" onclick="cerrarModalEditarPedido()">&times;</button>
        </div>
        <form action="index.php?ruta=admin&tab=pedidos" method="POST">
            <input type="hidden" name="action" value="editar_pedido">
            <input type="hidden" id="edit-id" name="id" value="">
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit-estado"><i class="fa-solid fa-list-check"></i> Estado del Pedido</label>
                    <select id="edit-estado" name="estado" class="form-input" required>
                        <option value="pendiente">Pendiente</option>
                        <option value="completado">Completado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit-total"><i class="fa-solid fa-coins"></i> Total Facturado (S/)</label>
                    <input type="number" id="edit-total" name="total" step="0.01" class="form-input" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="cerrarModalEditarPedido()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: CRUD Usuario -->
<div id="modal-usuario" class="modal-backdrop">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-user-title"><i class="fa-solid fa-user-gear"></i> Registrar Usuario</h3>
            <button class="close-modal" onclick="cerrarModalUsuario()">&times;</button>
        </div>
        <form action="index.php?ruta=admin&tab=usuarios" method="POST">
            <input type="hidden" name="action" value="guardar_usuario">
            <input type="hidden" id="user-id" name="id" value="">
            <div class="modal-body">
                <div class="form-group">
                    <label for="user-nombre"><i class="fa-solid fa-signature"></i> Nombre Completo</label>
                    <input type="text" id="user-nombre" name="nombre" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="user-email"><i class="fa-solid fa-envelope"></i> Correo Electrónico</label>
                    <input type="email" id="user-email" name="email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="user-password"><i class="fa-solid fa-lock"></i> Contraseña (vacío para mantener actual o por defecto: 123456)</label>
                    <input type="password" id="user-password" name="password" class="form-input">
                </div>
                <div class="form-group">
                    <label for="user-rol"><i class="fa-solid fa-user-lock"></i> Rol de Sistema</label>
                    <select id="user-rol" name="rol" class="form-input" required>
                        <option value="cliente">Cliente</option>
                        <option value="trabajador">Trabajador</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="cerrarModalUsuario()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar Registro</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: CRUD Cliente -->
<div id="modal-cliente" class="modal-backdrop">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-client-title"><i class="fa-solid fa-users"></i> Registrar Cliente</h3>
            <button class="close-modal" onclick="cerrarModalCliente()">&times;</button>
        </div>
        <form action="index.php?ruta=admin&tab=clientes" method="POST">
            <input type="hidden" name="action" value="guardar_cliente">
            <input type="hidden" id="client-id" name="id" value="">
            <div class="modal-body">
                <div class="form-group">
                    <label for="client-nombre"><i class="fa-solid fa-user"></i> Nombre</label>
                    <input type="text" id="client-nombre" name="nombre" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="client-email"><i class="fa-solid fa-envelope"></i> Correo</label>
                    <input type="email" id="client-email" name="email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="client-telefono"><i class="fa-solid fa-phone"></i> Teléfono</label>
                    <input type="text" id="client-telefono" name="telefono" class="form-input">
                </div>
                <div class="form-group">
                    <label for="client-direccion"><i class="fa-solid fa-location-dot"></i> Dirección</label>
                    <input type="text" id="client-direccion" name="direccion" class="form-input">
                </div>
                <div class="form-group">
                    <label for="client-puntos"><i class="fa-solid fa-award"></i> Puntos de Fidelidad</label>
                    <input type="number" id="client-puntos" name="puntos" class="form-input" value="0" min="0" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="cerrarModalCliente()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar Cliente</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: CRUD Trabajador -->
<div id="modal-trabajador" class="modal-backdrop">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-worker-title"><i class="fa-solid fa-id-card"></i> Registrar Trabajador</h3>
            <button class="close-modal" onclick="cerrarModalTrabajador()">&times;</button>
        </div>
        <form action="index.php?ruta=admin&tab=trabajadores" method="POST">
            <input type="hidden" name="action" value="guardar_trabajador">
            <input type="hidden" id="worker-id" name="id" value="">
            <div class="modal-body">
                <div class="form-group">
                    <label for="worker-nombre"><i class="fa-solid fa-user-tie"></i> Nombre Completo</label>
                    <input type="text" id="worker-nombre" name="nombre" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="worker-email"><i class="fa-solid fa-envelope"></i> Email corporativo</label>
                    <input type="email" id="worker-email" name="email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="worker-telefono"><i class="fa-solid fa-phone"></i> Teléfono</label>
                    <input type="text" id="worker-telefono" name="telefono" class="form-input">
                </div>
                <div class="form-group">
                    <label for="worker-cargo"><i class="fa-solid fa-briefcase"></i> Puesto/Cargo</label>
                    <input type="text" id="worker-cargo" name="cargo" placeholder="Barista / Caja / Administrador" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="worker-salario"><i class="fa-solid fa-money-bill-wave"></i> Salario Mensual (S/)</label>
                    <input type="number" id="worker-salario" name="salario" step="0.01" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="worker-fecha"><i class="fa-solid fa-calendar-plus"></i> Fecha Ingreso</label>
                    <input type="date" id="worker-fecha" name="fecha_ingreso" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="cerrarModalTrabajador()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar Ficha</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: CRUD Rol -->
<div id="modal-rol" class="modal-backdrop">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-role-title"><i class="fa-solid fa-user-lock"></i> Definir Rol</h3>
            <button class="close-modal" onclick="cerrarModalRol()">&times;</button>
        </div>
        <form action="index.php?ruta=admin&tab=roles" method="POST">
            <input type="hidden" name="action" value="guardar_rol">
            <input type="hidden" id="role-id" name="id" value="">
            <div class="modal-body">
                <div class="form-group">
                    <label for="role-nombre"><i class="fa-solid fa-shield"></i> Nombre del Rol</label>
                    <input type="text" id="role-nombre" name="nombre" class="form-input" placeholder="admin / barista / etc" required>
                </div>
                <div class="form-group">
                    <label for="role-descripcion"><i class="fa-solid fa-align-left"></i> Permisos y Descripción</label>
                    <textarea id="role-descripcion" name="descripcion" class="form-input" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="cerrarModalRol()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar Rol</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: CRUD Proveedor -->
<div id="modal-proveedor" class="modal-backdrop">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-provider-title"><i class="fa-solid fa-truck-field"></i> Registrar Proveedor</h3>
            <button class="close-modal" onclick="cerrarModalProveedor()">&times;</button>
        </div>
        <form action="index.php?ruta=admin&tab=proveedores" method="POST">
            <input type="hidden" name="action" value="guardar_proveedor">
            <input type="hidden" id="provider-id" name="id" value="">
            <div class="modal-body">
                <div class="form-group">
                    <label for="provider-nombre"><i class="fa-solid fa-building"></i> Razón Social / Nombre</label>
                    <input type="text" id="provider-nombre" name="nombre" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="provider-contacto"><i class="fa-solid fa-signature"></i> Persona de Contacto</label>
                    <input type="text" id="provider-contacto" name="contacto" class="form-input">
                </div>
                <div class="form-group">
                    <label for="provider-email"><i class="fa-solid fa-envelope"></i> Correo de Cotizaciones</label>
                    <input type="email" id="provider-email" name="email" class="form-input">
                </div>
                <div class="form-group">
                    <label for="provider-telefono"><i class="fa-solid fa-phone"></i> Teléfono</label>
                    <input type="text" id="provider-telefono" name="telefono" class="form-input">
                </div>
                <div class="form-group">
                    <label for="provider-direccion"><i class="fa-solid fa-location-dot"></i> Dirección Fiscal</label>
                    <input type="text" id="provider-direccion" name="direccion" class="form-input">
                </div>
                <div class="form-group">
                    <label for="provider-producto"><i class="fa-solid fa-mug-hot"></i> Producto/Materia Prima Suministrada</label>
                    <input type="text" id="provider-producto" name="producto_principal" placeholder="Granos de Café, Leche, Vasos, etc." class="form-input" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="cerrarModalProveedor()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar Proveedor</button>
            </div>
        </form>
    </div>
</div>


<!-- Scripting de Modales e Inicialización de Gráficos con Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ==========================================
// --- LÓGICA DE MODALES (MODAL BACKDROP CONTROLS) ---
// ==========================================

function abrirModalEditarPedido(button) {
    const id = button.getAttribute("data-id");
    const estado = button.getAttribute("data-estado");
    const total = button.getAttribute("data-total");
    
    document.getElementById("modal-id-label").textContent = "#" + id;
    document.getElementById("edit-id").value = id;
    document.getElementById("edit-estado").value = estado;
    document.getElementById("edit-total").value = total;
    
    document.getElementById("modal-editar-pedido").classList.add("active");
}

function cerrarModalEditarPedido() {
    document.getElementById("modal-editar-pedido").classList.remove("active");
}

// Modal CRUD Usuario
function abrirModalUsuario(btn = null) {
    if (btn) {
        document.getElementById("modal-user-title").innerHTML = "<i class='fa-solid fa-user-pen'></i> Modificar Usuario";
        document.getElementById("user-id").value = btn.getAttribute("data-id");
        document.getElementById("user-nombre").value = btn.getAttribute("data-nombre");
        document.getElementById("user-email").value = btn.getAttribute("data-email");
        document.getElementById("user-rol").value = btn.getAttribute("data-rol");
        document.getElementById("user-password").placeholder = "Dejar en blanco para mantener contraseña";
        document.getElementById("user-password").required = false;
    } else {
        document.getElementById("modal-user-title").innerHTML = "<i class='fa-solid fa-user-plus'></i> Registrar Nuevo Usuario";
        document.getElementById("user-id").value = "";
        document.getElementById("user-nombre").value = "";
        document.getElementById("user-email").value = "";
        document.getElementById("user-rol").value = "cliente";
        document.getElementById("user-password").placeholder = "Ingresa contraseña o vacío para 123456";
        document.getElementById("user-password").required = false;
    }
    document.getElementById("modal-usuario").classList.add("active");
}
function cerrarModalUsuario() {
    document.getElementById("modal-usuario").classList.remove("active");
}

// Modal CRUD Cliente
function abrirModalCliente(btn = null) {
    if (btn) {
        document.getElementById("modal-client-title").innerHTML = "<i class='fa-solid fa-user-pen'></i> Editar Ficha Cliente";
        document.getElementById("client-id").value = btn.getAttribute("data-id");
        document.getElementById("client-nombre").value = btn.getAttribute("data-nombre");
        document.getElementById("client-email").value = btn.getAttribute("data-email");
        document.getElementById("client-telefono").value = btn.getAttribute("data-telefono");
        document.getElementById("client-direccion").value = btn.getAttribute("data-direccion");
        document.getElementById("client-puntos").value = btn.getAttribute("data-puntos");
    } else {
        document.getElementById("modal-client-title").innerHTML = "<i class='fa-solid fa-user-plus'></i> Registrar Cliente";
        document.getElementById("client-id").value = "";
        document.getElementById("client-nombre").value = "";
        document.getElementById("client-email").value = "";
        document.getElementById("client-telefono").value = "";
        document.getElementById("client-direccion").value = "";
        document.getElementById("client-puntos").value = "0";
    }
    document.getElementById("modal-cliente").classList.add("active");
}
function cerrarModalCliente() {
    document.getElementById("modal-cliente").classList.remove("active");
}

// Modal CRUD Trabajador
function abrirModalTrabajador(btn = null) {
    if (btn) {
        document.getElementById("modal-worker-title").innerHTML = "<i class='fa-solid fa-id-card-clip'></i> Modificar Ficha de Trabajador";
        document.getElementById("worker-id").value = btn.getAttribute("data-id");
        document.getElementById("worker-nombre").value = btn.getAttribute("data-nombre");
        document.getElementById("worker-email").value = btn.getAttribute("data-email");
        document.getElementById("worker-telefono").value = btn.getAttribute("data-telefono");
        document.getElementById("worker-cargo").value = btn.getAttribute("data-cargo");
        document.getElementById("worker-salario").value = btn.getAttribute("data-salario");
        document.getElementById("worker-fecha").value = btn.getAttribute("data-fecha_ingreso");
    } else {
        document.getElementById("modal-worker-title").innerHTML = "<i class='fa-solid fa-id-card'></i> Registrar Trabajador";
        document.getElementById("worker-id").value = "";
        document.getElementById("worker-nombre").value = "";
        document.getElementById("worker-email").value = "";
        document.getElementById("worker-telefono").value = "";
        document.getElementById("worker-cargo").value = "";
        document.getElementById("worker-salario").value = "";
        document.getElementById("worker-fecha").value = "<?php echo date('Y-m-d'); ?>";
    }
    document.getElementById("modal-trabajador").classList.add("active");
}
function cerrarModalTrabajador() {
    document.getElementById("modal-trabajador").classList.remove("active");
}

// Modal CRUD Rol
function abrirModalRol(btn = null) {
    if (btn) {
        document.getElementById("modal-role-title").innerHTML = "<i class='fa-solid fa-user-shield'></i> Editar Detalles de Rol";
        document.getElementById("role-id").value = btn.getAttribute("data-id");
        document.getElementById("role-nombre").value = btn.getAttribute("data-nombre");
        document.getElementById("role-descripcion").value = btn.getAttribute("data-descripcion");
    } else {
        document.getElementById("modal-role-title").innerHTML = "<i class='fa-solid fa-user-lock'></i> Definir Nuevo Rol";
        document.getElementById("role-id").value = "";
        document.getElementById("role-nombre").value = "";
        document.getElementById("role-descripcion").value = "";
    }
    document.getElementById("modal-rol").classList.add("active");
}
function cerrarModalRol() {
    document.getElementById("modal-rol").classList.remove("active");
}

// Modal CRUD Proveedor
function abrirModalProveedor(btn = null) {
    if (btn) {
        document.getElementById("modal-provider-title").innerHTML = "<i class='fa-solid fa-truck-ramp-box'></i> Editar Proveedor";
        document.getElementById("provider-id").value = btn.getAttribute("data-id");
        document.getElementById("provider-nombre").value = btn.getAttribute("data-nombre");
        document.getElementById("provider-contacto").value = btn.getAttribute("data-contacto");
        document.getElementById("provider-email").value = btn.getAttribute("data-email");
        document.getElementById("provider-telefono").value = btn.getAttribute("data-telefono");
        document.getElementById("provider-direccion").value = btn.getAttribute("data-direccion");
        document.getElementById("provider-producto").value = btn.getAttribute("data-producto_principal");
    } else {
        document.getElementById("modal-provider-title").innerHTML = "<i class='fa-solid fa-truck-field'></i> Registrar Proveedor";
        document.getElementById("provider-id").value = "";
        document.getElementById("provider-nombre").value = "";
        document.getElementById("provider-contacto").value = "";
        document.getElementById("provider-email").value = "";
        document.getElementById("provider-telefono").value = "";
        document.getElementById("provider-direccion").value = "";
        document.getElementById("provider-producto").value = "";
    }
    document.getElementById("modal-proveedor").classList.add("active");
}
function cerrarModalProveedor() {
    document.getElementById("modal-proveedor").classList.remove("active");
}

// ==========================================
// --- INICIALIZACIÓN DE CHART.JS (DASHBOARD VISUAL) ---
// ==========================================
<?php if ($tab === 'dashboard' && $rol === 'admin'): 
    // Obtener datos reales de ventas de la última semana
    $ventasUltimaSemana = UsuarioModel::mdlObtenerVentasDiariasUltimaSemana();
    $labelsDias = [];
    $dataMontos = [];
    
    // Generar últimos 7 días con montos en Soles
    for ($i = 6; $i >= 0; $i--) {
        $fechaLoop = date('Y-m-d', strtotime("-$i days"));
        $diaFormat = date('d/m', strtotime("-$i days"));
        $labelsDias[] = $diaFormat;
        
        $montoDia = 0;
        foreach ($ventasUltimaSemana as $v) {
            if ($v['fecha_dia'] === $fechaLoop) {
                $montoDia = floatval($v['total']) * 3.80; // Convertir a Soles
                break;
            }
        }
        $dataMontos[] = $montoDia;
    }

    // Obtener distribución real por categorías
    $ventasCategorias = UsuarioModel::mdlObtenerVentasPorCategoria();
    $labelsCats = [];
    $dataCats = [];
    foreach ($ventasCategorias as $vc) {
        $labelsCats[] = $vc['categoria'];
        $dataCats[] = floatval($vc['total']) * 3.80;
    }
?>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Gráfico de Línea: Ventas de la última semana
    const ctxWeek = document.getElementById('salesWeekChart');
    if (ctxWeek) {
        new Chart(ctxWeek, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labelsDias); ?>,
                datasets: [{
                    label: 'Ventas Diarias (S/)',
                    data: <?php echo json_encode($dataMontos); ?>,
                    borderColor: '#c9962a',
                    backgroundColor: 'rgba(201, 150, 42, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#4e3629',
                    pointBorderColor: '#c9962a',
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(78, 54, 41, 0.05)' },
                        ticks: { color: '#7c5c4e' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#7c5c4e' }
                    }
                }
            }
        });
    }

    // 2. Gráfico Circular: Distribución por Categoría
    const ctxCat = document.getElementById('salesCategoryChart');
    if (ctxCat) {
        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($labelsCats); ?>,
                datasets: [{
                    data: <?php echo json_encode($dataCats); ?>,
                    backgroundColor: ['#4e3629', '#c9962a', '#7c5c4e', '#d4a373'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#4e3629', font: { family: 'Outfit' } }
                    }
                }
            }
        });
    }
});
<?php endif; ?>
</script>
