<?php
// Cette page prepare le formulaire de creation d'une commande.
session_start();
require_once __DIR__ . '/../config/db_connect.php';

// On ne propose que les clients effectivement actifs pour eviter de creer
// de nouvelles commandes sur des comptes devenus inactifs.
$customers = fetchManyFromDB(
    "SELECT
        c.id,
        c.name
    FROM customers c
    WHERE c.is_active = 1
    AND COALESCE(
        (SELECT MAX(o.order_date) FROM orders o WHERE o.customer_id = c.id),
        c.created_at
    ) >= DATE_SUB(NOW(), INTERVAL 2 YEAR)
    ORDER BY c.name ASC"
);

// On ne propose que les produits avec un stock disponible strictement positif.
$products = fetchManyFromDB(
    'SELECT id, name, code, price, stock FROM products WHERE stock > 0 ORDER BY name ASC'
);

$old = $_SESSION['old_order'] ?? [];
unset($_SESSION['old_order']);

$oldQuantities = $old['quantities'] ?? [];

$pageTitle = 'CRM Tornis - Ajouter une commande';
$activePage = 'orders';
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

        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="h3 mb-2">Ajouter une commande</h1>
                <p class="text-muted mb-0">Choisissez un client puis indiquez les quantites souhaitees par produit.</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary">Retour</a>
        </div>

        <?php if ($customers === false || $products === false): ?>
            <div class="alert alert-danger mb-0" role="alert">
                Impossible de charger les donnees necessaires a la creation d'une commande.
            </div>
        <?php elseif ($customers === []): ?>
            <div class="alert alert-warning mb-0" role="alert">
                Aucun client actif n'est disponible pour creer une commande.
            </div>
        <?php elseif ($products === []): ?>
            <div class="alert alert-warning mb-0" role="alert">
                Aucun produit en stock n'est disponible pour creer une commande.
            </div>
        <?php else: ?>
            <!-- Le formulaire envoie un tableau de quantites, indexe par identifiant produit. -->
            <form action="store.php" method="post">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="customer_id" class="form-label">Client</label>
                        <select class="form-select" id="customer_id" name="customer_id" required>
                            <option value="">Selectionnez un client</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= htmlspecialchars((string) $customer['id']) ?>" <?= ($old['customer_id'] ?? '') === (string) $customer['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($customer['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Produit</th>
                                <th scope="col">Code</th>
                                <th scope="col" class="text-end">Prix unitaire</th>
                                <th scope="col" class="text-center">Stock</th>
                                <th scope="col" class="text-center">Quantite</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td class="py-3 fw-semibold"><?= htmlspecialchars($product['name']) ?></td>
                                    <td class="py-3"><?= htmlspecialchars($product['code']) ?></td>
                                    <td class="py-3 text-end"><?= htmlspecialchars(number_format((float) $product['price'], 0, ',', ' ')) ?> FCFA</td>
                                    <td class="py-3 text-center"><?= htmlspecialchars((string) $product['stock']) ?></td>
                                    <td class="py-3 text-center" style="max-width: 120px;">
                                        <input
                                            type="number"
                                            class="form-control text-center"
                                            name="quantities[<?= htmlspecialchars((string) $product['id']) ?>]"
                                            min="0"
                                            max="<?= htmlspecialchars((string) $product['stock']) ?>"
                                            step="1"
                                            value="<?= htmlspecialchars($oldQuantities[(string) $product['id']] ?? '0') ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex gap-2 justify-content-end mt-4">
                    <a href="index.php" class="btn btn-outline-secondary">Annuler</a>
                    <button type="submit" class="btn text-white" style="background-color: #0B3041;">Enregistrer la commande</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
