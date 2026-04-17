<?php
// La suppression est volontairement reservee a une requete POST.
session_start();
require_once __DIR__ . '/../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_message'] = 'Methode non autorisee.';
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

$product = fetchOneFromDB(
    'SELECT id, name FROM products WHERE id = :id',
    [':id' => $productId]
);

if ($product === false) {
    $_SESSION['flash_message'] = 'Produit introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

// On bloque la suppression si le produit est deja reference dans une commande.
$usage = fetchOneFromDB(
    'SELECT COUNT(*) AS total FROM order_items WHERE product_id = :id',
    [':id' => $productId]
);

if ($usage !== false && (int) $usage['total'] > 0) {
    $_SESSION['flash_message'] = 'Suppression impossible : ce produit est deja lie a des commandes.';
    $_SESSION['flash_type'] = 'warning';
    header('Location: show.php?id=' . $productId);
    exit;
}

if (saveInDB('DELETE FROM products WHERE id = :id', [':id' => $productId])) {
    $_SESSION['flash_message'] = 'Le produit a ete supprime avec succes.';
    $_SESSION['flash_type'] = 'success';
    header('Location: index.php');
    exit;
}

$_SESSION['flash_message'] = 'Erreur lors de la suppression du produit.';
$_SESSION['flash_type'] = 'danger';
header('Location: show.php?id=' . $productId);
exit;
