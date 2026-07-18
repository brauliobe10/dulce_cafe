<?php
// Solo puede acceder el administrador
if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["rol"] !== "admin") {
    header("Location: index.php?ruta=login");
    exit();
}

require_once __DIR__ . '/../models/UsuarioModel.php';
$pedidos = UsuarioModel::mdlMostrarPedidos();

// Cabeceras HTTP para la descarga del archivo Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=reporte_ventas_dulce_cafe_" . date("Y-m-d") . ".xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas - Dulce Café</title>
</head>
<body>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr style="background-color:#4e3629; color:#fff; font-weight:bold;">
                <th colspan="6" style="font-size:16px; text-align:center;">
                    Reporte de Ventas y Pedidos — Dulce Café — <?php echo date("d/m/Y"); ?>
                </th>
            </tr>
            <tr style="background-color:#c89d7c; color:#fff;">
                <th>#ID Pedido</th>
                <th>Cliente</th>
                <th>Correo</th>
                <th>Total (PEN)</th>
                <th>Fecha y Hora</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (is_array($pedidos) && count($pedidos) > 0):
                $grandTotal = 0;
                foreach ($pedidos as $p):
                    $grandTotal += $p["total"];
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($p["id"]); ?></td>
                    <td><?php echo htmlspecialchars($p["cliente_nombre"]); ?></td>
                    <td><?php echo htmlspecialchars($p["cliente_email"]); ?></td>
                    <td>S/<?php echo number_format($p["total"] * 3.80, 2); ?></td>
                    <td><?php echo date("d/m/Y H:i", strtotime($p["fecha"])); ?></td>
                    <td><?php echo ucfirst(htmlspecialchars($p["estado"])); ?></td>
                </tr>
            <?php endforeach; ?>
                <tr style="font-weight:bold; background-color:#f5ebe0;">
                    <td colspan="3" style="text-align:right;">TOTAL GENERAL:</td>
                    <td>S/<?php echo number_format($grandTotal * 3.80, 2); ?></td>
                    <td colspan="2"></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center;">No hay pedidos registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
