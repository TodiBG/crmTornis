<?php
// Cette page applique la mise a jour d'un utilisateur existant.
session_start();
require_once __DIR__ . '/../config/db_connect.php';

/**
 * Supprime un avatar local du projet si le fichier appartient bien
 * au dossier dedie aux photos de profil.
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

/**
 * Valide et copie la nouvelle image envoyee depuis le formulaire.
 * Si aucun fichier n'est choisi, on renvoie simplement path = null.
 */
function uploadAvatarFromRequest(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['path' => null, 'error' => null];
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['path' => null, 'error' => 'Erreur lors de l\'envoi de la photo de profil.'];
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        return ['path' => null, 'error' => 'La photo de profil ne doit pas depasser 2 Mo.'];
    }

    // On limite volontairement les avatars a JPG/JPEG et PNG pour simplifier la validation.
    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    $mimeType = mime_content_type($file['tmp_name']);

    if ($mimeType === false || !isset($allowedMimeTypes[$mimeType])) {
        return ['path' => null, 'error' => 'Le format de l\'image est invalide. Formats acceptes : JPG, JPEG, PNG.'];
    }

    $uploadDirectory = __DIR__ . '/../assets/images/avatars';

    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0777, true) && !is_dir($uploadDirectory)) {
        return ['path' => null, 'error' => 'Impossible de creer le dossier de stockage des avatars.'];
    }

    $filename = uniqid('avatar_', true) . '.' . $allowedMimeTypes[$mimeType];
    $destination = $uploadDirectory . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['path' => null, 'error' => 'Impossible d\'enregistrer la photo de profil sur le serveur.'];
    }

    return ['path' => 'assets/images/avatars/' . $filename, 'error' => null];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_message'] = 'Methode non autorisee.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$userId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$firstname = trim($_POST['firstname'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = trim($_POST['role'] ?? 'user');

$_SESSION['old_user'] = [
    'firstname' => $firstname,
    'lastname' => $lastname,
    'email' => $email,
    'role' => $role,
];

if ($userId === false || $userId === null || $userId <= 0) {
    $_SESSION['flash_message'] = 'Utilisateur introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

if ($firstname === '' || $lastname === '' || $email === '') {
    $_SESSION['flash_message'] = 'Veuillez remplir tous les champs obligatoires.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: edit.php?id=' . $userId);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash_message'] = 'Veuillez saisir une adresse email valide.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: edit.php?id=' . $userId);
    exit;
}

if (!in_array($role, ['user', 'admin'], true)) {
    $_SESSION['flash_message'] = 'Le role selectionne est invalide.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: edit.php?id=' . $userId);
    exit;
}

if ($password !== '' && strlen($password) < 6) {
    $_SESSION['flash_message'] = 'Le nouveau mot de passe doit contenir au moins 6 caracteres.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: edit.php?id=' . $userId);
    exit;
}

$avatarUpload = uploadAvatarFromRequest($_FILES['avatar_file'] ?? []);

if ($avatarUpload['error'] !== null) {
    $_SESSION['flash_message'] = $avatarUpload['error'];
    $_SESSION['flash_type'] = 'danger';
    header('Location: edit.php?id=' . $userId);
    exit;
}

$user = fetchOneFromDB('SELECT id, avatar_url FROM users WHERE id = :id', [':id' => $userId]);

if ($user === false) {
    $_SESSION['flash_message'] = 'Utilisateur introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

// La requete de base met a jour les champs classiques.
$query = 'UPDATE users SET firstname = :firstname, lastname = :lastname, email = :email, role = :role, avatar_url = :avatar_url';
$params = [
    ':firstname' => $firstname,
    ':lastname' => $lastname,
    ':email' => $email,
    ':role' => $role,
    ':avatar_url' => $avatarUpload['path'] ?? $user['avatar_url'],
    ':id' => $userId,
];

// Si un nouveau mot de passe est saisi, on met aussi a jour son hash.
if ($password !== '') {
    $query .= ', password = :password';
    $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
}

$query .= ' WHERE id = :id';

if (saveInDB($query, $params)) {
    // Si un nouvel avatar remplace l'ancien, on peut supprimer l'ancien fichier local.
    if ($avatarUpload['path'] !== null && !empty($user['avatar_url'])) {
        deleteLocalAvatar($user['avatar_url']);
    }

    unset($_SESSION['old_user']);
    $_SESSION['flash_message'] = 'L\'utilisateur a ete mis a jour avec succes.';
    $_SESSION['flash_type'] = 'success';
    header('Location: index.php');
    exit;
}

// Si la requete SQL echoue, on retire le nouveau fichier pour rester coherent avec la base.
if ($avatarUpload['path'] !== null) {
    deleteLocalAvatar($avatarUpload['path']);
}

$_SESSION['flash_message'] = 'Erreur lors de la mise a jour de l\'utilisateur. Verifiez notamment que l\'email est unique.';
$_SESSION['flash_type'] = 'danger';
header('Location: edit.php?id=' . $userId);
exit;
