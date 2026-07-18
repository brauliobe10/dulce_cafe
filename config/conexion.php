<?php

class Conexion {
    private static $conexion = null;
    private static $ultimoError = null;

    public static function conectar() {
        if (self::$conexion !== null) {
            return self::$conexion;
        }

        $host = "localhost";
        $dbname = "dulce_cafe";
        $usuario = "root";
        $password = ""; 
        
        try {
            self::$conexion = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8",
                $usuario,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                ]
            );
            self::$ultimoError = null;
            return self::$conexion;
        } catch (PDOException $e) {
            self::$ultimoError = $e->getMessage();
            return null;
        }
    }

    public static function obtenerUltimoError() {
        return self::$ultimoError;
    }
}
