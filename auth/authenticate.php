<?php
session_start();
require_once __DIR__ . '/../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_message'] = 'Methode non autorisee.';
    $_SESSION['flash_type'] = 'danger';
    redirectToPath('/auth/login.php');
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$_SESSION['old_login_email'] = $email;

if ($email === '' || $password === '') {
    $_SESSION['flash_message'] = 'Veuillez remplir tous les champs.';
    $_SESSION['flash_type'] = 'danger';
    redirectToPath('/auth/login.php');
}

// On recupere l'utilisateur a partir de son email pour verifier ensuite le mot de passe hashe.
$user = fetchOneFromDB(
    'SELECT id, firstname, lastname, email, password, role, avatar_url FROM users WHERE email = :email',
    [':email' => $email]
);

if ($user === false || !password_verify($password, $user['password'])) {
    $_SESSION['flash_message'] = 'Identifiants invalides.';
    $_SESSION['flash_type'] = 'danger';
    redirectToPath('/auth/login.php');
}

loginUser($user);
unset($_SESSION['old_login_email']);
redirectToIntendedOr('/customers/index.php');
