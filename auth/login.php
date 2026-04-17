<?php
session_start();
require_once __DIR__ . '/../config/auth.php';

// Un utilisateur deja connecte est renvoye vers la partie protegee du CRM.
redirectIfAuthenticated('/customers/index.php');

$pageTitle = 'CRM Tornis - Connexion';
$activePage = 'login';
$basePath = '../';

$oldEmail = $_SESSION['old_login_email'] ?? '';
unset($_SESSION['old_login_email']);

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="bg-white p-4 p-md-5 rounded-3">
                <div class="text-center mb-4">
                    <h1 class="h3 mb-2">Connexion</h1>
                    <p class="text-muted mb-0">Identifiez-vous pour acceder aux espaces proteges du CRM.</p>
                </div>

                <?php if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])): ?>
                    <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_type']) ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['flash_message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                <?php endif; ?>

                <!--
                    Le mot de passe n'est jamais pre-rempli pour des raisons de securite.
                    Seule l'adresse email est conservee en cas d'erreur.
                -->
                <form action="authenticate.php" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            required
                            maxlength="150"
                            value="<?= htmlspecialchars($oldEmail) ?>">
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            name="password"
                            required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn text-white" style="background-color: #0B3041;">
                            Se connecter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

