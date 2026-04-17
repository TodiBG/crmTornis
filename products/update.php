<?php
session_start();
require_once __DIR__ . '/../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_message'] = 'Methode non autorisée.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$productId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($productId === false || $productId === null || $productId <= 0) {
    $_SESSION['flash_message'] = 'Produit introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
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

if ($name === '' || $code === '' || $price === '' || $stock === '') {
    $_SESSION['flash_message'] = 'Veuillez remplir tous les champs obligatoires.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: edit.php?id=' . $productId);
    exit;
}

if (!is_numeric($price) || (float) $price < 0) {
    $_SESSION['flash_message'] = 'Le prix doit etre un nombre positif.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: edit.php?id=' . $productId);
    exit;
}

if (filter_var($stock, FILTER_VALIDATE_INT) === false || (int) $stock < 0) {
    $_SESSION['flash_message'] = 'Le stock doit etre un entier positif ou nul.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: edit.php?id=' . $productId);
    exit;
}

$query = 'UPDATE products SET name = :name, code = :code, description = :description, price = :price, stock = :stock WHERE id = :id';
$params = [
    ':name' => $name,
    ':code' => $code,
    ':description' => $description !== '' ? $description : null,
    ':price' => number_format((float) $price, 2, '.', ''),
    ':stock' => (int) $stock,
    ':id' => $productId,
];

if (saveInDB($query, $params)) {
    unset($_SESSION['old_product']);
    $_SESSION['flash_message'] = 'Le produit a ete mis a jour avec succes.';
    $_SESSION['flash_type'] = 'success';
    header('Location: index.php');
    exit;
}

$_SESSION['flash_message'] = 'Erreur lors de la mise a jour du produit.';
$_SESSION['flash_type'] = 'danger';
header('Location: edit.php?id=' . $productId);
exit;
