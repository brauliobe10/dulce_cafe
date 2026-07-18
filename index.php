<?php

/**
     ==========================================================================
   ENRUTADOR CENTRAL Y CONTROLADOR FRONTAL - DULCE CAFÉ
   ==========================================================================
 * 
 * Este archivo actúa como el Front Controller de la aplicación bajo el patrón 
 * de diseño de software Modelo-Vista-Controlador (MVC). Todas las solicitudes 
 * de red convergen en este punto de entrada único.
 */

// Inicialización segura del manejo de sesiones globales de PHP
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusión obligatoria de la configuración de base de datos
require_once __DIR__ . '/config/conexion.php';

// Inclusión de Modelos
require_once __DIR__ . '/models/ProductoModel.php';

// Inclusión de Controladores
require_once __DIR__ . '/controllers/PlantillaController.php';
require_once __DIR__ . '/controllers/ProductoController.php';
require_once __DIR__ . '/controllers/UsuarioController.php';

// Instanciar el controlador de la plantilla y arrancar la visualización del sistema
$plantilla = new PlantillaController();
$plantilla->ctrCargarPlantilla();