<?php

// Les constantes de connexion sont definies dans db.php pour centraliser la configuration.
require_once 'db.php';

// On ouvre ici une connexion PDO unique reutilisable dans les autres fichiers.
try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
        MYSQL_USER,
        MYSQL_PASSWORD
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $exception) {
    die('Erreur : ' . $exception->getMessage());
}

// Cette fonction est encore un brouillon pedagogique.
// Elle sera remplacee plus tard par un vrai helper SQL.
function executeQuey(String $quey)
{
    return;
}
