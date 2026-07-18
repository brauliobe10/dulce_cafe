<?php
// Protección de acceso: Solo el administrador puede ver y generar boletas
if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["rol"] !== "admin") {
    header("Location: index.php?ruta=login");
    exit();
}

require_once __DIR__ . '/../models/UsuarioModel.php';

// Obtener el ID del pedido
$idPedido = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
if ($idPedido <= 0) {
    echo "<div class='alert alert-danger' style='max-width: 600px; margin: 3rem auto; text-align: center;'>";
    echo "<h3><i class='fa-solid fa-triangle-exclamation'></i> Error</h3>";
    echo "<p>ID de pedido no especificado o inválido.</p>";
    echo "<a href='index.php?ruta=admin' class='btn-hero' style='margin-top: 1rem; border: none;'>Volver a Administración</a>";
    echo "</div>";
    exit();
}

// Cargar la información general y detalles del pedido
$pedido = UsuarioModel::mdlMostrarPedidoPorId($idPedido);
if (!$pedido) {
    echo "<div class='alert alert-danger' style='max-width: 600px; margin: 3rem auto; text-align: center;'>";
    echo "<h3><i class='fa-solid fa-triangle-exclamation'></i> Pedido No Encontrado</h3>";
    echo "<p>El pedido #$idPedido no está registrado en el sistema.</p>";
    echo "<a href='index.php?ruta=admin' class='btn-hero' style='margin-top: 1rem; border: none;'>Volver a Administración</a>";
    echo "</div>";
    exit();
}

$detalles = UsuarioModel::mdlMostrarDetallesPedido($idPedido);

// Cálculos de montos en Soles (multiplicados por 3.80)
$totalSoles = $pedido["total"] * 3.80;
$subtotalSoles = $totalSoles / 1.18;
$igvSoles = $totalSoles - $subtotalSoles;
?>

<!-- Estilos específicos para la boleta electrónica de Dulce Café -->
<style>
.invoice-wrapper {
    background: #fff;
    max-width: 720px;
    margin: 2rem auto;
    padding: 2.5rem;
    border-radius: var(--radio-lg);
    box-shadow: 0 4px 20px rgba(78,54,41,0.08);
    border: 1px solid rgba(78,54,41,0.1);
    color: #333;
}

.invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: stretch;
    border-bottom: 2px solid var(--cafe-100);
    padding-bottom: 1.5rem;
    margin-bottom: 1.5rem;
}

.company-info {
    flex: 1;
}
.company-logo {
    font-family: var(--fuente-titulos);
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--cafe-700);
    margin-bottom: 0.5rem;
}
.company-logo i { color: var(--dorado); }
.company-details {
    font-size: 0.85rem;
    color: var(--cafe-600);
    line-height: 1.4;
}

.document-box {
    border: 2.5px solid var(--cafe-700);
    padding: 1rem 2rem;
    text-align: center;
    border-radius: var(--radio-md);
    background: var(--crema-50);
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-width: 240px;
}
.document-box h2 {
    font-size: 1rem;
    color: var(--cafe-700);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}
.document-box .ruc {
    font-weight: 700;
    font-size: 1.05rem;
    margin-bottom: 0.25rem;
}
.document-box .number {
    font-family: var(--fuente-titulos);
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--dorado);
}

.meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    background: var(--crema-50);
    padding: 1.2rem;
    border-radius: var(--radio-md);
    margin-bottom: 1.5rem;
    border: 1px solid rgba(78,54,41,0.06);
    font-size: 0.9rem;
}
.meta-item strong {
    color: var(--cafe-700);
}

.invoice-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1.5rem;
}
.invoice-table th {
    background: var(--cafe-700);
    color: #fff;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    padding: 0.75rem 1rem;
    text-align: left;
}
.invoice-table td {
    padding: 0.85rem 1rem;
    border-bottom: 1px solid var(--cafe-100);
    font-size: 0.9rem;
}
.invoice-table tbody tr:last-child td {
    border-bottom: 2px solid var(--cafe-700);
}

.invoice-summary {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 2rem;
}
.summary-box {
    width: 250px;
}
.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 0.4rem 0;
    font-size: 0.9rem;
}
.summary-row.grand-total {
    border-top: 1.5px solid var(--cafe-700);
    padding-top: 0.6rem;
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--cafe-700);
}

.invoice-footer {
    text-align: center;
    border-top: 1px dashed var(--cafe-400);
    padding-top: 1.5rem;
    font-size: 0.8rem;
    color: var(--cafe-600);
}

.actions-container {
    max-width: 720px;
    margin: 0 auto 2rem;
    display: flex;
    gap: 1rem;
    justify-content: space-between;
}

@media print {
    body {
        background: #fff !important;
        color: #000 !important;
    }
    .invoice-wrapper {
        box-shadow: none !important;
        border: none !important;
        margin: 0 !important;
        padding: 0 !important;
        max-width: 100% !important;
    }
    .actions-container, .site-header, .site-footer {
        display: none !important;
    }
}
</style>

<div class="actions-container">
    <a href="index.php?ruta=admin" class="btn-reporte" style="background: #e5e5e5; color: #333;">
        <i class="fa-solid fa-arrow-left"></i> Volver a Administración
    </a>
    <button onclick="window.print();" class="btn-reporte btn-pdf" style="border: none; cursor: pointer;">
        <i class="fa-solid fa-print"></i> Imprimir Boleta
    </button>
</div>

<div class="invoice-wrapper">
    <div class="invoice-header">
        <div class="company-info">
            <div class="company-logo"><i class="fa-solid fa-mug-hot"></i> DULCE CAFÉ</div>
            <div class="company-details">
                <p><strong>DULCE CAFÉ S.A.C.</strong></p>
                <p>Av. Larco 456, Miraflores, Lima</p>
                <p>Telf: (01) 445-8965 | ventas@dulcecafe.com</p>
                <p>Cafetería Artesanal & Repostería Gourmet</p>
            </div>
        </div>
        <div class="document-box">
            <span class="ruc">R.U.C. 20601234567</span>
            <h2>Boleta de Venta Electrónica</h2>
            <span class="number">B001-<?php echo str_pad($pedido["id"], 8, "0", STR_PAD_LEFT); ?></span>
        </div>
    </div>

    <div class="meta-grid">
        <div class="meta-item">
            <strong>Adquiriente:</strong> <?php echo htmlspecialchars($pedido["cliente_nombre"]); ?>
        </div>
        <div class="meta-item">
            <strong>Fecha de Emisión:</strong> <?php echo date("d/m/Y H:i", strtotime($pedido["fecha"])); ?>
        </div>
        <div class="meta-item">
            <strong>Correo Electrónico:</strong> <?php echo htmlspecialchars($pedido["cliente_email"]); ?>
        </div>
        <div class="meta-item">
            <strong>Condición de Pago:</strong> Contado / Efectivo
        </div>
        <div class="meta-item" style="grid-column: span 2;">
            <strong>Estado del Pedido:</strong> 
            <span class="estado-badge estado-<?php echo htmlspecialchars($pedido["estado"]); ?>" style="padding: 0.15rem 0.5rem; font-size: 0.75rem;">
                <?php echo ucfirst(htmlspecialchars($pedido["estado"])); ?>
            </span>
        </div>
    </div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th style="width: 8%; text-align: center;">Item</th>
                <th>Descripción del Producto</th>
                <th style="width: 15%;">Categoría</th>
                <th style="width: 12%; text-align: right;">Unit. S/</th>
                <th style="width: 10%; text-align: center;">Cant.</th>
                <th style="width: 15%; text-align: right;">Total S/</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (is_array($detalles) && count($detalles) > 0):
                $itemIndex = 1;
                foreach ($detalles as $det):
                    $precioUnitSoles = $det["precio_unitario"] * 3.80;
                    $lineTotalSoles = $precioUnitSoles * $det["cantidad"];
            ?>
                <tr>
                    <td style="text-align: center; color: var(--cafe-600);"><?php echo $itemIndex++; ?></td>
                    <td><strong><?php echo htmlspecialchars($det["nombre"]); ?></strong></td>
                    <td style="color: var(--cafe-600);"><?php echo htmlspecialchars($det["categoria"]); ?></td>
                    <td style="text-align: right;">S/ <?php echo number_format($precioUnitSoles, 2); ?></td>
                    <td style="text-align: center;"><?php echo $det["cantidad"]; ?></td>
                    <td style="text-align: right; font-weight: 600;">S/ <?php echo number_format($lineTotalSoles, 2); ?></td>
                </tr>
            <?php 
                endforeach;
            else: 
            ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem;">No se encontraron detalles de productos para este pedido.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="invoice-summary">
        <div class="summary-box">
            <div class="summary-row">
                <span>OP. GRAVADA</span>
                <span>S/ <?php echo number_format($subtotalSoles, 2); ?></span>
            </div>
            <div class="summary-row">
                <span>I.G.V. (18%)</span>
                <span>S/ <?php echo number_format($igvSoles, 2); ?></span>
            </div>
            <div class="summary-row grand-total">
                <span>TOTAL A PAGAR</span>
                <span>S/ <?php echo number_format($totalSoles, 2); ?></span>
            </div>
        </div>
    </div>

    <div class="invoice-footer">
        <p>Esta es una representación impresa de una boleta de venta electrónica generada por Dulce Café.</p>
        <p style="font-weight: 600; margin-top: 0.5rem; color: var(--cafe-700);">¡Muchas gracias por deleitarte con nuestro café artesanal!</p>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Ocultar dinámicamente el header y footer del sitio web principal para que solo quede la boleta
    const header = document.querySelector(".site-header");
    const footer = document.querySelector(".site-footer");
    if (header) header.style.display = "none";
    if (footer) footer.style.display = "none";
    
    // Autodisparar diálogo de impresión
    setTimeout(function() {
        window.print();
    }, 400);
});
</script>
