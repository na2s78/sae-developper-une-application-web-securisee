<?php

namespace touiteur;

use PDO;
use Exception;

class Connexion {

    private static $pdo = null;

    public static function setConfig($fichier) {
        $config = parse_ini_file($fichier);

        if (!$config) {
            throw new Exception("Impossible de lire le fichier db.ini");
        }

        $dsn = "mysql:host=" . $config['host'] . ";dbname=" . $config['dbname'] . ";charset=utf8";

        self::$pdo = new PDO($dsn, $config['user'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    public static function makeConnection() {
        if (self::$pdo === null) {
            throw new Exception("Connexion non initialisée");
        }
        return self::$pdo;
    }
}