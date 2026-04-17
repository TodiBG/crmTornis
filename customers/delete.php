<?php
// La suppression passe par POST pour eviter une suppression accidentelle via l'URL.
session_start();
require_once __DIR__ . '/../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_message'] = 'Methode non autorisee.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$customerId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($customerId === false || $customerId === null || $customerId <= 0) {
    $_SESSION['flash_message'] = 'Client introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$customer = fetchOneFromDB('SELECT id FROM customers WHERE id = :id', [':id' => $customerId]);

if ($customer === false) {
    $_SESSION['flash_message'] = 'Client introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

// On interdit la suppression si le client est deja rattache a des commandes.
$ordersUsage = fetchOneFromDB(
    'SELECT COUNT(*) AS total FROM orders WHERE customer_id = :id',
    [':id' => $customerId]
);

if ($ordersUsage !== false && (int) $ordersUsage['total'] > 0) {
    $_SESSION['flash_message'] = 'Suppression impossible : ce client est deja lie a des commandes.';
    $_SESSION['flash_type'] = 'warning';
    header('Location: index.php');
    exit;
}

if (saveInDB('DELETE FROM customers WHERE id = :id', [':id' => $customerId])) {
    $_SESSION['flash_message'] = 'Le client a ete supprime avec succes.';
    $_SESSION['flash_type'] = 'success';
    header('Location: index.php');
    exit;
}

$_SESSION['flash_message'] = 'Erreur lors de la suppression du client.';
$_SESSION['flash_type'] = 'danger';
header('Location: index.php');
exit;
