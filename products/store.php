<?php
// La session sert a memoriser les anciennes valeurs du formulaire en cas d'erreur.
session_start();
require_once __DIR__ . '/../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_message'] = 'Methode non autorisée.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: create.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$code = trim($_POST['code'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = trim($_POST['price'] ?? '');
$stock = trim($_POST['stock'] ?? '');

$_SESSION['old_product'] = [
    'name' => $name,
    'code' => $code,
    'description' => $description,
    'price' => $price,
    'stock' => $stock,
];

// On valide d'abord les champs obligatoires avant de tenter l'insertion SQL.
if ($name === '' || $code === '' || $price === '' || $stock === '') {
    $_SESSION['flash_message'] = 'Veuillez remplir tous les champs obligatoires.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: create.php');
    exit;
}

if (!is_numeric($price) || (float) $price < 0) {
    $_SESSION['flash_message'] = 'Le prix doit etre un nombre positif.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: create.php');
    exit;
}

if (filter_var($stock, FILTER_VALIDATE_INT) === false || (int) $stock < 0) {
    $_SESSION['flash_message'] = 'Le stock doit etre un entier positif ou nul.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: create.php');
    exit;
}

$query = 'INSERT INTO products (name, code, description, price, stock) VALUES (:name, :code, :description, :price, :stock)';
// Les parametres sont prepares a part pour rendre la requete plus lisible.
$params = [
    ':name' => $name,
    ':code' => $code,
    ':description' => $description !== '' ? $description : null,
    ':price' => number_format((float) $price, 2, '.', ''),
    ':stock' => (int) $stock,
];

if (saveInDB($query, $params)) {
    unset($_SESSION['old_product']);
    $_SESSION['flash_message'] = 'Le produit a été enregistre avec succes.';
    $_SESSION['flash_type'] = 'success';
    header('Location: create.php');
    exit;
}

$_SESSION['flash_message'] = 'Erreur lors de l\'enregistrement du produit.';
$_SESSION['flash_type'] = 'danger';
header('Location: create.php');
exit;
