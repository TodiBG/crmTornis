<?php
session_start();
require_once 'config/db_connect.php';
require_once 'config/auth.php';

$pageTitle = 'CRM Tornis - Accueil';
$activePage = 'home';
$basePath = '';
$authenticatedUser = getAuthenticatedUser();

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';
?>

<main class="container my-5">

    <?php if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_type']) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="p-5 mb-4 bg-white rounded-3">
        <div class="container-fluid py-2">
            <h1 class="display-6 fw-bold">Bienvenue sur le CRM de Tornis</h1>
            <p class="col-md-10 fs-5 text-muted">
                Gestion de clients, produits et commandes dans une interface simplifiee.
            </p>
            <div class="text-center">
                <?php if ($authenticatedUser !== null): ?>
                    <!--
                        Une fois connecte, l'utilisateur peut acceder directement aux actions rapides
                        du CRM depuis la page d'accueil.
                    -->
                    <a href="customers/create.php" class="btn btn-md text-white mt-1" style="background-color: #0B3041;">Ajouter un client</a>
                    <a href="products/create.php" class="btn btn-md text-white mt-1" style="background-color: #0B3041;">Ajouter un produit</a>
                    <a href="orders/create.php" class="btn btn-md text-white mt-1" style="background-color: #0B3041;">Ajouter une commande</a>
                    <a href="users/create.php" class="btn btn-md text-white mt-1" style="background-color: #0B3041;">Ajouter un utilisateur</a>
                <?php else: ?>
                    <!--
                        L'accueil reste public, mais les pages de gestion sont protegees.
                        On oriente donc le visiteur vers la page de connexion.
                    -->
                    <a href="auth/login.php" class="btn btn-md text-white mt-1" style="background-color: #0B3041;">Se connecter</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/stats/stats_global.php'; ?>



</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
