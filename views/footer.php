    </main><!-- /site-main -->

    <footer class="site-footer" id="footer">
        <div class="footer-container">
            <div class="footer-col">
                <h3><i class="fa-solid fa-mug-hot"></i> Dulce Café</h3>
                <p>Tu cafetería artesanal de confianza. Disfruta de los mejores sabores preparados con ingredientes de calidad premium seleccionados con amor.</p>
            </div>
            <div class="footer-col">
                <h3>Navegación</h3>
                <ul>
                    <li><a href="index.php?ruta=inicio"><i class="fa-solid fa-house"></i> Inicio</a></li>
                    <li><a href="index.php?ruta=catalogo"><i class="fa-solid fa-utensils"></i> Catálogo</a></li>
                    <?php if (!isset($_SESSION['iniciarSesion'])): ?>
                    <li><a href="index.php?ruta=login"><i class="fa-solid fa-arrow-right-to-bracket"></i> Iniciar Sesión</a></li>
                    <li><a href="index.php?ruta=registro"><i class="fa-solid fa-user-plus"></i> Registrarse</a></li>
                    <?php else: ?>
                    <li><a href="index.php?ruta=logout"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Contacto</h3>
                <ul>
                    <li><i class="fa-solid fa-location-dot"></i> Av. Principal 123, Ciudad</li>
                    <li><i class="fa-solid fa-phone"></i> +1 (555) 123-4567</li>
                    <li><i class="fa-solid fa-envelope"></i> hola@dulcecafe.com</li>
                    <li><i class="fa-solid fa-clock"></i> Lun–Sáb: 7am – 9pm</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Dulce Café — Todos los derechos reservados. Hecho con <i class="fa-solid fa-heart" style="color:#d4a373;"></i> y mucho café.</p>
        </div>
    </footer>

    <!-- Toast de notificación flotante -->
    <div class="notificacion" id="toast-notificacion" role="status" aria-live="polite">
        <i class="fa-solid fa-circle-check notificacion-icono"></i>
        <span id="notificacion-texto">Producto añadido</span>
    </div>

</body>
</html>
