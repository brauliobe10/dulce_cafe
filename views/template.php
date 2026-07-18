<?php
// Plantilla base del sitio (header y apertura de body)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dulce Café — Cafetería artesanal con las mejores bebidas y postres premium. Ordena en línea fácil y rápido.">
    <title>Dulce Café — <?php echo ucfirst(isset($_GET['ruta']) ? $_GET['ruta'] : 'Inicio'); ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <!-- Estilos propios -->
    <link rel="stylesheet" href="assets/css/estilos.css?v=<?php echo filemtime('assets/css/estilos.css'); ?>">
    <!-- JS propio (diferido para no bloquear el renderizado) -->
    <script defer src="assets/js/app.js"></script>
</head>
<body>
    <header class="site-header" id="top">
        <nav class="nav-bar">
            <a class="logo" href="index.php?ruta=inicio">
                <i class="fa-solid fa-mug-hot"></i> Dulce Café
            </a>
            <ul class="nav-links">
                <li><a href="index.php?ruta=inicio">Inicio</a></li>
                <li><a href="index.php?ruta=catalogo">Catálogo</a></li>
                <?php if (isset($_SESSION['iniciarSesion']) && in_array($_SESSION['rol'], ['admin', 'trabajador'])): ?>
                    <li><a href="index.php?ruta=admin">Admin</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION['iniciarSesion'])): ?>
                    <!-- El carrito es visible para clientes, administradores, trabajadores e invitados -->
                    <li>
                        <button class="cart-indicator" id="btn-carrito" title="Ver carrito de compras">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <span class="cart-count">0</span>
                        </button>
                    </td>
                    <li>
                        <span class="nav-user">
                            <i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                            <?php if ($_SESSION['rol'] === 'invitado'): ?>
                                <small style="background-color: #8c6239; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; margin-left: 5px; font-weight: bold; text-transform: uppercase;">Invitado</small>
                            <?php endif; ?>
                        </span>
                    </li>
                    <li><a href="index.php?ruta=logout" class="btn-logout"><i class="fa-solid fa-arrow-right-from-bracket"></i> Salir</a></li>
                <?php else: ?>
                    <li><a href="index.php?ruta=login">Iniciar Sesión</a></li>
                    <li><a href="index.php?ruta=registro" class="btn-cta">Registrarse</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main class="site-main">
        <!-- El contenido específico de cada ruta se inserta aquí -->