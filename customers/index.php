<?php
// Cette requete calcule aussi un statut "effectif" pour tenir compte
// de l'inactivation automatique apres 2 ans sans activite.
session_start();
require_once __DIR__ . '/../config/db_connect.php';

$search = trim($_GET['search'] ?? '');

// On construit la requete progressivement pour pouvoir ajouter une recherche libre.
$query = "SELECT
        c.id,
        c.name,
        c.email,
        c.tel,
        c.address,
        c.is_active,
        c.created_at,
        COALESCE(MAX(o.order_date), c.created_at) AS last_activity_at,
        CASE
            WHEN c.is_active = 0 THEN 0
            WHEN COALESCE(MAX(o.order_date), c.created_at) < DATE_SUB(NOW(), INTERVAL 2 YEAR) THEN 0
            ELSE 1
        END AS effective_is_active
    FROM customers c
    LEFT JOIN orders o ON o.customer_id = c.id";

$queryParams = [];

if ($search !== '') {
    // Une seule saisie permet de rechercher dans plusieurs colonnes utiles.
    $query .= "
    WHERE
        c.name LIKE :search
        OR c.email LIKE :search
        OR c.tel LIKE :search
        OR c.address LIKE :search";
    $queryParams[':search'] = '%' . $search . '%';
}

$query .= "
    GROUP BY c.id, c.name, c.email, c.tel, c.address, c.is_active, c.created_at
    ORDER BY c.created_at DESC, c.id DESC";

$customers = fetchManyFromDB($query, $queryParams);

$pageTitle = 'CRM Tornis - Clients';
$activePage = 'customers';
$basePath = '../';

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<main class="container my-5">
    <div class="bg-white p-4 p-md-5 rounded-3">
        <?php require __DIR__ . '/../partials/flash.php'; ?>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-2">Liste des clients</h1>
                <p class="text-muted mb-0">Consultez vos clients et leur statut d'activite.</p>
            </div>
            <a href="create.php" class="btn text-white" style="background-color: #0B3041;">
                Ajouter un client
            </a>
        </div>

        <form method="get" class="row g-3 align-items-end mb-4">
            <div class="col-md-8">
                <label for="search" class="form-label">Recherche client</label>
                <input
                    type="text"
                    class="form-control"
                    id="search"
                    name="search"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Nom, email, telephone ou adresse">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn text-white" style="background-color: #0B3041;">Rechercher</button>
                <a href="index.php" class="btn btn-outline-secondary">Reinitialiser</a>
            </div>
        </form>

        <?php if ($customers === false): ?>
            <div class="alert alert-danger mb-0" role="alert">
                Une erreur est survenue lors du chargement des clients.
            </div>
        <?php elseif ($customers === []): ?>
            <div class="border rounded-3 p-4 text-center text-muted">
                <?= $search !== '' ? 'Aucun client ne correspond a votre recherche.' : 'Aucun client n\'est encore enregistre.' ?>
            </div>
        <?php else: ?>
            <!-- Le tableau sert de vue synthese pour consulter et administrer les clients. -->
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Nom</th>
                            <th scope="col">Email</th>
                            <th scope="col">Telephone</th>
                            <th scope="col">Adresse</th>
                            <th scope="col" class="text-center">Statut</th>
                            <th scope="col">Derniere activite</th>
                            <th scope="col">Ajoute le</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td class="fw-semibold py-3"><?= htmlspecialchars($customer['name']) ?></td>
                                <td class="py-3"><?= htmlspecialchars($customer['email']) ?></td>
                                <td class="py-3"><?= htmlspecialchars($customer['tel']) ?></td>
                                <td class="py-3"><?= htmlspecialchars($customer['address']) ?></td>
                                <td class="text-center py-3">
                                    <?php if ((int) $customer['effective_is_active'] === 1): ?>
                                        <span class="badge bg-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3"><?= htmlspecialchars(date('d/m/Y', strtotime($customer['last_activity_at']))) ?></td>
                                <td class="py-3"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($customer['created_at']))) ?></td>
                                <td class="text-end text-nowrap py-3">
                                    <a href="edit.php?id=<?= htmlspecialchars((string) $customer['id']) ?>" class="btn btn-sm text-secondary border-0" title="Modifier le client" aria-label="Modifier le client">
                                        <i class="bi bi-pencil"></i>
                                        <span class="visually-hidden">Modifier</span>
                                    </a>
                                    <button
                                        type="button"
                                        class="btn btn-sm text-danger border-0"
                                        title="Supprimer le client"
                                        aria-label="Supprimer le client"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteCustomerModal"
                                        data-customer-id="<?= htmlspecialchars((string) $customer['id']) ?>"
                                        data-customer-name="<?= htmlspecialchars($customer['name']) ?>">
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

<div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-labelledby="deleteCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="deleteCustomerModalLabel">Supprimer le client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Voulez-vous vraiment supprimer ce client ?</p>
                <p class="mb-0 fw-semibold" id="deleteCustomerName"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <form action="delete.php" method="post" class="m-0">
                    <input type="hidden" name="id" id="deleteCustomerId">
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var deleteModal = document.getElementById('deleteCustomerModal');

        if (!deleteModal) {
            return;
        }

        deleteModal.addEventListener('show.bs.modal', function (event) {
            var triggerButton = event.relatedTarget;
            deleteModal.querySelector('#deleteCustomerId').value = triggerButton.getAttribute('data-customer-id');
            deleteModal.querySelector('#deleteCustomerName').textContent = triggerButton.getAttribute('data-customer-name');
        });
    });
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
