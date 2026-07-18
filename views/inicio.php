<?php
// Vista de la página principal — Dulce Café
?>
<section class="hero" id="inicio-hero">
    <div class="hero-content">
        <p class="hero-tagline"><i class="fa-solid fa-mug-hot"></i> Artesanal &amp; Premium</p>
        <h1>Bienvenido a <span>Dulce Café</span></h1>
        <p>Descubre nuestra selección de bebidas y postres artesanales preparados con los mejores ingredientes. Una experiencia de sabor única en cada sorbo.</p>
        <a href="index.php?ruta=catalogo" class="btn-hero">
            <i class="fa-solid fa-utensils"></i> Ver Nuestro Menú
        </a>
    </div>
</section>

<section class="features-section" id="features">
    <div class="features-container">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-seedling"></i>
                </div>
                <h3>Ingredientes Naturales</h3>
                <p>Seleccionamos los mejores granos y materias primas de origen sostenible para garantizar calidad premium.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-hand-sparkles"></i>
                </div>
                <h3>Preparación Artesanal</h3>
                <p>Cada bebida y postre es preparado con técnicas tradicionales y el cuidado que merece cada cliente.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
                <h3>Pedidos Fáciles</h3>
                <p>Ordena en línea de forma rápida y segura. Tu pedido favorito con solo unos clics desde donde estés.</p>
            </div>
        </div>
    </div>
</section>

<section class="cta-section" id="cta">
    <div class="cta-container">
        <h2>¿Listo para disfrutar?</h2>
        <p>Regístrate hoy y recibe acceso a promociones exclusivas para miembros.</p>
        <div class="cta-buttons">
            <a href="index.php?ruta=catalogo" class="btn-cta-primary">
                <i class="fa-solid fa-coffee"></i> Explorar Menú
            </a>
            <?php if (!isset($_SESSION['iniciarSesion'])): ?>
            <a href="index.php?ruta=registro" class="btn-cta-secondary">
                <i class="fa-solid fa-user-plus"></i> Crear Cuenta Gratis
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>
