<?php
session_start();
require_once 'config/db_connect.php';

$totalCustomers = 0;
$totalProducts = 0;
$totalOrders = 0;
$latestCustomers = [];

$totalActiveCustomers = 0;
$totalMissingProducts = 0;
$totalCancelledOrders = 0;

$pageTitle = 'CRM Tornis - Accueil';
$activePage = 'home';
$basePath = '';

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
                <a href="#form-client" class="btn btn-md text-white mt-1" style="background-color: #0B3041;">Ajouter un client</a>
                <a href="products/create.php" class="btn btn-md text-white mt-1" style="background-color: #0B3041;">Ajouter un produit</a>
                <a href="#form-client" class="btn btn-md text-white mt-1" style="background-color: #0B3041;">Ajouter une commande</a>
                <a href="#form-client" class="btn btn-md text-white mt-1" style="background-color: #0B3041;">Ajouter un utilisateur</a>
            </div>
        </div>
    </div>

    <div class="bg-white p-3 rounded-3">
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card h-80 rounded-3" style="border: 2px solid #104862; border-radius:10px !important;">
                    <h5 class="card-title text-white p-1" style="border-radius:4px 4px 0 0;  background-color: #104862;">Clients actifs</h5>
                    <p class="display-6 m-5 mt-0 mb-3"><strong><?= htmlspecialchars($totalCustomers) ?></strong></p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-80 rounded-3" style="border: 2px solid #262626; border-radius:10px !important;">
                    <h5 class="card-title text-white p-1" style="border-radius:4px 4px 0 0;  background-color: #262626;">Nombre de produits</h5>
                    <p class="display-6 m-5 mt-0 mb-3"><strong><?= htmlspecialchars($totalProducts) ?></strong></p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-80 rounded-3" style="border: 2px solid #0D3512; border-radius:10px !important;">
                    <h5 class="card-title text-white p-1" style="border-radius:4px 4px 0 0;  background-color: #0D3512;">Commandes en cours</h5>
                    <p class="display-6 m-5 mt-0 mb-3"><strong><?= htmlspecialchars($totalOrders) ?></strong></p>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card h-80 rounded-3" style="border: 2px solid #104862; border-radius:10px !important;">
                    <h5 class="card-title text-white p-1" style="border-radius:4px 4px 0 0;  background-color: #104862;">Clients inactifs</h5>
                    <p class="display-6 m-5 mt-0 mb-3"><strong><?= htmlspecialchars($totalActiveCustomers) ?></strong></p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-80 rounded-3" style="border: 2px solid #262626; border-radius:10px !important;">
                    <h5 class="card-title text-white p-1" style="border-radius:4px 4px 0 0;  background-color: #262626;">Produits en rupture de stock</h5>
                    <p class="display-6 m-5 mt-0 mb-3"><strong><?= htmlspecialchars($totalMissingProducts) ?></strong></p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-80 rounded-3" style="border: 2px solid #0D3512; border-radius:10px !important;">
                    <h5 class="card-title text-white p-1" style="border-radius:4px 4px 0 0; background-color: #0D3512;">Commandes abandonnees</h5>
                    <p class="display-6 m-5 mt-0 mb-3"><strong><?= htmlspecialchars($totalCancelledOrders) ?></strong></p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
