<?php
// La suppression est reservee a une requete POST pour eviter les suppressions accidentelles.
session_start();
require_once __DIR__ . '/../config/db_connect.php';

// La suppression d'un compte utilisateur reste reservee a l'administration.
requireAdmin();

/**
 * Supprime un avatar stocke localement si son chemin pointe bien
 * vers le dossier des photos de profil de l'application.
 */
function deleteLocalAvatar(string $avatarPath): void
{
    $avatarsDirectory = realpath(__DIR__ . '/../assets/images/avatars');
    $targetFile = realpath(__DIR__ . '/../' . $avatarPath);

    if ($avatarsDirectory === false || $targetFile === false) {
        return;
    }

    if (str_starts_with($targetFile, $avatarsDirectory . DIRECTORY_SEPARATOR) && is_file($targetFile)) {
        unlink($targetFile);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_message'] = 'Methode non autorisee.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$userId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($userId === false || $userId === null || $userId <= 0) {
    $_SESSION['flash_message'] = 'Utilisateur introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$user = fetchOneFromDB('SELECT id, avatar_url FROM users WHERE id = :id', [':id' => $userId]);

if ($user === false) {
    $_SESSION['flash_message'] = 'Utilisateur introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

if (saveInDB('DELETE FROM users WHERE id = :id', [':id' => $userId])) {
    // Une fois la ligne supprimee en base, on peut aussi nettoyer l'avatar associe.
    if (!empty($user['avatar_url'])) {
        deleteLocalAvatar($user['avatar_url']);
    }

    $_SESSION['flash_message'] = 'L\'utilisateur a ete supprime avec succes.';
    $_SESSION['flash_type'] = 'success';
    header('Location: index.php');
    exit;
}

$_SESSION['flash_message'] = 'Erreur lors de la suppression de l\'utilisateur.';
$_SESSION['flash_type'] = 'danger';
header('Location: index.php');
exit;
