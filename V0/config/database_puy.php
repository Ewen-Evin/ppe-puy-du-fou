<?php

function getPDO(){
    $dbHost     = getenv('DB_HOST') ?: 'localhost';
    $dbName     = getenv('DB_NAME') ?: 'bdd';
    $dbUser     = getenv('DB_USER') ?: 'root';
    $dbPassword = getenv('DB_PASS') ?: '';

    try {
        $pdo = new PDO(
            "mysql:host=$dbHost;dbname=$dbName;charset=utf8",
            $dbUser,
            $dbPassword
        );
            
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } 
    catch (PDOException $e) {
        echo "Erreur de connexion : " . $e->getMessage();
        return null;
    }
}