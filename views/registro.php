<?php
// Generar token CSRF único si no existe
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
// Leer y limpiar mensaje flash de sesión si existe
$msgFlash = $_SESSION["msg_flash"] ?? null;
unset($_SESSION["msg_flash"]);
?>

<section class="auth-section" id="registro-seccion">
    <div class="auth-container">
        <!-- Panel visual izquierdo -->
        <div class="auth-panel" style="background-image: url('assets/img/login_image.png'); background-size: cover; background-position: center;">
            <div class="auth-panel-content">
                <i class="fa-solid fa-mug-hot auth-icon"></i>
                <h2>Únete a Dulce Café</h2>
                <p>Crea tu cuenta gratuita y accede a un universo de sabores artesanales. Ordena en línea con total comodidad y seguridad.</p>
                <div class="auth-features">
                    <div class="auth-feature-item"><i class="fa-solid fa-check"></i> Registro rápido y seguro</div>
                    <div class="auth-feature-item"><i class="fa-solid fa-check"></i> Datos protegidos con encriptación BCRYPT</div>
                    <div class="auth-feature-item"><i class="fa-solid fa-check"></i> Realiza pedidos en minutos</div>
                </div>
            </div>
        </div>

        <!-- Formulario de Registro -->
        <div class="auth-form-wrapper">
            <div class="auth-form-box">
                <h1 class="auth-title">Crear Cuenta</h1>
                <p class="auth-subtitle">Completa el formulario para registrarte</p>

                <?php if ($msgFlash): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($msgFlash['tipo']); ?>">
                        <i class="fa-solid <?php echo $msgFlash['tipo'] === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
                        <?php echo htmlspecialchars($msgFlash['texto']); ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?ruta=registro" method="POST" id="form-registro" novalidate>
                    <!-- Token CSRF oculto (Protección CSRF) -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="form-group">
                        <label for="regNombre"><i class="fa-solid fa-user"></i> Nombre Completo</label>
                        <input
                            type="text"
                            id="regNombre"
                            name="regNombre"
                            class="form-input"
                            placeholder="Tu nombre completo"
                            required
                            autocomplete="name"
                            maxlength="100"
                        >
                    </div>

                    <div class="form-group">
                        <label for="regEmail"><i class="fa-solid fa-envelope"></i> Correo Electrónico</label>
                        <input
                            type="email"
                            id="regEmail"
                            name="regEmail"
                            class="form-input"
                            placeholder="correo@ejemplo.com"
                            required
                            autocomplete="email"
                        >
                    </div>

                    <div class="form-group">
                        <label for="regPassword"><i class="fa-solid fa-lock"></i> Contraseña</label>
                        <div class="input-password-wrapper">
                            <input
                                type="password"
                                id="regPassword"
                                name="regPassword"
                                class="form-input"
                                placeholder="Mínimo 8 caracteres"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >
                            <button type="button" class="toggle-password" data-target="regPassword" title="Ver contraseña">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <!-- Indicador de fortaleza de contraseña -->
                        <div class="password-strength" id="pwd-strength">
                            <div class="pwd-bar"><span id="pwd-bar-fill"></span></div>
                            <small id="pwd-strength-text">Ingresa una contraseña...</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="regPasswordConfirm"><i class="fa-solid fa-shield-halved"></i> Confirmar Contraseña</label>
                        <div class="input-password-wrapper">
                            <input
                                type="password"
                                id="regPasswordConfirm"
                                name="regPasswordConfirm"
                                class="form-input"
                                placeholder="Repite tu contraseña"
                                required
                                autocomplete="new-password"
                            >
                            <button type="button" class="toggle-password" data-target="regPasswordConfirm" title="Ver contraseña">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-auth-submit" id="btn-registro">
                        <i class="fa-solid fa-user-plus"></i> Registrarme en Dulce Café
                    </button>
                </form>

                <div class="auth-divider"><span>¿Ya tienes una cuenta?</span></div>

                <a href="index.php?ruta=login" class="btn-auth-secondary">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Iniciar sesión
                </a>
            </div>
        </div>
    </div>
</section>

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

// Indicador visual de fortaleza de contraseña
const pwdInput = document.getElementById("regPassword");
const pwdBarFill = document.getElementById("pwd-bar-fill");
const pwdStrengthText = document.getElementById("pwd-strength-text");

if (pwdInput) {
    pwdInput.addEventListener("input", function() {
        const val = this.value;
        let fuerza = 0;
        if (val.length >= 8) fuerza++;
        if (/[A-Z]/.test(val)) fuerza++;
        if (/[0-9]/.test(val)) fuerza++;
        if (/[^A-Za-z0-9]/.test(val)) fuerza++;

        const colores = ["#e74c3c", "#e67e22", "#f1c40f", "#2ecc71"];
        const textos = ["Muy débil", "Débil", "Moderada", "Fuerte"];

        pwdBarFill.style.width = (fuerza * 25) + "%";
        pwdBarFill.style.backgroundColor = colores[fuerza - 1] || "#e0e0e0";
        pwdStrengthText.textContent = textos[fuerza - 1] || "Ingresa una contraseña...";
    });
}

// Validar que las contraseñas coincidan antes de enviar
document.getElementById("form-registro").addEventListener("submit", function(e) {
    const pwd = document.getElementById("regPassword").value;
    const confirm = document.getElementById("regPasswordConfirm").value;
    if (pwd !== confirm) {
        e.preventDefault();
        alert("Las contraseñas no coinciden. Por favor verifica.");
    }
});
</script>
