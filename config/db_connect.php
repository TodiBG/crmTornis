<?php

require_once __DIR__ . '/db.php';

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
