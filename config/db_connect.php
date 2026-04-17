<?php


// Les constantes de connexion sont definies dans db.php pour centraliser la configuration.
require_once 'config/db.php';


// Connnexion à la base de données 
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



// Fonction de fabrication de requêtes
function executeQuey(String  $quey) {
    // Cette fonction est encore un brouillon pedagogique.
    // Elle sera remplacee plus tard par un vrai helper SQL.
    return  ;  

    try {
        $statment = $pdo->query("SELECT COUNT(*) AS total FROM customers");
    } catch (PDOException $e) {
        $flashMessage = "Erreur lors du chargement des données.";
        $flashType = "danger";
    }
}
