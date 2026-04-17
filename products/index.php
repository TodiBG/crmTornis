<?php
// Cette page recupere tous les produits pour construire la vue liste.
session_start();
require_once __DIR__ . '/../config/db_connect.php';

// La lecture est centralisee dans un helper pour garder la page lisible.
$products = fetchManyFromDB(
    'SELECT id, name, code, description, price, stock, created_at FROM products ORDER BY created_at DESC, id DESC'
);

$pageTitle = 'CRM Tornis - Produits';
$activePage = 'products';
$basePath = '../';

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<main class="container my-5">
    <div class="bg-white p-4 p-md-5 rounded-3">
        <?php if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_type']) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['flash_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-2">Liste des produits</h1>
                <p class="text-muted mb-0">Consultez le catalogue et l'etat du stock des produits.</p>
            </div>
            <a href="create.php" class="btn text-white" style="background-color: #0B3041;">
                Ajouter un produit
            </a>
        </div>

        <?php if ($products === false): ?>
            <div class="alert alert-danger mb-0" role="alert">
                Une erreur est survenue lors du chargement des produits.
            </div>
        <?php elseif ($products === []): ?>
            <div class="border rounded-3 p-4 text-center text-muted">
                Aucun produit n'est encore enregistre.
            </div>
        <?php else: ?>
            <!-- Le tableau permet d'avoir une vue rapide du catalogue et du stock. -->
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Nom</th>
                            <th scope="col">Code</th>
                            <th scope="col">Description</th>
                            <th scope="col" class="text-end">Prix</th>
                            <th scope="col" class="text-center">Stock</th>
                            <th scope="col">Ajoute le</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($product['name']) ?></td>
                                <td><?= htmlspecialchars($product['code']) ?></td>
                                <td><?= htmlspecialchars($product['description'] ?? '-') ?></td>
                                <td class="text-end"><?= htmlspecialchars(number_format((float) $product['price'], 2, ',', ' ')) ?> FCFA</td>
                                <td class="text-center">
                                    <?php if ((int) $product['stock'] > 0): ?>
                                        <span class="badge bg-success"><?= htmlspecialchars((string) $product['stock']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Rupture</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($product['created_at']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
