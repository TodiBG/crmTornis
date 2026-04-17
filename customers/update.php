<?php
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

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$tel = trim($_POST['tel'] ?? '');
$address = trim($_POST['address'] ?? '');
$isActive = $_POST['is_active'] ?? '1';

$_SESSION['old_customer'] = [
    'name' => $name,
    'email' => $email,
    'tel' => $tel,
    'address' => $address,
    'is_active' => $isActive,
];

if ($name === '' || $email === '' || $tel === '' || $address === '') {
    $_SESSION['flash_message'] = 'Veuillez remplir tous les champs obligatoires.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: edit.php?id=' . $customerId);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash_message'] = 'Veuillez saisir une adresse email valide.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: edit.php?id=' . $customerId);
    exit;
}

if ($isActive !== '0' && $isActive !== '1') {
    $_SESSION['flash_message'] = 'Le statut du client est invalide.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: edit.php?id=' . $customerId);
    exit;
}

$query = 'UPDATE customers SET name = :name, email = :email, tel = :tel, address = :address, is_active = :is_active WHERE id = :id';
$params = [
    ':name' => $name,
    ':email' => $email,
    ':tel' => $tel,
    ':address' => $address,
    ':is_active' => (int) $isActive,
    ':id' => $customerId,
];

if (saveInDB($query, $params)) {
    unset($_SESSION['old_customer']);
    $_SESSION['flash_message'] = 'Le client a ete mis a jour avec succes.';
    $_SESSION['flash_type'] = 'success';
    header('Location: index.php');
    exit;
}

$_SESSION['flash_message'] = 'Erreur lors de la mise a jour du client. Verifiez notamment que l\'email est unique.';
$_SESSION['flash_type'] = 'danger';
header('Location: edit.php?id=' . $customerId);
exit;
