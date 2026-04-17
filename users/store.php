<?php
// Cette page traite la creation d'un utilisateur.
session_start();
require_once __DIR__ . '/../config/db_connect.php';

/**
 * Supprime un avatar local du projet.
 * On limite volontairement la suppression au dossier assets/images/avatars
 * pour eviter d'effacer un fichier en dehors de la zone prevue.
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
 * Cette fonction valide puis copie l'image envoyee dans le dossier des avatars.
 * Elle renvoie soit le chemin a enregistrer en base, soit un message d'erreur.
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

// On valide les informations obligatoires avant de toucher a la base.
if ($firstname === '' || $lastname === '' || $email === '' || $password === '') {
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

if (!in_array($role, ['user', 'admin'], true)) {
    $_SESSION['flash_message'] = 'Le role selectionne est invalide.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: create.php');
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['flash_message'] = 'Le mot de passe doit contenir au moins 6 caracteres.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: create.php');
    exit;
}

// L'upload est traite apres les validations de base pour eviter de copier un fichier inutilement.
$avatarUpload = uploadAvatarFromRequest($_FILES['avatar_file'] ?? []);

if ($avatarUpload['error'] !== null) {
    $_SESSION['flash_message'] = $avatarUpload['error'];
    $_SESSION['flash_type'] = 'danger';
    header('Location: create.php');
    exit;
}

// Le mot de passe est hashé avant insertion : on ne stocke jamais le mot de passe brut.
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

if (saveInDB(
    'INSERT INTO users (firstname, lastname, email, password, role, avatar_url) VALUES (:firstname, :lastname, :email, :password, :role, :avatar_url)',
    [
        ':firstname' => $firstname,
        ':lastname' => $lastname,
        ':email' => $email,
        ':password' => $passwordHash,
        ':role' => $role,
        ':avatar_url' => $avatarUpload['path'],
    ]
)) {
    unset($_SESSION['old_user']);
    $_SESSION['flash_message'] = 'L\'utilisateur a ete enregistre avec succes.';
    $_SESSION['flash_type'] = 'success';
    header('Location: index.php');
    exit;
}

// Si l'insertion SQL echoue apres l'upload, on nettoie le fichier envoye pour ne pas laisser d'orphelin.
if ($avatarUpload['path'] !== null) {
    deleteLocalAvatar($avatarUpload['path']);
}

$_SESSION['flash_message'] = 'Erreur lors de l\'enregistrement de l\'utilisateur. Verifiez notamment que l\'email est unique.';
$_SESSION['flash_type'] = 'danger';
header('Location: create.php');
exit;
