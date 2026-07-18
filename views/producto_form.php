<?php
// Protección de acceso: Solo el administrador puede ver este formulario
if (!isset($_SESSION["iniciarSesion"]) || !in_array($_SESSION["rol"], ["admin", "trabajador"])) {
    $_SESSION["msg_flash"] = ["tipo" => "danger", "texto" => "Acceso denegado. Permisos insuficientes."];
    header("Location: index.php?ruta=login");
    exit();
}

// Se espera que la variable $producto esté disponible si se está editando
$producto = $producto ?? null;
$isEditing = ($producto !== null && is_array($producto));

// Valores por defecto para el formulario
$id = '';
$nombre = '';
$descripcion = '';
$precioSoles = '';
$stock = 0;
$categoria = 'Bebidas';
$imagen = '';

if ($isEditing) {
    /** @var array $producto */
    $id = $producto['id'];
    $nombre = htmlspecialchars($producto['nombre']);
    $descripcion = htmlspecialchars($producto['descripcion']);
    $precioSoles = number_format($producto['precio'] * 3.80, 2, '.', '');
    $stock = intval($producto['stock']);
    $categoria = $producto['categoria'];
    $imagen = htmlspecialchars($producto['imagen']);
}
?>

<section class="auth-section" id="producto-form-seccion">
    <div class="auth-container" style="min-height: 700px;">
        <!-- Panel visual izquierdo -->
        <div class="auth-panel" style="background-image: url('assets/img/login_image.png'); background-size: cover; background-position: center;">
            <div class="auth-panel-content">
                <i class="fa-solid fa-mug-saucer auth-icon" style="color: var(--dorado-lt);"></i>
                <h2><?php echo $isEditing ? 'Editar Producto' : 'Nuevo Producto'; ?></h2>
                <p>Mantén actualizado el catálogo gourmet de Dulce Café para ofrecer siempre la mejor experiencia a nuestros comensales.</p>
                <div class="auth-features">
                    <div class="auth-feature-item"><i class="fa-solid fa-check"></i> Define precios y categorías con precisión</div>
                    <div class="auth-feature-item"><i class="fa-solid pointer fa-check"></i> Monitorea el inventario disponible (Stock)</div>
                    <div class="auth-feature-item"><i class="fa-solid fa-check"></i> Enlaza imágenes apetecibles y descriptivas</div>
                </div>
            </div>
        </div>

        <!-- Formulario de Producto -->
        <div class="auth-form-wrapper">
            <div class="auth-form-box" style="max-width: 500px;">
                <h1 class="auth-title"><?php echo $isEditing ? 'Gestionar Ítem' : 'Agregar Ítem'; ?></h1>
                <p class="auth-subtitle">Completa los campos del menú a continuación</p>

                <form action="index.php?ruta=producto" method="POST" id="form-producto">
                    <?php if ($isEditing): ?>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="nombre"><i class="fa-solid fa-tag"></i> Nombre del Producto</label>
                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            class="form-input"
                            placeholder="Ej: Espresso Macchiato Doble"
                            value="<?php echo $nombre; ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="descripcion"><i class="fa-solid fa-align-left"></i> Descripción Gourmet</label>
                        <textarea
                            id="descripcion"
                            name="descripcion"
                            class="form-input"
                            placeholder="Detalles sobre el aroma, sabor o ingredientes..."
                            rows="3"
                            style="resize: vertical; font-family: inherit;"
                            required
                        ><?php echo $descripcion; ?></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="precio"><i class="fa-solid fa-coins"></i> Precio (S/)</label>
                            <input
                                type="number"
                                id="precio"
                                name="precio"
                                class="form-input"
                                placeholder="0.00"
                                step="0.01"
                                min="0.01"
                                value="<?php echo $precioSoles; ?>"
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label for="stock"><i class="fa-solid fa-boxes-stacked"></i> Stock Inicial</label>
                            <input
                                type="number"
                                id="stock"
                                name="stock"
                                class="form-input"
                                placeholder="10"
                                min="0"
                                value="<?php echo $stock; ?>"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="categoria"><i class="fa-solid fa-folder-open"></i> Categoría del Menú</label>
                        <select id="categoria" name="categoria" class="form-input" required>
                            <option value="Bebidas" <?php echo $categoria === 'Bebidas' ? 'selected' : ''; ?>>Bebidas</option>
                            <option value="Postres" <?php echo $categoria === 'Postres' ? 'selected' : ''; ?>>Postres</option>
                            <option value="Desayuno" <?php echo $categoria === 'Desayuno' ? 'selected' : ''; ?>>Desayuno</option>
                            <option value="Otros" <?php echo $categoria === 'Otros' ? 'selected' : ''; ?>>Otros</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="imagen"><i class="fa-solid fa-image"></i> Enlace de la Imagen (URL)</label>
                        <input
                            type="url"
                            id="imagen"
                            name="imagen"
                            class="form-input"
                            placeholder="https://ejemplo.com/imagen.jpg"
                            value="<?php echo $imagen; ?>"
                            required
                        >
                    </div>

                    <button type="submit" class="btn-auth-submit" style="margin-top: 1rem; border: none; cursor: pointer;">
                        <i class="fa-solid fa-floppy-disk"></i> <?php echo $isEditing ? 'Guardar Cambios' : 'Crear Producto'; ?>
                    </button>
                </form>

                <?php if ($isEditing): ?>
                    <form action="index.php?ruta=producto" method="POST" style="margin-top: 0.5rem;" onsubmit="return confirmarEliminarProducto(event)">
                        <input type="hidden" name="action" value="eliminar">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <button type="submit" class="btn-auth-secondary" style="border-color: var(--rojo); color: var(--rojo); background: rgba(192,57,43,0.03);">
                            <i class="fa-solid fa-trash-can"></i> Eliminar Ítem del Menú
                        </button>
                    </form>
                <?php endif; ?>

                <div class="auth-divider"><span>Acciones rápidas</span></div>

                <a href="index.php?ruta=catalogo" class="btn-auth-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Volver al Catálogo
                </a>
            </div>
        </div>
    </div>
</section>

<script>
function confirmarEliminarProducto(event) {
    if (!confirm("¿Estás completamente seguro de que deseas eliminar este producto del menú? Se borrará permanentemente de la base de datos.")) {
        event.preventDefault();
        return false;
    }
    return true;
}
</script>
