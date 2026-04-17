<?php
session_start();
require_once __DIR__ . '/../config/db_connect.php';

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
                                    <?php if ((int) $product['stock'] > 0): ?>
                                        <span class="badge bg-success"><?= htmlspecialchars((string) $product['stock']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Rupture</span>
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
