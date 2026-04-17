<?php
// Le dashboard regroupe quelques indicateurs et deux listes courtes pour aider a piloter l'activite.
$dashboardCounters = fetchOneFromDB(
    "SELECT
        (SELECT COUNT(*) FROM customers) AS total_customers,
        (SELECT COUNT(*) FROM products) AS total_products,
        (SELECT COUNT(*) FROM orders WHERE status IN ('En_attente', 'en_attente')) AS pending_orders,
        (SELECT COUNT(*) FROM customers c
            WHERE c.is_active = 0
            OR COALESCE(
                (SELECT MAX(o.order_date) FROM orders o WHERE o.customer_id = c.id),
                c.created_at
            ) < DATE_SUB(NOW(), INTERVAL 2 YEAR)
        ) AS inactive_customers,
        (SELECT COUNT(*) FROM products WHERE stock BETWEEN 1 AND 5) AS low_stock_products,
        (SELECT COUNT(*) FROM products WHERE stock <= 0) AS out_of_stock_products,
        (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status IN ('ValidÃ©e', 'Validée', 'Validée')) AS validated_revenue"
);

$recentOrders = fetchManyFromDB(
    "SELECT o.id, o.order_date, o.status, o.total_amount, c.name AS customer_name
    FROM orders o
    INNER JOIN customers c ON c.id = o.customer_id
    ORDER BY o.order_date DESC, o.id DESC
    LIMIT 5"
);

$stockAlerts = fetchManyFromDB(
    "SELECT id, name, stock
    FROM products
    WHERE stock <= 5
    ORDER BY stock ASC, name ASC
    LIMIT 5"
);

$totalCustomers = (int) ($dashboardCounters['total_customers'] ?? 0);
$totalProducts = (int) ($dashboardCounters['total_products'] ?? 0);
$pendingOrders = (int) ($dashboardCounters['pending_orders'] ?? 0);
$inactiveCustomers = (int) ($dashboardCounters['inactive_customers'] ?? 0);
$lowStockProducts = (int) ($dashboardCounters['low_stock_products'] ?? 0);
$outOfStockProducts = (int) ($dashboardCounters['out_of_stock_products'] ?? 0);
$validatedRevenue = (float) ($dashboardCounters['validated_revenue'] ?? 0);
$activeCustomers = max(0, $totalCustomers - $inactiveCustomers);
?>

<div class="bg-white p-4 rounded-3">
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="border rounded-3 p-3 h-100">
                <div class="text-muted small">Clients actifs</div>
                <div class="fw-bold fs-3"><?= htmlspecialchars((string) $activeCustomers) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="border rounded-3 p-3 h-100">
                <div class="text-muted small">Produits references</div>
                <div class="fw-bold fs-3"><?= htmlspecialchars((string) $totalProducts) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="border rounded-3 p-3 h-100">
                <div class="text-muted small">Commandes en attente</div>
                <div class="fw-bold fs-3"><?= htmlspecialchars((string) $pendingOrders) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="border rounded-3 p-3 h-100">
                <div class="text-muted small">CA des commandes Validées</div>
                <div class="fw-bold fs-4"><?= htmlspecialchars(number_format($validatedRevenue, 0, ',', ' ')) ?> FCFA</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="border rounded-3 p-3 h-100">
                <div class="text-muted small">Clients inactifs</div>
                <div class="fw-bold fs-4"><?= htmlspecialchars((string) $inactiveCustomers) ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded-3 p-3 h-100">
                <div class="text-muted small">Produits en stock faible</div>
                <div class="fw-bold fs-4 text-warning"><?= htmlspecialchars((string) $lowStockProducts) ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded-3 p-3 h-100">
                <div class="text-muted small">Produits en rupture</div>
                <div class="fw-bold fs-4 text-danger"><?= htmlspecialchars((string) $outOfStockProducts) ?></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="border rounded-3 p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0">Dernieres commandes</h2>
                    <a href="orders/index.php" class="small text-decoration-none">Voir tout</a>
                </div>

                <?php if ($recentOrders === false || $recentOrders === []): ?>
                    <p class="text-muted mb-0">Aucune commande recente a afficher.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Client</th>
                                    <th>Statut</th>
                                    <th class="text-end">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $recentOrder): ?>
                                    <tr>
                                        <td>CMD-<?= htmlspecialchars(str_pad((string) $recentOrder['id'], 4, '0', STR_PAD_LEFT)) ?></td>
                                        <td><?= htmlspecialchars($recentOrder['customer_name']) ?></td>
                                        <td>
                                            <?php if (in_array($recentOrder['status'], ['En_attente', 'en_attente'], true)): ?>
                                                <span class="badge bg-warning text-dark">En attente</span>
                                            <?php elseif (in_array($recentOrder['status'], ['ValidÃ©e', 'Validée', 'Validée'], true)): ?>
                                                <span class="badge bg-success">Validée</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Annulée</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end"><?= htmlspecialchars(number_format((float) $recentOrder['total_amount'], 0, ',', ' ')) ?> FCFA</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="border rounded-3 p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0">Alertes stock</h2>
                    <a href="products/index.php?stock_status=low" class="small text-decoration-none">Gerer le stock</a>
                </div>

                <?php if ($stockAlerts === false || $stockAlerts === []): ?>
                    <p class="text-muted mb-0">Aucune alerte stock pour le moment.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($stockAlerts as $alertProduct): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><?= htmlspecialchars($alertProduct['name']) ?></span>
                                    <?php if ((int) $alertProduct['stock'] <= 0): ?>
                                        <span class="badge bg-danger">Rupture</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><?= htmlspecialchars((string) $alertProduct['stock']) ?> restant(s)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
