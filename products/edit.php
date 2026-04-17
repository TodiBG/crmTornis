<?php
// L'identifiant du produit vient de l'URL : on le valide avant toute lecture SQL.
session_start();
require_once __DIR__ . '/../config/db_connect.php';

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($productId === false || $productId === null || $productId <= 0) {
    $_SESSION['flash_message'] = 'Produit introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$product = fetchOneFromDB(
    'SELECT id, name, code, description, price, stock FROM products WHERE id = :id',
    [':id' => $productId]
);

if ($product === false) {
    $_SESSION['flash_message'] = 'Produit introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$old = $_SESSION['old_product'] ?? [];
unset($_SESSION['old_product']);

// Si une validation a echoue precedemment, on re-affiche les valeurs saisies.
if ($old !== []) {
    $product['name'] = $old['name'] ?? $product['name'];
    $product['code'] = $old['code'] ?? $product['code'];
    $product['description'] = $old['description'] ?? $product['description'];
    $product['price'] = $old['price'] ?? $product['price'];
    $product['stock'] = $old['stock'] ?? $product['stock'];
}

$pageTitle = 'CRM Tornis - Modifier un produit';
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
                    <div class="alert alert-sm alert-<?= htmlspecialchars($_SESSION['flash_type']) ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['flash_message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h1 class="h3 mb-2">Modifier un produit</h1>
                        <p class="text-muted mb-0">
                            Mettez a jour les informations du produit selectionne.
                        </p>
                    </div>
                    <a href="index.php" class="btn btn-outline-secondary">Retour</a>
                </div>

                <!-- Le formulaire poste vers update.php qui gerera la modification en base. -->
                <form action="update.php" method="post">
                    <input type="hidden" name="id" value="<?= htmlspecialchars((string) $product['id']) ?>">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="name" class="form-label">Nom du produit</label>
                            <input
                                type="text"
                                class="form-control"
                                id="name"
                                name="name"
                                maxlength="150"
                                required
                                value="<?= htmlspecialchars($product['name']) ?>">
                        </div>

                        <div class="col-md-4">
                            <label for="code" class="form-label">Code produit</label>
                            <input
                                type="text"
                                class="form-control"
                                id="code"
                                name="code"
                                maxlength="50"
                                required
                                value="<?= htmlspecialchars($product['code']) ?>">
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea
                                class="form-control"
                                id="description"
                                name="description"
                                rows="4"
                                placeholder="Decrivez brievement le produit"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="price" class="form-label">Prix</label>
                            <div class="input-group">
                                <span class="input-group-text">EUR</span>
                                <input
                                    type="number"
                                    class="form-control"
                                    id="price"
                                    name="price"
                                    min="0"
                                    step="0.01"
                                    required
                                    value="<?= htmlspecialchars((string) $product['price']) ?>">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="stock" class="form-label">Stock disponible</label>
                            <input
                                type="number"
                                class="form-control"
                                id="stock"
                                name="stock"
                                min="0"
                                step="1"
                                required
                                value="<?= htmlspecialchars((string) $product['stock']) ?>">
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="index.php" class="btn btn-outline-secondary">Annuler</a>
                        <button type="submit" class="btn text-white" style="background-color: #0B3041;">
                            Mettre a jour le produit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
