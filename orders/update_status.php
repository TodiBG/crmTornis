<?php
// Cette page traite uniquement le changement de statut d'une commande existante.
session_start();
require_once __DIR__ . '/../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_message'] = 'Methode non autorisee.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$orderId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$status = trim($_POST['status'] ?? '');
$redirectTo = trim($_POST['redirect_to'] ?? '');

// La liste blanche evite d'enregistrer un statut inattendu dans la base.
$allowedStatuses = ['en_attente', 'Validée', 'Annulée'];

if ($orderId === false || $orderId === null || $orderId <= 0) {
    $_SESSION['flash_message'] = 'Commande introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

if (!in_array($status, $allowedStatuses, true)) {
    $_SESSION['flash_message'] = 'Le statut selectionne est invalide.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . ($redirectTo !== '' ? $redirectTo : 'show.php?id=' . $orderId));
    exit;
}

$order = fetchOneFromDB(
    'SELECT id FROM orders WHERE id = :id',
    [':id' => $orderId]
);

if ($order === false) {
    $_SESSION['flash_message'] = 'Commande introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

// La mise a jour est simple : on ne modifie ici que le champ status.
if (saveInDB(
    'UPDATE orders SET status = :status WHERE id = :id',
    [
        ':status' => $status,
        ':id' => $orderId,
    ]
)) {
    $_SESSION['flash_message'] = 'Le statut de la commande a ete mis a jour avec succes.';
    $_SESSION['flash_type'] = 'success';
    header('Location: ' . ($redirectTo !== '' ? $redirectTo : 'show.php?id=' . $orderId));
    exit;
}

$_SESSION['flash_message'] = 'Erreur lors de la mise a jour du statut.';
$_SESSION['flash_type'] = 'danger';
header('Location: ' . ($redirectTo !== '' ? $redirectTo : 'show.php?id=' . $orderId));
exit;
