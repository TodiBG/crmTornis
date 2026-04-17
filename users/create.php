<?php
// Cette page affiche le formulaire de creation d'un utilisateur.
session_start();
require_once __DIR__ . '/../config/db_connect.php';

$old = $_SESSION['old_user'] ?? [];
unset($_SESSION['old_user']);

$pageTitle = 'CRM Tornis - Ajouter un utilisateur';
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
                        <h1 class="h3 mb-2">Ajouter un utilisateur</h1>
                        <p class="text-muted mb-0">Renseignez les informations du compte a creer pour le CRM.</p>
                    </div>
                    <a href="index.php" class="btn btn-outline-secondary">Retour</a>
                </div>

                <!--
                    L'attribut enctype permet d'envoyer un fichier depuis l'ordinateur de l'utilisateur.
                    Sans lui, $_FILES serait vide cote PHP.
                -->
                <form action="store.php" method="post" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="firstname" class="form-label">Prenom</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" maxlength="50" required value="<?= htmlspecialchars($old['firstname'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="lastname" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="lastname" name="lastname" maxlength="50" required value="<?= htmlspecialchars($old['lastname'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" maxlength="150" required value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="user" <?= ($old['role'] ?? 'user') === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                                <option value="admin" <?= ($old['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" minlength="6" required>
                        </div>

                        <div class="col-md-6">
                            <label for="avatar_file" class="form-label">Photo de profil</label>
                            <input type="file" class="form-control" id="avatar_file" name="avatar_file" accept=".jpg,.jpeg,.png">
                            <div class="form-text">
                                Formats acceptes : JPG, JPEG, PNG. Taille maximale : 2 Mo.
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="index.php" class="btn btn-outline-secondary">Annuler</a>
                        <button type="submit" class="btn text-white" style="background-color: #0B3041;">Enregistrer l'utilisateur</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
