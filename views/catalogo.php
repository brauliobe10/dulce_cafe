<?php
// Vista de catálogo de productos para clientes
// Se espera que la variable $productos esté disponible (array de productos)
?>
<section class="catalogo-section" id="catalogo">
    <div class="catalogo-header">
        <h1 class="catalogo-title"><i class="fa-solid fa-coffee"></i> Nuestro Menú</h1>
        <p class="catalogo-subtitle">Explora la variedad de bebidas y alimentos que Dulce Café tiene para ofrecer.</p>
        
        <?php if (isset($_SESSION['iniciarSesion']) && $_SESSION['rol'] === 'admin'): ?>
            <div style="margin-top: 1.5rem;">
                <a href="index.php?ruta=producto" class="btn-hero" style="padding: 0.6rem 1.5rem; font-size: 0.9rem; border: none; cursor: pointer;">
                    <i class="fa-solid fa-plus"></i> Agregar producto
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php
    $msgFlash = $_SESSION["msg_flash"] ?? null;
    unset($_SESSION["msg_flash"]);
    if ($msgFlash): ?>
        <div class="alert alert-<?php echo htmlspecialchars($msgFlash['tipo']); ?>" style="max-width: 600px; margin: 0 auto 2rem;">
            <i class="fa-solid <?php echo $msgFlash['tipo'] === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
            <?php echo htmlspecialchars($msgFlash['texto']); ?>
        </div>
    <?php endif; ?>

    <div class="catalogo-grid">
        <?php if (!empty($productos)): ?>
            <?php foreach ($productos as $prod): ?>
                <article class="producto-card">
                    <?php
                        $imgSrc = (strpos($prod['imagen'], 'http') === 0)
                            ? htmlspecialchars($prod['imagen'])
                            : 'assets/img/' . htmlspecialchars($prod['imagen']);
                    ?>
                    <img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($prod['nombre']); ?>" class="producto-imagen" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?q=80&w=400'" />
                    <h2 class="producto-nombre"><?php echo htmlspecialchars($prod['nombre']); ?></h2>
                    <p class="producto-descripcion"><?php echo nl2br(htmlspecialchars($prod['descripcion'])); ?></p>
                    <div class="producto-footer">
                        <div>
                            <span class="producto-precio">S/<?php echo number_format($prod['precio'] * 3.80, 2); ?></span>
                            <span class="producto-stock">Stock: <?php echo htmlspecialchars($prod['stock']); ?> uds.</span>
                        </div>
                        <?php if (isset($_SESSION['iniciarSesion']) && $_SESSION['rol'] === 'admin'): ?>
                            <a href="index.php?ruta=producto&id=<?php echo $prod['id']; ?>" class="btn-agregar btn-editar-prod" style="border-color: var(--dorado); color: var(--dorado); display: inline-flex; align-items: center; gap: 0.4rem;">
                                <i class="fa-solid fa-pen-to-square"></i> Editar
                            </a>
                        <?php else: ?>
                            <!-- Único control de cantidad tipo "Pill" (Pastilla marrón) idéntico a tu diseño deseado -->
                            <div class="control-cantidad-pills" style="display: inline-flex; align-items: center; background-color: #543c2b; padding: 5px 8px; border-radius: 8px; gap: 10px;">
                                <button class="btn-cantidad-menos" 
                                        data-id="<?php echo $prod['id']; ?>" 
                                        style="background-color: #a67f5d; color: white; border: none; width: 28px; height: 28px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 1.1rem; display: flex; align-items: center; justify-content: center;">-</button>
                                
                                <span class="cantidad-display" 
                                      id="cant-display-<?php echo $prod['id']; ?>" 
                                      style="color: white; font-weight: bold; min-width: 20px; text-align: center; font-size: 1rem; user-select: none;">0</span>
                                
                                <button class="btn-cantidad-mas" 
                                        data-id="<?php echo $prod['id']; ?>" 
                                        data-nombre="<?php echo htmlspecialchars($prod['nombre']); ?>"
                                        data-precio="<?php echo $prod['precio']; ?>"
                                        data-stock="<?php echo $prod['stock']; ?>" 
                                        style="background-color: #a67f5d; color: white; border: none; width: 28px; height: 28px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 1.1rem; display: flex; align-items: center; justify-content: center;">+</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="catalogo-vacio">Aún no hay productos disponibles. Consulte al administrador.</p>
        <?php endif; ?>
    </div>
</section>