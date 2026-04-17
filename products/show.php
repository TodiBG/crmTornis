<?php
// La page detail recharge le produit a partir de son identifiant dans l'URL.
session_start();
require_once __DIR__ . '/../config/db_connect.php';

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($productId === false || $productId === null || $productId <= 0) {
    $_SESSION['flash_message'] = 'Produit introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

// Une seule ligne est attendue, d'ou l'utilisation du helper fetchOneFromDB.
$product = fetchOneFromDB(
    'SELECT id, name, code, description, price, stock, created_at FROM products WHERE id = :id',
    [':id' => $productId]
);

if ($product === false) {
    $_SESSION['flash_message'] = 'Produit introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$pageTitle = 'CRM Tornis - Detail produit';
$activePage = 'products';
$basePath = '../';

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="bg-white p-4 p-md-5 rounded-3">
                <?php if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])): ?>
                    <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_type']) ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['flash_message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                <?php endif; ?>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
                    <div>
                        <h1 class="h3 mb-2"><?= htmlspecialchars($product['name']) ?></h1>
                        <p class="text-muted mb-0">Consultez les informations detaillees du produit.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="index.php" class="btn btn-outline-secondary">Retour</a>
                        <a href="edit.php?id=<?= htmlspecialchars((string) $product['id']) ?>" class="btn text-white" style="background-color: #0B3041;">
                            Modifier
                        </a>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted small mb-1">Code produit</div>
                            <div class="fw-semibold"><?= htmlspecialchars($product['code']) ?></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted small mb-1">Prix</div>
                            <div class="fw-semibold"><?= htmlspecialchars(number_format((float) $product['price'], 2, ',', ' ')) ?> FCFA</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted small mb-1">Stock</div>
                            <div class="fw-semibold">
                                <?php if ((int) $product['stock'] > 0): ?>
                                    <?= htmlspecialchars((string) $product['stock']) ?> unite(s) disponible(s)
                                <?php else: ?>
                                    Rupture de stock
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted small mb-1">Date d'ajout</div>
                            <div class="fw-semibold"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($product['created_at']))) ?></div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="border rounded-3 p-3">
                            <div class="text-muted small mb-1">Description</div>
                            <div><?= nl2br(htmlspecialchars($product['description'] ?: 'Aucune description.')) ?></div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <form action="delete.php" method="post" onsubmit="return confirm('Supprimer ce produit ?');">
                        <input type="hidden" name="id" value="<?= htmlspecialchars((string) $product['id']) ?>">
                        <button type="submit" class="btn btn-outline-danger">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
