<?php
// Cette page affiche l'entete de la commande et le detail de ses lignes.
session_start();
require_once __DIR__ . '/../config/db_connect.php';

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($orderId === false || $orderId === null || $orderId <= 0) {
    $_SESSION['flash_message'] = 'Commande introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$order = fetchOneFromDB(
    "SELECT
        o.id,
        o.order_date,
        o.status,
        o.total_amount,
        c.name AS customer_name,
        c.email AS customer_email,
        c.tel AS customer_tel
    FROM orders o
    INNER JOIN customers c ON c.id = o.customer_id
    WHERE o.id = :id",
    [':id' => $orderId]
);

if ($order === false) {
    $_SESSION['flash_message'] = 'Commande introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$items = fetchManyFromDB(
    "SELECT
        oi.quantity,
        oi.unit_price,
        oi.line_total,
        p.name AS product_name,
        p.code AS product_code
    FROM order_items oi
    INNER JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = :order_id
    ORDER BY oi.id ASC",
    [':order_id' => $orderId]
);

$pageTitle = 'CRM Tornis - Detail commande';
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

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-2">Commande CMD-<?= htmlspecialchars(str_pad((string) $order['id'], 4, '0', STR_PAD_LEFT)) ?></h1>
                <p class="text-muted mb-0">Consultez l'entete de commande puis le detail des articles vendus.</p>
            </div>
            <div class="d-flex gap-2">
                <!-- Le changement de statut passe par une modale pour garder l'utilisateur sur la page detail. -->
                <button
                    type="button"
                    class="btn text-white"
                    style="background-color: #0B3041;"
                    data-bs-toggle="modal"
                    data-bs-target="#updateOrderStatusModal">
                    Changer le statut
                </button>
                <a href="index.php" class="btn btn-outline-secondary">Retour</a>
                <form action="delete.php" method="post" class="m-0">
                    <input type="hidden" name="id" value="<?= htmlspecialchars((string) $order['id']) ?>">
                    <button type="submit" class="btn btn-outline-danger">Supprimer</button>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100">
                    <div class="text-muted small mb-1">Client</div>
                    <div class="fw-semibold"><?= htmlspecialchars($order['customer_name']) ?></div>
                    <div><?= htmlspecialchars($order['customer_email']) ?></div>
                    <div><?= htmlspecialchars($order['customer_tel']) ?></div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100">
                    <div class="text-muted small mb-1">Commande</div>
                    <div class="fw-semibold"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($order['order_date']))) ?></div>
                    <div>
                        Statut :
                        <?php if ($order['status'] === 'en_attente'): ?>
                            <span class="badge bg-warning text-dark">En attente</span>
                        <?php elseif ($order['status'] === 'Validée'): ?>
                            <span class="badge bg-success">Validée</span>
                        <?php elseif ($order['status'] === 'Annulée'): ?>
                            <span class="badge bg-danger">Annulée</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= htmlspecialchars($order['status']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div>Montant total : <?= htmlspecialchars(number_format((float) $order['total_amount'], 0, ',', ' ')) ?> FCFA</div>
                </div>
            </div>
        </div>

        <?php if ($items === false || $items === []): ?>
            <div class="alert alert-warning mb-0" role="alert">
                Aucun article n'est rattache a cette commande.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Produit</th>
                            <th scope="col">Code</th>
                            <th scope="col" class="text-center">Quantite</th>
                            <th scope="col" class="text-end">Prix unitaire</th>
                            <th scope="col" class="text-end">Total ligne</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="py-3 fw-semibold"><?= htmlspecialchars($item['product_name']) ?></td>
                                <td class="py-3"><?= htmlspecialchars($item['product_code']) ?></td>
                                <td class="py-3 text-center"><?= htmlspecialchars((string) $item['quantity']) ?></td>
                                <td class="py-3 text-end"><?= htmlspecialchars(number_format((float) $item['unit_price'], 0, ',', ' ')) ?> FCFA</td>
                                <td class="py-3 text-end"><?= htmlspecialchars(number_format((float) $item['line_total'], 0, ',', ' ')) ?> FCFA</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

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
                    <strong>CMD-<?= htmlspecialchars(str_pad((string) $order['id'], 4, '0', STR_PAD_LEFT)) ?></strong>.
                </p>

                <!-- Le formulaire reste dans la modale pour limiter les allers-retours dans l'interface. -->
                <form action="update_status.php" method="post" id="updateOrderStatusForm">
                    <input type="hidden" name="id" value="<?= htmlspecialchars((string) $order['id']) ?>">
                    <input type="hidden" name="redirect_to" value="show.php?id=<?= htmlspecialchars((string) $order['id']) ?>">

                    <label for="status" class="form-label">Nouveau statut</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="en_attente" <?= $order['status'] === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                        <option value="validée" <?= $order['status'] === 'validée' ? 'selected' : '' ?>>Validée</option>
                        <option value="annulée" <?= $order['status'] === 'annulée' ? 'selected' : '' ?>>Annulée</option>
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

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
