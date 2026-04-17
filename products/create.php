<?php
session_start();
require_once __DIR__ . '/../config/db_connect.php';

$old = $_SESSION['old_product'] ?? [];
unset($_SESSION['old_product']);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Tornis - Ajouter un produit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100 bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #0B3041 !important;">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php">CRM <span style="color: red;">T</span>ornis</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCRM"
                aria-controls="navbarCRM" aria-expanded="false" aria-label="Basculer la navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarCRM">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../customers.php">Clients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="create.php">Produits</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../orders.php">Commandes</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <?php if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_type']) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['flash_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="bg-white p-4 p-md-5 rounded-3">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h1 class="h3 mb-2">Ajouter un produit</h1>
                            <p class="text-muted mb-0">
                                Renseignez les informations du produit pour l'ajouter au catalogue.
                            </p>
                        </div>
                        <a href="../index.php" class="btn btn-outline-secondary">Retour</a>
                    </div>

                    <form action="store.php" method="post">
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
                                    value="<?= htmlspecialchars($old['name'] ?? '') ?>">
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
                                    value="<?= htmlspecialchars($old['code'] ?? '') ?>">
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea
                                    class="form-control"
                                    id="description"
                                    name="description"
                                    rows="4"
                                    placeholder="Décrivez brièvement le produit"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                            </div>

                            <div class="col-md-6">
                                <label for="price" class="form-label">Prix</label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input
                                        type="number"
                                        class="form-control"
                                        id="price"
                                        name="price"
                                        min="0"
                                        step="0.01"
                                        required
                                        value="<?= htmlspecialchars($old['price'] ?? '') ?>">
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
                                    value="<?= htmlspecialchars($old['stock'] ?? '0') ?>">
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="../index.php" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn text-white" style="background-color: #0B3041;">
                                Enregistrer le produit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white mt-auto">
        <div class="container py-3 text-center text-muted">
            &copy; 2026 <span style="color:red;"><strong>T</strong></span><span style="color:#0B3041;"><strong>ornis</strong></span> - CRM de gestion clientèle
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
