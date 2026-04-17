<?php
// Cette page charge un utilisateur existant pour pre-remplir le formulaire de modification.
session_start();
require_once __DIR__ . '/../config/db_connect.php';

$userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($userId === false || $userId === null || $userId <= 0) {
    $_SESSION['flash_message'] = 'Utilisateur introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$user = fetchOneFromDB(
    'SELECT id, firstname, lastname, email, role, avatar_url FROM users WHERE id = :id',
    [':id' => $userId]
);

if ($user === false) {
    $_SESSION['flash_message'] = 'Utilisateur introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$old = $_SESSION['old_user'] ?? [];
unset($_SESSION['old_user']);

// Si la validation a deja echoue une fois, on re-affiche ce que l'utilisateur avait saisi.
if ($old !== []) {
    $user['firstname'] = $old['firstname'] ?? $user['firstname'];
    $user['lastname'] = $old['lastname'] ?? $user['lastname'];
    $user['email'] = $old['email'] ?? $user['email'];
    $user['role'] = $old['role'] ?? $user['role'];
}

$pageTitle = 'CRM Tornis - Modifier un utilisateur';
$activePage = 'users';
$basePath = '../';

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="bg-white p-4 p-md-5 rounded-3">
                <?php if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])): ?>
                    <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_type']) ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['flash_message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h1 class="h3 mb-2">Modifier un utilisateur</h1>
                        <p class="text-muted mb-0">Mettez a jour les informations du compte selectionne.</p>
                    </div>
                    <a href="index.php" class="btn btn-outline-secondary">Retour</a>
                </div>

                <!--
                    Ici aussi, multipart/form-data est necessaire pour permettre le remplacement
                    de la photo de profil par un nouveau fichier.
                -->
                <form action="update.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= htmlspecialchars((string) $user['id']) ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="firstname" class="form-label">Prenom</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" maxlength="50" required value="<?= htmlspecialchars($user['firstname']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="lastname" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="lastname" name="lastname" maxlength="50" required value="<?= htmlspecialchars($user['lastname']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" maxlength="150" required value="<?= htmlspecialchars($user['email']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" minlength="6">
                            <div class="form-text">Laissez vide pour conserver le mot de passe actuel.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="avatar_file" class="form-label">Nouvelle photo de profil</label>
                            <input type="file" class="form-control" id="avatar_file" name="avatar_file" accept=".jpg,.jpeg,.png">
                            <div class="form-text">
                                Laissez vide pour conserver l'avatar actuel. Formats acceptes : JPG, JPEG, PNG.
                                Taille maximale : 2 Mo.
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($user['avatar_url'])): ?>
                        <div class="mt-4">
                            <p class="form-label mb-2">Avatar actuel</p>
                            <!--
                                Le chemin stocke en base est relatif a la racine du projet.
                                Depuis le dossier users/, on ajoute ../ pour retrouver l'image.
                            -->
                            <img
                                src="../<?= htmlspecialchars($user['avatar_url']) ?>"
                                alt="Avatar actuel de l'utilisateur"
                                class="rounded-circle border"
                                style="width: 96px; height: 96px; object-fit: cover;">
                        </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="index.php" class="btn btn-outline-secondary">Annuler</a>
                        <button type="submit" class="btn text-white" style="background-color: #0B3041;">Mettre a jour l'utilisateur</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
