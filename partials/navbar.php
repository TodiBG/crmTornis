<?php
$activePage = $activePage ?? '';
$basePath = $basePath ?? '';
?>
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #0B3041 !important;">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= htmlspecialchars($basePath) ?>index.php">CRM <span style="color: red;">T</span>ornis</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCRM"
            aria-controls="navbarCRM" aria-expanded="false" aria-label="Basculer la navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarCRM">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'home' ? 'active' : '' ?>" href="<?= htmlspecialchars($basePath) ?>index.php">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'customers' ? 'active' : '' ?>" href="<?= htmlspecialchars($basePath) ?>customers.php">Clients</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'products' ? 'active' : '' ?>" href="<?= htmlspecialchars($basePath) ?>products/index.php">Produits</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'orders' ? 'active' : '' ?>" href="<?= htmlspecialchars($basePath) ?>orders.php">Commandes</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
