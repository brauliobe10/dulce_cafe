<?php
// Generar token CSRF único si no existe
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
// Leer y limpiar mensaje flash de sesión si existe
$msgFlash = $_SESSION["msg_flash"] ?? null;
unset($_SESSION["msg_flash"]);
?>

<section class="auth-section" id="login-seccion">
    <div class="auth-container">
        <!-- Panel visual izquierdo -->
        <div class="auth-panel" style="background-image: url('assets/img/login_image.png'); background-size: cover; background-position: center;">
            <div class="auth-panel-content">
                <i class="fa-solid fa-mug-hot auth-icon"></i>
                <h2>Bienvenido de vuelta</h2>
                <p>Inicia sesión para acceder a tu historial de pedidos y disfrutar de una experiencia de compra personalizada en Dulce Café.</p>
                <div class="auth-features">
                    <div class="auth-feature-item"><i class="fa-solid fa-check"></i> Seguimiento de pedidos en tiempo real</div>
                    <div class="auth-feature-item"><i class="fa-solid fa-check"></i> Historial de compras completo</div>
                    <div class="auth-feature-item"><i class="fa-solid fa-check"></i> Acceso a promociones exclusivas</div>
                </div>
            </div>
        </div>

        <!-- Formulario de Inicio de Sesión -->
        <div class="auth-form-wrapper">
            <div class="auth-form-box">
                <h1 class="auth-title">Iniciar Sesión</h1>
                <p class="auth-subtitle">Ingresa con tu correo y contraseña</p>

                <?php if ($msgFlash): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($msgFlash['tipo']); ?>">
                        <i class="fa-solid <?php echo $msgFlash['tipo'] === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
                        <?php echo htmlspecialchars($msgFlash['texto']); ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?ruta=login" method="POST" id="form-login" novalidate>
                    <!-- Token CSRF oculto (Protección CSRF) -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="form-group">
                        <label for="logEmail"><i class="fa-solid fa-envelope"></i> Correo Electrónico</label>
                        <input
                            type="email"
                            id="logEmail"
                            name="logEmail"
                            class="form-input"
                            placeholder="correo@ejemplo.com"
                            required
                            autocomplete="email"
                        >
                    </div>

                    <div class="form-group">
                        <label for="logPassword"><i class="fa-solid fa-lock"></i> Contraseña</label>
                        <div class="input-password-wrapper">
                            <input
                                type="password"
                                id="logPassword"
                                name="logPassword"
                                class="form-input"
                                placeholder="Tu contraseña"
                                required
                                autocomplete="current-password"
                            >
                            <button type="button" class="toggle-password" data-target="logPassword" title="Ver contraseña">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-auth-submit" id="btn-login">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Ingresar a Dulce Café
                    </button>
                </form>

                <div class="auth-divider"><span>¿Aún no tienes cuenta?</span></div>

                <a href="index.php?ruta=registro" class="btn-auth-secondary">
                    <i class="fa-solid fa-user-plus"></i> Crear una cuenta gratis
                </a>

                <!-- NUEVO: Botón Entrar como Invitado -->
                <button type="button" class="btn-auth-guest" id="btn-open-guest" style="width: 100%; margin-top: 12px; background-color: #6f5d57; color: white; border: none; padding: 12px; border-radius: 8px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px; transition: background 0.3s;">
                    <i class="fa-solid fa-user-secret"></i> Entrar como Invitado
                </button>
            </div>
        </div>
    </div>
</section>

<!-- NUEVO: Modal de Acceso Express (Invitado) -->
<div id="modal-guest" class="modal-guest-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div class="modal-guest-content" style="background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 450px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); position: relative;">
        
        <button type="button" id="btn-close-guest" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 20px; cursor: pointer; color: #999;">&times;</button>
        
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="color: #4a342e; margin-bottom: 5px; font-size: 22px; font-weight: bold; display: flex; align-items: center; justify-content: center; gap: 10px;">
                <i class="fa-solid fa-user-tag" style="color: #8c6239;"></i> Datos de Acceso Express
            </h2>
            <p style="color: #777; font-size: 13px;">Ingresa tus datos para pedir al instante sin necesidad de contraseñas.</p>
        </div>

        <form action="index.php?ruta=login-invitado" method="POST" id="form-guest-login">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="guestDni" style="display: block; margin-bottom: 6px; font-weight: 600; color: #4a342e; font-size: 14px;">Número de DNI:</label>
                <input type="text" id="guestDni" name="guestDni" class="form-input" placeholder="8 dígitos" required maxlength="8" pattern="\d{8}" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="guestNombre" style="display: block; margin-bottom: 6px; font-weight: 600; color: #4a342e; font-size: 14px;">Nombres y Apellidos:</label>
                <input type="text" id="guestNombre" name="guestNombre" class="form-input" placeholder="Ej. Juan Pérez" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label for="guestCelular" style="display: block; margin-bottom: 6px; font-weight: 600; color: #4a342e; font-size: 14px;">Número de Celular:</label>
                <input type="text" id="guestCelular" name="guestCelular" class="form-input" placeholder="Ej. 987654321" required maxlength="9" pattern="9\d{8}" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
            </div>

            <button type="submit" class="btn-auth-submit" style="width: 100%; background-color: #8c6239; color: white; padding: 12px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: background 0.3s;">
                Ingresar al Catálogo &rarr;
            </button>
        </form>
    </div>
</div>

<script>
// Toggle para mostrar/ocultar contraseña
document.querySelectorAll(".toggle-password").forEach(btn => {
    btn.addEventListener("click", function() {
        const targetId = this.getAttribute("data-target");
        const input = document.getElementById(targetId);
        const icon = this.querySelector("i");
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.replace("fa-eye-slash", "fa-eye");
        }
    });
});

// Controladores del Modal de Invitado
const btnOpenGuest = document.getElementById('btn-open-guest');
const btnCloseGuest = document.getElementById('btn-close-guest');
const modalGuest = document.getElementById('modal-guest');

if (btnOpenGuest && modalGuest) {
    btnOpenGuest.addEventListener('click', () => {
        modalGuest.style.display = 'flex';
    });
}

if (btnCloseGuest && modalGuest) {
    btnCloseGuest.addEventListener('click', () => {
        modalGuest.style.display = 'none';
    });
}

// Cerrar modal al hacer clic afuera del contenedor blanco
window.addEventListener('click', (e) => {
    if (e.target === modalGuest) {
        modalGuest.style.display = 'none';
    }
});

// Forzar solo números en DNI y Celular
document.getElementById('guestDni').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
});
document.getElementById('guestCelular').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>