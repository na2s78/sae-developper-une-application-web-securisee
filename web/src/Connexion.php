<?php

class Connexion
{
    public static function getPDO(): PDO
    {
        $serveur = "localhost";
        $base = "";
        $utilisateur = "root";
        $motDePasse = "";

        $pdo = new PDO(
            "mysql:host=$serveur;dbname=$base;charset=utf8mb4",
            $utilisateur,
            $motDePasse
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}