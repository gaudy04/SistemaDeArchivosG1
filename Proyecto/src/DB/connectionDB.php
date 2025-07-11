<?php
namespace App\DB;

use App\Config\ResponseHTTP;
use PDO;
use PDOException;

require __DIR__ . '/dataDB.php';

class connectionDB {
    private static $host = '';
    private static $user = '';
    private static $pass = '';

    final public static function inicializar($host, $user, $pass) {
        self::$host = $host;
        self::$user = $user;
        self::$pass = $pass;
    }

    final public static function getConnection() {
        try {
            // Opciones de conexión
            $opt = [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
            
            $pdo = new PDO(self::$host, self::$user, self::$pass, $opt);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            error_log('Conexión exitosa a la base de datos.');
            return $pdo;

        } catch (PDOException $e) {
            error_log('Error en la conexión a la DB: ' . $e->getMessage());
            die(json_encode(ResponseHTTP::status500('Error al conectar con la base de datos.')));
        }
    }
}

