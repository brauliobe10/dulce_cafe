<?php

require_once __DIR__ . '/../models/UsuarioModel.php';

class UsuarioController {
    
    //Procesa el registro seguro de un cliente.
     
    public function ctrRegistroUsuario() {
        if (isset($_POST["regNombre"]) && isset($_POST["regEmail"]) && isset($_POST["regPassword"])) {
            
            // 1. Mitigación de CSRF: Verificar que el token sea correcto
            if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Falsificación de petición detectada (Error CSRF)."];
                header("Location: index.php?ruta=registro");
                exit();
            }

            // 2. Mitigación de XSS: Sanitizar entradas de usuario y validar formato
            $nombre = trim($_POST["regNombre"]);
            $email = filter_var(trim($_POST["regEmail"]), FILTER_SANITIZE_EMAIL);
            $password = $_POST["regPassword"];

            if (empty($nombre) || empty($email) || empty($password)) {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Todos los campos son obligatorios."];
                header("Location: index.php?ruta=registro");
                exit();
            }

            // Validar expresiones regulares básicas para seguridad adicional (XSS / Code Injection)
            if (!preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ ]+$/', $nombre)) {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "El nombre contiene caracteres no válidos."];
                header("Location: index.php?ruta=registro");
                exit();
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "El formato del correo electrónico no es válido."];
                header("Location: index.php?ruta=registro");
                exit();
            }

            // 3. Validar si el correo ya existe
            $tabla = "usuarios";
            $usuarioExistente = UsuarioModel::mdlMostrarUsuario($tabla, "email", $email);
            if ($usuarioExistente) {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "El correo electrónico ya está registrado."];
                header("Location: index.php?ruta=registro");
                exit();
            }

            // 4. Encriptar contraseña de forma segura con BCRYPT
            $passwordEncriptado = password_hash($password, PASSWORD_BCRYPT);

            // 5. Enviar al modelo para registrar
            $datos = [
                "nombre" => htmlspecialchars($nombre), // Sanitización XSS
                "email" => $email,
                "password" => $passwordEncriptado,
                "rol" => "cliente"
            ];

            $respuesta = UsuarioModel::mdlRegistroUsuario($tabla, $datos);

            if ($respuesta) {
                // Post/refresh/redirect pattern (Evitar duplicidades al refrescar)
                $_SESSION["msg_flash"] = ["tipo" => "success", "texto" => "Registro exitoso. Inicia sesión con tus credenciales."];
                header("Location: index.php?ruta=login");
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error al registrar el usuario en el sistema."];
                header("Location: index.php?ruta=registro");
            }
            exit();
        }
    }

    /**
     * Procesa el inicio de sesión seguro y controla la mitigación de ataques de fuerza bruta.
     */
    public function ctrIngresoUsuario() {
        if (isset($_POST["logEmail"]) && isset($_POST["logPassword"])) {

            // 1. Mitigación de CSRF
            if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error de token de seguridad CSRF."];
                header("Location: index.php?ruta=login");
                exit();
            }

            $email = filter_var(trim($_POST["logEmail"]), FILTER_SANITIZE_EMAIL);
            $password = $_POST["logPassword"];
            $tabla = "usuarios";

            // Verificar que la conexión a la base de datos esté disponible
            $pdo = Conexion::conectar();
            if ($pdo === null) {
                $errorBD = Conexion::obtenerUltimoError();
                $_SESSION["msg_flash"] = [
                    "tipo" => "danger",
                    "texto" => "Error interno: no se pudo conectar a MySQL. " . ($errorBD ? "Detalle: $errorBD" : "Verifique XAMPP y la configuración de la DB." )
                ];
                header("Location: index.php?ruta=login");
                exit();
            }

            // 2. Obtener datos del usuario mediante PDO (evitando SQL Injection)
            $usuario = UsuarioModel::mdlMostrarUsuario($tabla, "email", $email);

            if ($usuario) {
                // 3. Mitigación de Fuerza Bruta: Verificar si la cuenta está bloqueada temporalmente
                if ($usuario["intentos_fallidos"] >= 5) {
                    $fechaActual = new DateTime();
                    $ultimoIntento = new DateTime($usuario["ultimo_intento"]);
                    $intervalo = $fechaActual->diff($ultimoIntento);
                    $minutosTranscurridos = ($intervalo->days * 24 * 60) + ($intervalo->h * 60) + $intervalo->i;

                    if ($minutosTranscurridos < 5) {
                        $minutosRestantes = 5 - $minutosTranscurridos;
                        $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Cuenta temporalmente bloqueada por exceso de intentos fallidos. Intente de nuevo en $minutosRestantes minutos."];
                        header("Location: index.php?ruta=login");
                        exit();
                    } else {
                        // El bloqueo ha expirado, reiniciamos los intentos
                        UsuarioModel::mdlActualizarUsuario($tabla, "intentos_fallidos", 0, "id", $usuario["id"]);
                        $usuario["intentos_fallidos"] = 0;
                    }
                }

                // 4. Verificar la contraseña encriptada
                if (password_verify($password, $usuario["password"])) {
                    
                    // Contraseña correcta: Reiniciar los intentos fallidos a 0
                    UsuarioModel::mdlActualizarUsuario($tabla, "intentos_fallidos", 0, "id", $usuario["id"]);

                    // Regenerar el ID de sesión para prevenir fijación de sesión
                    session_regenerate_id(true);

                    // Inicializar variables de sesión seguras
                    $_SESSION["iniciarSesion"] = "ok";
                    $_SESSION["id"] = $usuario["id"];
                    $_SESSION["nombre"] = $usuario["nombre"];
                    $_SESSION["email"] = $usuario["email"];
                    $_SESSION["rol"] = $usuario["rol"];

                    // Redirección segura Post/refresh/redirect en base al Rol
                    if ($usuario["rol"] === "admin") {
                        header("Location: index.php?ruta=admin");
                    } else {
                        header("Location: index.php?ruta=catalogo");
                    }
                    exit();

                } else {
                    // Contraseña incorrecta: Incrementar intentos y actualizar marca de tiempo
                    $intentos = $usuario["intentos_fallidos"] + 1;
                    $fechaHora = date("Y-m-d H:i:s");
                    
                    UsuarioModel::mdlActualizarUsuario($tabla, "intentos_fallidos", $intentos, "id", $usuario["id"]);
                    UsuarioModel::mdlActualizarUsuario($tabla, "ultimo_intento", $fechaHora, "id", $usuario["id"]);

                    $intentosRestantes = 5 - $intentos;
                    if ($intentosRestantes > 0) {
                        $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Contraseña incorrecta. Te quedan $intentosRestantes intentos antes del bloqueo."];
                    } else {
                        $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Has alcanzado el límite de intentos. Cuenta bloqueada temporalmente por 5 minutos."];
                    }
                }
            } else {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "El correo electrónico no está registrado en Dulce Café."];
            }

            header("Location: index.php?ruta=login");
            exit();
        }
    }

    /**
     * NUEVO: Procesa el acceso rápido de Invitado (Sin registro formal de credenciales)
     */
    public function ctrIngresoInvitado() {
        if (isset($_POST["guestDni"]) && isset($_POST["guestNombre"]) && isset($_POST["guestCelular"])) {

            // 1. Mitigación de CSRF
            if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Error de token de seguridad CSRF."];
                header("Location: index.php?ruta=login");
                exit();
            }

            // 2. Sanitizar y limpiar entradas contra ataques XSS
            $dni = trim($_POST["guestDni"]);
            $nombre = trim($_POST["guestNombre"]);
            $celular = trim($_POST["guestCelular"]);

            // 3. Validar longitud y formatos (DNI: 8 dígitos, Celular: 9 dígitos)
            if (!preg_match('/^\d{8}$/', $dni)) {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "El DNI debe tener exactamente 8 dígitos numéricos."];
                header("Location: index.php?ruta=login");
                exit();
            }

            if (!preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ ]+$/', $nombre)) {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "El nombre ingresado contiene caracteres no válidos."];
                header("Location: index.php?ruta=login");
                exit();
            }

            if (!preg_match('/^9\d{8}$/', $celular)) {
                $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "El celular debe empezar con 9 y contener 9 dígitos."];
                header("Location: index.php?ruta=login");
                exit();
            }

            // Regenerar ID de sesión por seguridad
            session_regenerate_id(true);

            // 4. Iniciar sesión segura con el Rol 'invitado'
            $_SESSION["iniciarSesion"] = "ok";
            $_SESSION["id"] = "INV-" . $dni; // ID temporal único basado en su DNI
            $_SESSION["nombre"] = htmlspecialchars($nombre);
            $_SESSION["dni"] = $dni;
            $_SESSION["celular"] = $celular;
            $_SESSION["rol"] = "invitado";

            // Redirigir directamente al catálogo
            header("Location: index.php?ruta=catalogo");
            exit();
        }
    }

    /**
     * Cierra la sesión activa de forma segura.
     */
    public static function ctrCerrarSesion() {
        // Vaciar la variable $_SESSION
        $_SESSION = [];

        // Borrar la cookie de sesión si existe en el navegador
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }

        // Destruir sesión en el servidor
        session_destroy();

        // Redirección segura
        header("Location: index.php?ruta=catalogo");
        exit();
    }
}