<?php
session_start();
require_once __DIR__ . '/../config/db_connect.php';

/**
 * Cette page ne prend aucun identifiant en URL.
 * Le profil affiche toujours l'utilisateur actuellement connecte,
 * ce qui garantit qu'il ne peut modifier que ses propres informations.
 */
$authenticatedUser = getAuthenticatedUser();

if ($authenticatedUser === null) {
    redirectToPath('/auth/login.php');
}

$user = fetchOneFromDB(
    'SELECT id, firstname, lastname, email, avatar_url FROM users WHERE id = :id',
    [':id' => $authenticatedUser['id']]
);

if ($user === false) {
    logoutUser();
    $_SESSION['flash_message'] = 'Votre session n\'est plus valide. Veuillez vous reconnecter.';
    $_SESSION['flash_type'] = 'warning';
    redirectToPath('/auth/login.php');
}

$old = $_SESSION['old_profile'] ?? [];
unset($_SESSION['old_profile']);

// Si une validation echoue, on reaffiche les donnees saisies precedemment.
if ($old !== []) {
    $user['firstname'] = $old['firstname'] ?? $user['firstname'];
    $user['lastname'] = $old['lastname'] ?? $user['lastname'];
    $user['email'] = $old['email'] ?? $user['email'];
}

$pageTitle = 'CRM Tornis - Mon profil';
$activePage = 'profile';
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
                        <h1 class="h3 mb-2">Mon profil</h1>
                        <p class="text-muted mb-0">
                            Modifiez ici vos informations personnelles et, si besoin, votre mot de passe.
                        </p>
                    </div>
                    <a href="../index.php" class="btn btn-outline-secondary">Retour</a>
                </div>

                <!--
                    Le formulaire ne transporte pas d'identifiant utilisateur.
                    Le traitement s'appuie uniquement sur l'utilisateur stocke en session.
                -->
                <form action="update_profile.php" method="post" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="firstname" class="form-label">Prenom</label>
                            <input
                                type="text"
                                class="form-control"
                                id="firstname"
                                name="firstname"
                                maxlength="50"
                                required
                                value="<?= htmlspecialchars($user['firstname']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="lastname" class="form-label">Nom</label>
                            <input
                                type="text"
                                class="form-control"
                                id="lastname"
                                name="lastname"
                                maxlength="50"
                                required
                                value="<?= htmlspecialchars($user['lastname']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                maxlength="150"
                                required
                                value="<?= htmlspecialchars($user['email']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="avatar_file" class="form-label">Nouvelle photo de profil</label>
                            <input type="file" class="form-control" id="avatar_file" name="avatar_file" accept=".jpg,.jpeg,.png">
                        </div>

                        <div class="col-md-12">
                            <label for="current_password" class="form-label">Mot de passe actuel</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                            <div class="form-text">
                                Ce champ est obligatoire uniquement si vous souhaitez changer de mot de passe.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="new_password" class="form-label">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" minlength="6">
                        </div>

                        <div class="col-md-6">
                            <label for="new_password_confirmation" class="form-label">Confirmation du nouveau mot de passe</label>
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" minlength="6">
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="form-label mb-2">Avatar actuel</p>
                        <?php
                        // On choisit un avatar par defaut si aucun fichier personnalise n'est enregistre.
                        $profileAvatarPath = !empty($user['avatar_url'])
                            ? $user['avatar_url']
                            : 'assets/images/avatars/default.jpg';
                        ?>
                        <img
                            src="../<?= htmlspecialchars($profileAvatarPath) ?>"
                            alt="Avatar actuel de l'utilisateur connecte"
                            class="rounded-circle border"
                            style="width: 96px; height: 96px; object-fit: cover;">
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="../index.php" class="btn btn-outline-secondary">Annuler</a>
                        <button type="submit" class="btn text-white" style="background-color: #0B3041;">Mettre a jour mon profil</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

