<?php
session_start();
require_once __DIR__ . '/../config/db_connect.php';

$customerId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($customerId === false || $customerId === null || $customerId <= 0) {
    $_SESSION['flash_message'] = 'Client introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$customer = fetchOneFromDB(
    'SELECT id, name, email, tel, address, is_active FROM customers WHERE id = :id',
    [':id' => $customerId]
);

if ($customer === false) {
    $_SESSION['flash_message'] = 'Client introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$old = $_SESSION['old_customer'] ?? [];
unset($_SESSION['old_customer']);

if ($old !== []) {
    $customer['name'] = $old['name'] ?? $customer['name'];
    $customer['email'] = $old['email'] ?? $customer['email'];
    $customer['tel'] = $old['tel'] ?? $customer['tel'];
    $customer['address'] = $old['address'] ?? $customer['address'];
    $customer['is_active'] = $old['is_active'] ?? (string) $customer['is_active'];
}

$pageTitle = 'CRM Tornis - Modifier un client';
$activePage = 'customers';
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

                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h1 class="h3 mb-2">Modifier un client</h1>
                        <p class="text-muted mb-0">Mettez a jour les informations du client selectionne.</p>
                    </div>
                    <a href="index.php" class="btn btn-outline-secondary">Retour</a>
                </div>

                <form action="update.php" method="post">
                    <input type="hidden" name="id" value="<?= htmlspecialchars((string) $customer['id']) ?>">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="name" class="form-label">Nom du client</label>
                            <input type="text" class="form-control" id="name" name="name" maxlength="100" required value="<?= htmlspecialchars($customer['name']) ?>">
                        </div>

                        <div class="col-md-4">
                            <label for="is_active" class="form-label">Statut</label>
                            <select class="form-select" id="is_active" name="is_active" required>
                                <option value="1" <?= (string) $customer['is_active'] === '1' ? 'selected' : '' ?>>Actif</option>
                                <option value="0" <?= (string) $customer['is_active'] === '0' ? 'selected' : '' ?>>Inactif</option>
                            </select>
                            <div class="form-text">
                                Meme si le client est actif ici, il passera automatiquement inactif apres 2 ans sans activite.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" maxlength="150" required value="<?= htmlspecialchars($customer['email']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="tel" class="form-label">Telephone</label>
                            <input type="text" class="form-control" id="tel" name="tel" maxlength="20" required value="<?= htmlspecialchars($customer['tel']) ?>">
                        </div>

                        <div class="col-12">
                            <label for="address" class="form-label">Adresse</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required><?= htmlspecialchars($customer['address']) ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="index.php" class="btn btn-outline-secondary">Annuler</a>
                        <button type="submit" class="btn text-white" style="background-color: #0B3041;">Mettre a jour le client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
