<?php
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';

class PlantillaController {
    // Carga la plantilla principal y delega la vista según la ruta
    public function ctrCargarPlantilla() {
        // Determinar ruta solicitada (index.php?ruta=xxx)
        $ruta = isset($_GET['ruta']) ? $_GET['ruta'] : 'inicio';

        // Rutas públicas y protegidas (todas las rutas válidas del sistema, incluyendo login-invitado)
        $publicas = ['inicio', 'catalogo', 'producto', 'login', 'login-invitado', 'registro', 'logout', 'admin', 'reporte_excel', 'reporte_pdf'];

        if (!in_array($ruta, $publicas)) {
            // Si la ruta no está permitida, redirigir a inicio
            $ruta = 'inicio';
        }

        // Incluir encabezado común
        include __DIR__ . '/../views/template.php';

        // Cargar contenido específico
        switch ($ruta) {
            case 'inicio':
                include __DIR__ . '/../views/inicio.php';
                break;
            case 'catalogo':
                // Usa controlador de productos para obtener datos
                $controller = new ProductoController();
                $controller->listar();
                break;
            case 'producto':
                $id = $_GET['id'] ?? null;
                $controller = new ProductoController();
                $controller->formulario($id);
                break;
            case 'login':
                // Procesar POST de login, luego mostrar vista
                $usuarioCtrl = new UsuarioController();
                $usuarioCtrl->ctrIngresoUsuario();
                include __DIR__ . '/../views/login.php';
                break;
            case 'login-invitado':
                // Procesar POST de login rápido del invitado (redirige internamente si tiene éxito)
                $usuarioCtrl = new UsuarioController();
                $usuarioCtrl->ctrIngresoInvitado();
                // Si falla o se intenta acceder por GET, lo mandamos de vuelta al Login
                include __DIR__ . '/../views/login.php';
                break;
            case 'registro':
                // Procesar POST de registro, luego mostrar vista
                $usuarioCtrl = new UsuarioController();
                $usuarioCtrl->ctrRegistroUsuario();
                include __DIR__ . '/../views/registro.php';
                break;
            case 'logout':
                // Cerrar sesión (redirige internamente)
                UsuarioController::ctrCerrarSesion();
                break;
            case 'admin':
                include __DIR__ . '/../views/admin.php';
                break;
            case 'reporte_excel':
                include __DIR__ . '/../views/reporte_excel.php';
                break;
            case 'reporte_pdf':
                include __DIR__ . '/../views/reporte_pdf.php';
                break;
            default:
                include __DIR__ . '/../views/404.php';
        }

        // Incluir pie de página común
        include __DIR__ . '/../views/footer.php';
    }
}
?>