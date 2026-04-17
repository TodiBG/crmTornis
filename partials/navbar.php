<?php
require_once __DIR__ . '/../config/auth.php';

$activePage = $activePage ?? '';
$basePath = $basePath ?? '';
$authenticatedUser = getAuthenticatedUser();
?>
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #0B3041 !important;">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= htmlspecialchars($basePath) ?>index.php">CRM <span style="color: red;">T</span>ornis</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCRM"
            aria-controls="navbarCRM" aria-expanded="false" aria-label="Basculer la navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarCRM">
            <!--
                Ce premier bloc contient les liens principaux du CRM.
                Il regroupe la navigation "metier" : accueil, clients, produits, commandes, utilisateurs.
            -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'home' ? 'active' : '' ?>" href="<?= htmlspecialchars($basePath) ?>index.php">Accueil</a>
                </li>

                <?php if ($authenticatedUser !== null): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $activePage === 'customers' ? 'active' : '' ?>" href="<?= htmlspecialchars($basePath) ?>customers/index.php">Clients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activePage === 'products' ? 'active' : '' ?>" href="<?= htmlspecialchars($basePath) ?>products/index.php">Produits</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activePage === 'orders' ? 'active' : '' ?>" href="<?= htmlspecialchars($basePath) ?>orders/index.php">Commandes</a>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $activePage === 'users' ? 'active' : '' ?>" href="<?= htmlspecialchars($basePath) ?>users/index.php">Utilisateurs</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <!--
                Ce second bloc est volontairement separe.
                Il regroupe les informations liees a la session : connexion, nom de l'utilisateur, deconnexion.
            -->
            <ul class="navbar-nav ms-lg-4 mb-2 mb-lg-0 border-top border-light pt-2 pt-lg-0 mt-2 mt-lg-0">
                <?php if ($authenticatedUser !== null): ?>
                    <?php
                    // Si l'utilisateur n'a pas d'avatar personnalise, on reutilise l'image par defaut du projet.
                    $navbarAvatarPath = !empty($authenticatedUser['avatar_url'])
                        ? $authenticatedUser['avatar_url']
                        : 'assets/images/avatars/default.jpg';

                    // Le texte complet est place dans l'attribut title pour etre visible au survol.
                    $navbarUserFullName = trim(($authenticatedUser['firstname'] ?? '') . ' ' . ($authenticatedUser['lastname'] ?? ''));
                    ?>
                    <li class="nav-item">
                        <!--
                            L'avatar sert d'acces rapide au profil personnel.
                            Le title permet d'afficher le nom complet au survol.
                        -->
                        <a
                            class="nav-link d-flex align-items-center pe-1 <?= $activePage === 'profile' ? 'active' : '' ?>"
                            href="<?= htmlspecialchars($basePath) ?>auth/profile.php"
                            title="<?= htmlspecialchars($navbarUserFullName) ?>">
                            <img
                                src="<?= htmlspecialchars($basePath . $navbarAvatarPath) ?>"
                                alt="Avatar de <?= htmlspecialchars($navbarUserFullName) ?>"
                                class="rounded-circle border border-light"
                                style="width: 30px; height: 30px; object-fit: cover;">
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars($basePath) ?>auth/logout.php">Deconnexion</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $activePage === 'login' ? 'active' : '' ?>" href="<?= htmlspecialchars($basePath) ?>auth/login.php">Connexion</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
