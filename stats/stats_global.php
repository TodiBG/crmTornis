<?php

// Chaque variable correspond a une carte du tableau de bord.
$totalCustomers = 0;
$totalProducts = 0;
$totalOrders = 0;

$totalInactiveCustomers = 0;
$totalMissingProducts = 0;
$totalCancelledOrders = 0;

// Les requetes sont separees pour garder un code lisible et facile a expliquer.
$statsCustomers = fetchOneFromDB('SELECT COUNT(*) AS total FROM customers');
$statsProducts = fetchOneFromDB('SELECT COUNT(*) AS total FROM products');
$statsOrders = fetchOneFromDB("SELECT COUNT(*) AS total FROM orders WHERE status = 'en_attente'");
$statsInactiveCustomers = fetchOneFromDB('SELECT COUNT(*) AS total FROM customers WHERE is_active = 0');
$statsMissingProducts = fetchOneFromDB('SELECT COUNT(*) AS total FROM products WHERE stock <= 0');
$statsCancelledOrders = fetchOneFromDB(
    "SELECT COUNT(*) AS total FROM orders WHERE status IN ('abandonnee', 'abandonne', 'annulee', 'annule', 'cancelled')"
);


// Chaque bloc verifie que la requete a bien abouti avant de lire le resultat.
if ($statsCustomers !== false) {
    $totalCustomers = (int) $statsCustomers['total'];
}

if ($statsProducts !== false) {
    $totalProducts = (int) $statsProducts['total'];
}

if ($statsOrders !== false) {
    $totalOrders = (int) $statsOrders['total'];
}

if ($statsInactiveCustomers !== false) {
    $totalInactiveCustomers = (int) $statsInactiveCustomers['total'];
}

if ($statsMissingProducts !== false) {
    $totalMissingProducts = (int) $statsMissingProducts['total'];
}

if ($statsCancelledOrders !== false) {
    $totalCancelledOrders = (int) $statsCancelledOrders['total'];
}


?>

<div class="bg-white p-3 rounded-3">
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card h-80 rounded-3" style="border: 2px solid #104862; border-radius:10px !important;">
                <h5 class="card-title text-white p-1" style="border-radius:4px 4px 0 0; background-color: #104862;">Clients actifs</h5>
                <p class="display-6 m-5 mt-0 mb-3"><strong><?= htmlspecialchars((string) ($totalCustomers - $totalInactiveCustomers)) ?></strong></p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-80 rounded-3" style="border: 2px solid #262626; border-radius:10px !important;">
                <h5 class="card-title text-white p-1" style="border-radius:4px 4px 0 0; background-color: #262626;">Nombre de produits</h5>
                <p class="display-6 m-5 mt-0 mb-3"><strong><?= htmlspecialchars((string) $totalProducts) ?></strong></p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-80 rounded-3" style="border: 2px solid #0D3512; border-radius:10px !important;">
                <h5 class="card-title text-white p-1" style="border-radius:4px 4px 0 0; background-color: #0D3512;">Commandes en cours</h5>
                <p class="display-6 m-5 mt-0 mb-3"><strong><?= htmlspecialchars((string) $totalOrders) ?></strong></p>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card h-80 rounded-3" style="border: 2px solid #104862; border-radius:10px !important;">
                <h5 class="card-title text-white p-1" style="border-radius:4px 4px 0 0; background-color: #104862;">Clients inactifs</h5>
                <p class="display-6 m-5 mt-0 mb-3"><strong><?= htmlspecialchars((string) $totalInactiveCustomers) ?></strong></p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-80 rounded-3" style="border: 2px solid #262626; border-radius:10px !important;">
                <h5 class="card-title text-white p-1" style="border-radius:4px 4px 0 0; background-color: #262626;">Produits en rupture de stock</h5>
                <p class="display-6 m-5 mt-0 mb-3"><strong><?= htmlspecialchars((string) $totalMissingProducts) ?></strong></p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-80 rounded-3" style="border: 2px solid #0D3512; border-radius:10px !important;">
                <h5 class="card-title text-white p-1" style="border-radius:4px 4px 0 0; background-color: #0D3512;">Commandes abandonnees</h5>
                <p class="display-6 m-5 mt-0 mb-3"><strong><?= htmlspecialchars((string) $totalCancelledOrders) ?></strong></p>
            </div>
        </div>
    </div>
</div>

