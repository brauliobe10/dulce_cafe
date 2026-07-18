<?php

require_once __DIR__ . '/../models/VentaModel.php';

class VentaController {

    
    //Obtiene todos los datos de ventas necesarios para el dashboard/reportes.
    public static function obtenerDatos() {
        return [
            'ventasDia' => VentaModel::mdlObtenerVentasDia(),
            'ventasSemana' => VentaModel::mdlObtenerVentasSemana(),
            'ventasMes' => VentaModel::mdlObtenerVentasMes(),
            'ventasUltimaSemana' => VentaModel::mdlObtenerVentasDiariasUltimaSemana(),
            'ventasCategorias' => VentaModel::mdlObtenerVentasPorCategoria(),
        ];
    }
}
