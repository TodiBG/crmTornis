<?php
// La session permet de transporter un message flash d'une page a l'autre.
session_start();
require_once 'config/db_connect.php';
?>


<?php
// Ces variables alimentent les cartes du tableau de bord.
// Elles seront remplacees plus tard par des valeurs calculees en base.
$totalCustomers = 0;
$totalProducts = 0;
$totalOrders = 0;
$latestCustomers = [];

$totalActiveCustomers = 0;
$totalMissingProducts = 0;
$totalCancelledOrders = 0;
?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Tornis - Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100 bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm"  style="background-color: #0B3041 !important;">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">CRM <span style="color: red;">T</span>ornis</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCRM"
                aria-controls="navbarCRM" aria-expanded="false" aria-label="Basculer la navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarCRM">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="customers.php">Clients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Produits</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">Commandes</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-5">

        <!-- Message flash -->
        <?php if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])): ?>
            <!-- Une fois affiche, le message est supprime de la session. -->
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_type']) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['flash_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php
            unset($_SESSION['flash_message'], $_SESSION['flash_type']);
            ?>
        <?php endif; ?>

        <!-- Intro -->
        <div class="p-5 mb-4 bg-white rounded-3">
            <div class="container-fluid py-2">
                <h1 class="display-6 fw-bold">Bienvenue sur le CRM de Tornis</h1>
                <p class="col-md-10 fs-5 text-muted">
                    Gestion de clients,  produits et  commandes dans une interface simplifiée.
                </p>
                <div class="text-center">
                <a href="#form-client" class="btn btn-md text-white mt-1" style="background-color: #0B3041;">Ajouter un client</a>
                <a href="#form-client" class="btn btn-md text-white mt-1" style="background-color: #0B3041;">Ajouter un produit</a>
                <a href="#form-client" class="btn btn-md text-white mt-1" style="background-color: #0B3041;">Ajouter une commande</a>
                <a href="#form-client" class="btn btn-md text-white mt-1" style="background-color: #0B3041;">Ajouter un utilisateur</a>
                 </div>
            </div>
        </div>




        <!-- Statistiques -->
         <!-- Chaque bloc est prevu pour afficher un indicateur cle du CRM. -->
         <div class="bg-white p-3 rounded-3"> 
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card   h-80 rounded-3"  style="border: 4px solid #104862; border-radius:10px !important;" >
                    <h5 class="card-title text-white p-1"  style="background-color: #104862;">Clients actifs</h5>
                    <p class="display-6 m-5 mt-0 mb-3"> <strong>  <?= htmlspecialchars($totalCustomers) ?> </strong></p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card   h-80 rounded-3"  style="border: 4px solid #262626; border-radius:10px !important;" >
                    <h5 class="card-title text-white p-1"  style="background-color: #262626;">Nombre de produits</h5>
                    <p class="display-6 m-5 mt-0 mb-3"> <strong>  <?= htmlspecialchars($totalProducts) ?> </strong></p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card   h-80 rounded-3"  style="border: 4px solid #0D3512; border-radius:10px !important;" >
                    <h5 class="card-title text-white p-1"  style="background-color: #0D3512;">Commandes en cours</h5>
                    <p class="display-6 m-5 mt-0 mb-3"> <strong>  <?= htmlspecialchars($totalOrders) ?> </strong></p>
                </div>
            </div>
        </div>


        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card  h-80 rounded-3"  style="border: 4px solid #104862; border-radius:10px !important;" >
                    <h5 class="card-title text-white p-1"  style="background-color: #104862;">Clients inactifs</h5>
                    <p class="display-6 m-5 mt-0 mb-3"> <strong>  <?= htmlspecialchars($totalActiveCustomers) ?> </strong></p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card  h-80 rounded-3"  style="border: 4px solid #262626; border-radius:10px !important;" >
                    <h5 class="card-title text-white p-1"  style="background-color: #262626;">Produits en rupture de stock</h5>
                    <p class="display-6 m-5 mt-0 mb-3"> <strong>  <?= htmlspecialchars($totalMissingProducts) ?> </strong></p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card  h-80 rounded-3"  style="border: 4px solid #0D3512; border-radius:10px !important;" >
                    <h5 class="card-title text-white p-1"  style="background-color: #0D3512;">Commandes abondonnées</h5>
                    <p class="display-6 m-5 mt-0 mb-3"> <strong>  <?= htmlspecialchars($totalCancelledOrders) ?> </strong></p>
                </div>
            </div>
        </div>

        </div>

    </main>

    <footer class="bg-white  mt-auto">
        <div class="container py-3 text-center text-muted">
            © 2026 <span style="color:red;"><strong>T</strong></span><span style="color:#0B3041;"><strong>ornis</strong></span>  - CRM de gestion clientèle
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
