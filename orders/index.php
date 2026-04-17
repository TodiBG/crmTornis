<?php
// Cette page liste les commandes avec les informations utiles pour les consulter rapidement.
session_start();
require_once __DIR__ . '/../config/db_connect.php';

$statusFilter = trim($_GET['status'] ?? '');
$customerFilter = filter_input(INPUT_GET, 'customer_id', FILTER_VALIDATE_INT);
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');

// Ces options alimentent le select de filtre et servent aussi de documentation de la logique.
$statusGroups = [
    'pending' => ['En_attente', 'en_attente'],
    'validated' => ['ValidÃ©e', 'Validée', 'Validée'],
    'cancelled' => ['AnnulÃ©e', 'Annulée', 'Annulée'],
];

$customers = fetchManyFromDB('SELECT id, name FROM customers ORDER BY name ASC') ?: [];

$query = "SELECT
        o.id,
        o.order_date,
        o.status,
        o.total_amount,
        o.created_at,
        c.name AS customer_name
    FROM orders o
    INNER JOIN customers c ON c.id = o.customer_id
    WHERE 1 = 1";

$queryParams = [];

if ($statusFilter !== '' && isset($statusGroups[$statusFilter])) {
    // On construit autant de placeholders qu'il y a de variantes de statut a accepter.
    $statusPlaceholders = [];

    foreach ($statusGroups[$statusFilter] as $index => $statusValue) {
        $placeholder = ':status_' . $index;
        $statusPlaceholders[] = $placeholder;
        $queryParams[$placeholder] = $statusValue;
    }

    $query .= ' AND o.status IN (' . implode(', ', $statusPlaceholders) . ')';
}

if ($customerFilter !== false && $customerFilter !== null && $customerFilter > 0) {
    $query .= ' AND o.customer_id = :customer_id';
    $queryParams[':customer_id'] = $customerFilter;
}

if ($dateFrom !== '') {
    $query .= ' AND DATE(o.order_date) >= :date_from';
    $queryParams[':date_from'] = $dateFrom;
}

if ($dateTo !== '') {
    $query .= ' AND DATE(o.order_date) <= :date_to';
    $queryParams[':date_to'] = $dateTo;
}

$query .= ' ORDER BY o.order_date DESC, o.id DESC';

$orders = fetchManyFromDB($query, $queryParams);

$pageTitle = 'CRM Tornis - Commandes';
$activePage = 'orders';
$basePath = '../';

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<main class="container my-5">
    <div class="bg-white p-4 p-md-5 rounded-3">
        <?php require __DIR__ . '/../partials/flash.php'; ?>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-2">Liste des commandes</h1>
                <p class="text-muted mb-0">Consultez les commandes, leur client et leur montant total.</p>
            </div>
            <a href="create.php" class="btn text-white" style="background-color: #0B3041;">
                Ajouter une commande
            </a>
        </div>

        <form method="get" class="row g-3 align-items-end mb-4">
            <div class="col-md-3">
                <label for="status" class="form-label">Statut</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Tous</option>
                    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>En attente</option>
                    <option value="validated" <?= $statusFilter === 'validated' ? 'selected' : '' ?>>Validées</option>
                    <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Annulées</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="customer_id" class="form-label">Client</label>
                <select class="form-select" id="customer_id" name="customer_id">
                    <option value="">Tous</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?= htmlspecialchars((string) $customer['id']) ?>" <?= (string) $customerFilter === (string) $customer['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($customer['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">Du</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">Au</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?= htmlspecialchars($dateTo) ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn text-white" style="background-color: #0B3041;">Filtrer</button>
                <a href="index.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <?php if ($orders === false): ?>
            <div class="alert alert-danger mb-0" role="alert">
                Une erreur est survenue lors du chargement des commandes.
            </div>
        <?php elseif ($orders === []): ?>
            <div class="border rounded-3 p-4 text-center text-muted">
                Aucune commande ne correspond aux filtres selectionnes.
            </div>
        <?php else: ?>
            <!-- Le tableau donne une vue synthese du cycle commercial. -->
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Reference</th>
                            <th scope="col">Client</th>
                            <th scope="col">Date</th>
                            <th scope="col">Statut</th>
                            <th scope="col" class="text-end">Montant total</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="fw-semibold py-3">CMD-<?= htmlspecialchars(str_pad((string) $order['id'], 4, '0', STR_PAD_LEFT)) ?></td>
                                <td class="py-3"><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td class="py-3"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($order['order_date']))) ?></td>
                                <td class="py-3">
                                    <?php if (in_array($order['status'], ['En_attente', 'en_attente'], true)): ?>
                                        <span class="badge bg-warning text-dark">En attente</span>
                                    <?php elseif (in_array($order['status'], ['ValidÃ©e', 'Validée', 'Validée'], true)): ?>
                                        <span class="badge bg-success">Validée</span>
                                    <?php elseif (in_array($order['status'], ['AnnulÃ©e', 'Annulée', 'Annulée'], true)): ?>
                                        <span class="badge bg-danger">Annulée</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($order['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end py-3"><?= htmlspecialchars(number_format((float) $order['total_amount'], 0, ',', ' ')) ?> FCFA</td>
                                <td class="text-end text-nowrap py-3">
                                    <a href="show.php?id=<?= htmlspecialchars((string) $order['id']) ?>" class="btn btn-sm text-dark border-0" title="Voir la commande" aria-label="Voir la commande">
                                        <i class="bi bi-eye"></i>
                                        <span class="visually-hidden">Voir</span>
                                    </a>
                                    <button
                                        type="button"
                                        class="btn btn-sm text-primary border-0"
                                        title="Changer le statut"
                                        aria-label="Changer le statut"
                                        data-bs-toggle="modal"
                                        data-bs-target="#updateOrderStatusModal"
                                        data-order-id="<?= htmlspecialchars((string) $order['id']) ?>"
                                        data-order-label="CMD-<?= htmlspecialchars(str_pad((string) $order['id'], 4, '0', STR_PAD_LEFT)) ?>"
                                        data-order-status="<?= htmlspecialchars($order['status']) ?>">
                                        <i class="bi bi-arrow-repeat"></i>
                                        <span class="visually-hidden">Changer le statut</span>
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-sm text-danger border-0"
                                        title="Supprimer la commande"
                                        aria-label="Supprimer la commande"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteOrderModal"
                                        data-order-id="<?= htmlspecialchars((string) $order['id']) ?>"
                                        data-order-label="CMD-<?= htmlspecialchars(str_pad((string) $order['id'], 4, '0', STR_PAD_LEFT)) ?>">
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

<div class="modal fade" id="deleteOrderModal" tabindex="-1" aria-labelledby="deleteOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="deleteOrderModalLabel">Supprimer la commande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Voulez-vous vraiment supprimer cette commande ?</p>
                <p class="mb-0 fw-semibold" id="deleteOrderName"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <form action="delete.php" method="post" class="m-0">
                    <input type="hidden" name="id" id="deleteOrderId">
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="updateOrderStatusModal" tabindex="-1" aria-labelledby="updateOrderStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="updateOrderStatusModalLabel">Changer le statut</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">
                    Selectionnez le nouveau statut pour la commande
                    <strong id="updateOrderStatusName"></strong>.
                </p>

                <!-- Une modale unique est reutilisee pour toutes les lignes du tableau. -->
                <form action="update_status.php" method="post" id="updateOrderStatusForm">
                    <input type="hidden" name="id" id="updateOrderStatusId">
                    <input type="hidden" name="redirect_to" value="index.php">

                    <label for="updateOrderStatusValue" class="form-label">Nouveau statut</label>
                    <select class="form-select" id="updateOrderStatusValue" name="status" required>
                        <option value="En_attente">En attente</option>
                        <option value="ValidÃ©e">Validée</option>
                        <option value="AnnulÃ©e">Annulée</option>
                    </select>
                </form>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-success" form="updateOrderStatusForm">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var deleteModal = document.getElementById('deleteOrderModal');
        var updateStatusModal = document.getElementById('updateOrderStatusModal');

        if (!deleteModal) {
            return;
        }

        deleteModal.addEventListener('show.bs.modal', function (event) {
            var triggerButton = event.relatedTarget;
            deleteModal.querySelector('#deleteOrderId').value = triggerButton.getAttribute('data-order-id');
            deleteModal.querySelector('#deleteOrderName').textContent = triggerButton.getAttribute('data-order-label');
        });

        if (!updateStatusModal) {
            return;
        }

        updateStatusModal.addEventListener('show.bs.modal', function (event) {
            var triggerButton = event.relatedTarget;
            updateStatusModal.querySelector('#updateOrderStatusId').value = triggerButton.getAttribute('data-order-id');
            updateStatusModal.querySelector('#updateOrderStatusName').textContent = triggerButton.getAttribute('data-order-label');
            updateStatusModal.querySelector('#updateOrderStatusValue').value = triggerButton.getAttribute('data-order-status');
        });
    });
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
