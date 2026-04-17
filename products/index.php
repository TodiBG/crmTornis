<?php
session_start();
require_once __DIR__ . '/../config/db_connect.php';

$stockFilter = trim($_GET['stock_status'] ?? '');

// Le seuil "faible stock" est volontairement explicite pour etre facile a modifier plus tard.
$lowStockThreshold = 5;

$query = 'SELECT id, name, code, description, price, stock, created_at FROM products';
$queryParams = [];

if ($stockFilter === 'available') {
    $query .= ' WHERE stock > :available_threshold';
    $queryParams[':available_threshold'] = $lowStockThreshold;
} elseif ($stockFilter === 'low') {
    $query .= ' WHERE stock BETWEEN :low_min AND :low_max';
    $queryParams[':low_min'] = 1;
    $queryParams[':low_max'] = $lowStockThreshold;
} elseif ($stockFilter === 'out') {
    $query .= ' WHERE stock <= 0';
}

$query .= ' ORDER BY created_at DESC, id DESC';

$products = fetchManyFromDB($query, $queryParams);

$stockSummary = fetchOneFromDB(
    'SELECT
        COUNT(*) AS total_products,
        COALESCE(SUM(stock), 0) AS total_units,
        SUM(CASE WHEN stock BETWEEN 1 AND :low_threshold THEN 1 ELSE 0 END) AS low_stock_products,
        SUM(CASE WHEN stock <= 0 THEN 1 ELSE 0 END) AS out_of_stock_products
    FROM products',
    [':low_threshold' => $lowStockThreshold]
);

$pageTitle = 'CRM Tornis - Produits';
$activePage = 'products';
$basePath = '../';

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<main class="container my-5">
    <div class="bg-white p-4 p-md-5 rounded-3">
        <?php require __DIR__ . '/../partials/flash.php'; ?>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-2">Liste des produits</h1>
                <p class="text-muted mb-0">Consultez le catalogue et l'etat du stock des produits.</p>
            </div>
            <a href="create.php" class="btn text-white" style="background-color: #0B3041;">
                Ajouter un produit
            </a>
        </div>

        <?php if ($stockSummary !== false): ?>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small">Unites en stock</div>
                        <div class="fw-bold fs-4"><?= htmlspecialchars((string) $stockSummary['total_units']) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small">Produits en stock faible</div>
                        <div class="fw-bold fs-4 text-warning"><?= htmlspecialchars((string) $stockSummary['low_stock_products']) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small">Produits en rupture</div>
                        <div class="fw-bold fs-4 text-danger"><?= htmlspecialchars((string) $stockSummary['out_of_stock_products']) ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form method="get" class="row g-3 align-items-end mb-4">
            <div class="col-md-4">
                <label for="stock_status" class="form-label">Filtrer le stock</label>
                <select class="form-select" id="stock_status" name="stock_status">
                    <option value="">Tous les produits</option>
                    <option value="available" <?= $stockFilter === 'available' ? 'selected' : '' ?>>Stock confortable</option>
                    <option value="low" <?= $stockFilter === 'low' ? 'selected' : '' ?>>Stock faible</option>
                    <option value="out" <?= $stockFilter === 'out' ? 'selected' : '' ?>>Rupture</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn text-white" style="background-color: #0B3041;">Filtrer</button>
                <a href="index.php" class="btn btn-outline-secondary">Reinitialiser</a>
            </div>
        </form>

        <?php if ($products === false): ?>
            <div class="alert alert-danger mb-0" role="alert">
                Une erreur est survenue lors du chargement des produits.
            </div>
        <?php elseif ($products === []): ?>
            <div class="border rounded-3 p-4 text-center text-muted">
                Aucun produit ne correspond au filtre de stock selectionne.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm  table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Nom</th>
                            <th scope="col">Code</th>
                            <th scope="col">Description</th>
                            <th scope="col" class="text-end">Prix (FCFA) </th>
                            <th scope="col" class="text-center">Stock</th>
                            <th scope="col">Ajoute le</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="fw-semibold py-3"><?= htmlspecialchars($product['name']) ?></td>
                                <td class="py-3"><?= htmlspecialchars($product['code']) ?></td>
                                <td class="py-3"><?= htmlspecialchars($product['description'] ?? '-') ?></td>
                                <td class="text-end py-3"><?= htmlspecialchars(number_format((float) $product['price'], 0, ',', ' ')) ?></td>
                                <td class="text-center py-3">
                                    <?php
                                    // Les couleurs aident a identifier rapidement les produits a surveiller.
                                    $stockValue = (int) $product['stock'];
                                    ?>
                                    <?php if ($stockValue <= 0): ?>
                                        <span class="badge bg-danger">Rupture</span>
                                    <?php elseif ($stockValue <= $lowStockThreshold): ?>
                                        <span class="badge bg-warning text-dark"><?= htmlspecialchars((string) $stockValue) ?> (faible)</span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?= htmlspecialchars((string) $stockValue) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($product['created_at']))) ?></td>
                                <td class="text-end text-nowrap py-3">
                                    <a href="show.php?id=<?= htmlspecialchars((string) $product['id']) ?>" class="btn btn-sm text-dark border-0" title="Voir le produit" aria-label="Voir le produit">
                                        <i class="bi bi-eye"></i>
                                        <span class="visually-hidden">Voir</span>
                                    </a>
                                    <a href="edit.php?id=<?= htmlspecialchars((string) $product['id']) ?>" class="btn btn-sm text-secondary border-0" title="Modifier le produit" aria-label="Modifier le produit">
                                        <i class="bi bi-pencil"></i>
                                        <span class="visually-hidden">Modifier</span>
                                    </a>
                                    <button
                                        type="button"
                                        class="btn btn-sm text-danger border-0"
                                        title="Supprimer le produit"
                                        aria-label="Supprimer le produit"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteProductModal"
                                        data-product-id="<?= htmlspecialchars((string) $product['id']) ?>"
                                        data-product-name="<?= htmlspecialchars($product['name']) ?>">
                                            <i class="bi bi-trash"></i>
                                            <span class="visually-hidden">Supprimer</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="deleteProductModalLabel">Suppression de produit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Voulez-vous vraiment supprimer ce produit ?</p>
                <p class="mb-0 fw-semibold" id="deleteProductName"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-sm " data-bs-dismiss="modal">Annuler</button>
                <form action="delete.php" method="post" class="m-0">
                    <input type="hidden" name="id" id="deleteProductId">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var deleteModal = document.getElementById('deleteProductModal');

        if (!deleteModal) {
            return;
        }

        deleteModal.addEventListener('show.bs.modal', function (event) {
            var triggerButton = event.relatedTarget;
            var productId = triggerButton.getAttribute('data-product-id');
            var productName = triggerButton.getAttribute('data-product-name');

            deleteModal.querySelector('#deleteProductId').value = productId;
            deleteModal.querySelector('#deleteProductName').textContent = productName;
        });
    });
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
