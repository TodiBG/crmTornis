<?php
session_start();
require_once __DIR__ . '/../config/db_connect.php';

$old = $_SESSION['old_customer'] ?? [];
unset($_SESSION['old_customer']);

$pageTitle = 'CRM Tornis - Ajouter un client';
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
                        <h1 class="h3 mb-2">Ajouter un client</h1>
                        <p class="text-muted mb-0">Renseignez les informations du client a ajouter au CRM.</p>
                    </div>
                    <a href="index.php" class="btn btn-outline-secondary">Retour</a>
                </div>

                <form action="store.php" method="post">
                    <div class="row g-3">

                        <div class="col-md-12">
                            <label for="name" class="form-label">Nom du client</label>
                            <input type="text" class="form-control" id="name" name="name" maxlength="100" required value="<?= htmlspecialchars($old['name'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" maxlength="150" required value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="tel" class="form-label">Telephone</label>
                            <input type="text" class="form-control" id="tel" name="tel" maxlength="20" required value="<?= htmlspecialchars($old['tel'] ?? '') ?>">
                        </div>

                        <div class="col-12">
                            <label for="address" class="form-label">Adresse</label>
                            <input type="text" class="form-control" id="address" name="address" rows="3" required value="<?= htmlspecialchars($old['address'] ?? '') ?>"/>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="index.php" class="btn btn-outline-secondary">Annuler</a>
                        <button type="submit" class="btn text-white" style="background-color: #0B3041;">Enregistrer le client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
