<?php
session_start();
require_once __DIR__ . '/../config/db_connect.php';

/**
 * Supprime un avatar local s'il appartient bien au dossier prevu pour les profils.
 * Cette precaution evite de supprimer accidentellement un autre fichier du projet.
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
 * Valide puis enregistre la nouvelle photo de profil.
 * Le tableau retourne soit un chemin pret a enregistrer en base, soit un message d'erreur.
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

/**
 * Met a jour les donnees de session pour que la navbar affiche immediatement
 * le nouveau nom, email ou avatar sans attendre une nouvelle connexion.
 */
function refreshAuthenticatedUserSession(array $user): void
{
    $_SESSION['auth_user'] = [
        'id' => (int) $user['id'],
        'firstname' => $user['firstname'],
        'lastname' => $user['lastname'],
        'email' => $user['email'],
        'role' => $user['role'],
        'avatar_url' => $user['avatar_url'] ?? null,
    ];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_message'] = 'Methode non autorisee.';
    $_SESSION['flash_type'] = 'danger';
    redirectToPath('/auth/profile.php');
}

$authenticatedUser = getAuthenticatedUser();

if ($authenticatedUser === null) {
    redirectToPath('/auth/login.php');
}

$firstname = trim($_POST['firstname'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');
$email = trim($_POST['email'] ?? '');
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$newPasswordConfirmation = $_POST['new_password_confirmation'] ?? '';

$_SESSION['old_profile'] = [
    'firstname' => $firstname,
    'lastname' => $lastname,
    'email' => $email,
];

if ($firstname === '' || $lastname === '' || $email === '') {
    $_SESSION['flash_message'] = 'Veuillez remplir tous les champs obligatoires.';
    $_SESSION['flash_type'] = 'danger';
    redirectToPath('/auth/profile.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash_message'] = 'Veuillez saisir une adresse email valide.';
    $_SESSION['flash_type'] = 'danger';
    redirectToPath('/auth/profile.php');
}

$user = fetchOneFromDB(
    'SELECT id, firstname, lastname, email, password, role, avatar_url FROM users WHERE id = :id',
    [':id' => $authenticatedUser['id']]
);

if ($user === false) {
    logoutUser();
    $_SESSION['flash_message'] = 'Votre compte est introuvable. Veuillez vous reconnecter.';
    $_SESSION['flash_type'] = 'warning';
    redirectToPath('/auth/login.php');
}

// Si un nouveau mot de passe est saisi, on exige aussi l'ancien et une confirmation identique.
if ($newPassword !== '' || $newPasswordConfirmation !== '') {
    if ($currentPassword === '') {
        $_SESSION['flash_message'] = 'Veuillez saisir votre mot de passe actuel pour le modifier.';
        $_SESSION['flash_type'] = 'danger';
        redirectToPath('/auth/profile.php');
    }

    if (!password_verify($currentPassword, $user['password'])) {
        $_SESSION['flash_message'] = 'Le mot de passe actuel est incorrect.';
        $_SESSION['flash_type'] = 'danger';
        redirectToPath('/auth/profile.php');
    }

    if (strlen($newPassword) < 6) {
        $_SESSION['flash_message'] = 'Le nouveau mot de passe doit contenir au moins 6 caracteres.';
        $_SESSION['flash_type'] = 'danger';
        redirectToPath('/auth/profile.php');
    }

    if ($newPassword !== $newPasswordConfirmation) {
        $_SESSION['flash_message'] = 'La confirmation du nouveau mot de passe ne correspond pas.';
        $_SESSION['flash_type'] = 'danger';
        redirectToPath('/auth/profile.php');
    }
}

$avatarUpload = uploadAvatarFromRequest($_FILES['avatar_file'] ?? []);

if ($avatarUpload['error'] !== null) {
    $_SESSION['flash_message'] = $avatarUpload['error'];
    $_SESSION['flash_type'] = 'danger';
    redirectToPath('/auth/profile.php');
}

$query = 'UPDATE users SET firstname = :firstname, lastname = :lastname, email = :email, avatar_url = :avatar_url';
$params = [
    ':firstname' => $firstname,
    ':lastname' => $lastname,
    ':email' => $email,
    ':avatar_url' => $avatarUpload['path'] ?? $user['avatar_url'],
    ':id' => $authenticatedUser['id'],
];

if ($newPassword !== '') {
    $query .= ', password = :password';
    $params[':password'] = password_hash($newPassword, PASSWORD_DEFAULT);
}

$query .= ' WHERE id = :id';

if (!saveInDB($query, $params)) {
    if ($avatarUpload['path'] !== null) {
        deleteLocalAvatar($avatarUpload['path']);
    }

    $_SESSION['flash_message'] = 'Erreur lors de la mise a jour de votre profil. Verifiez notamment que l\'email est unique.';
    $_SESSION['flash_type'] = 'danger';
    redirectToPath('/auth/profile.php');
}

if ($avatarUpload['path'] !== null && !empty($user['avatar_url'])) {
    deleteLocalAvatar($user['avatar_url']);
}

$updatedUser = [
    'id' => $user['id'],
    'firstname' => $firstname,
    'lastname' => $lastname,
    'email' => $email,
    'role' => $user['role'],
    'avatar_url' => $avatarUpload['path'] ?? $user['avatar_url'],
];

refreshAuthenticatedUserSession($updatedUser);
unset($_SESSION['old_profile']);
$_SESSION['flash_message'] = 'Votre profil a ete mis a jour avec succes.';
$_SESSION['flash_type'] = 'success';
redirectToPath('/auth/profile.php');
