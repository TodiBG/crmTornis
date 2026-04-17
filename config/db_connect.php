<?php

// Les constantes de connexion sont definies dans db.php pour centraliser la configuration.
require_once __DIR__ . '/db.php';

// On ouvre ici une connexion PDO unique reutilisable dans les autres fichiers.
try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
        MYSQL_USER,
        MYSQL_PASSWORD
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Exception $exception) {
    die('Erreur : ' . $exception->getMessage());
}

// Ce helper sert aux requetes d'ecriture : il renvoie simplement vrai ou faux.
function saveInDB(string $query, array $params = []): bool
{
    global $pdo;

    try {
        $statement = $pdo->prepare($query);
        return $statement->execute($params);
    } catch (PDOException $exception) {
        return false;
    }
}

// Ce helper est utile quand on attend une liste complete de resultats.
function fetchManyFromDB(string $query, array $params = []): array|false
{
    global $pdo;

    try {
        $statement = $pdo->prepare($query);
        $statement->execute($params);
        return $statement->fetchAll();
    } catch (PDOException $exception) {
        return false;
    }
}

// Ce helper simplifie les cas ou une seule ligne est attendue.
function fetchOneFromDB(string $query, array $params = []): array|false
{
    global $pdo;

    try {
        $statement = $pdo->prepare($query);
        $statement->execute($params);
        $result = $statement->fetch();
        return $result === false ? false : $result;
    } catch (PDOException $exception) {
        return false;
    }
}
