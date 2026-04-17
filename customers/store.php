<?php
// Cette page traite la creation d'un client apres soumission du formulaire.
session_start();
require_once __DIR__ . '/../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_message'] = 'Methode non autorisee.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$tel = trim($_POST['tel'] ?? '');
$address = trim($_POST['address'] ?? '');

$_SESSION['old_customer'] = [
    'name' => $name,
    'email' => $email,
    'tel' => $tel,
    'address' => $address,
];

// On valide d'abord les donnees minimales avant toute insertion SQL.
if ($name === '' || $email === '' || $tel === '' || $address === '') {
    $_SESSION['flash_message'] = 'Veuillez remplir tous les champs obligatoires.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: create.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash_message'] = 'Veuillez saisir une adresse email valide.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: create.php');
    exit;
}

$query = 'INSERT INTO customers (name, email, tel, address, is_active) VALUES (:name, :email, :tel, :address, :is_active)';
// Le client est cree actif par defaut, conformement a la regle metier.
$params = [
    ':name' => $name,
    ':email' => $email,
    ':tel' => $tel,
    ':address' => $address,
    ':is_active' => 1,
];

if (saveInDB($query, $params)) {
    unset($_SESSION['old_customer']);
    $_SESSION['flash_message'] = 'Le client a ete enregistre avec succes.';
    $_SESSION['flash_type'] = 'success';
    header('Location: index.php');
    exit;
}

$_SESSION['flash_message'] = 'Erreur lors de l\'enregistrement du client. Verifiez notamment que l\'email est unique.';
$_SESSION['flash_type'] = 'danger';
header('Location: create.php');
exit;
