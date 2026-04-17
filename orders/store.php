<?php
// Cette page traite la creation complete d'une commande et de ses lignes.
session_start();
require_once __DIR__ . '/../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_message'] = 'Methode non autorisee.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$customerId = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
$quantities = $_POST['quantities'] ?? [];

$_SESSION['old_order'] = [
    'customer_id' => $_POST['customer_id'] ?? '',
    'quantities' => is_array($quantities) ? $quantities : [],
];

if ($customerId === false || $customerId === null || $customerId <= 0) {
    $_SESSION['flash_message'] = 'Veuillez selectionner un client valide.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: create.php');
    exit;
}

if (!is_array($quantities)) {
    $_SESSION['flash_message'] = 'Les quantites envoyees sont invalides.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: create.php');
    exit;
}

// On prepare une liste nettoyee des lignes qui ont une quantite strictement positive.
$selectedProducts = [];

foreach ($quantities as $productId => $quantity) {
    if (!ctype_digit((string) $productId)) {
        continue;
    }

    if (filter_var($quantity, FILTER_VALIDATE_INT) === false) {
        $_SESSION['flash_message'] = 'Toutes les quantites doivent etre des entiers.';
        $_SESSION['flash_type'] = 'danger';
        header('Location: create.php');
        exit;
    }

    if ((int) $quantity > 0) {
        $selectedProducts[(int) $productId] = (int) $quantity;
    }
}

if ($selectedProducts === []) {
    $_SESSION['flash_message'] = 'Veuillez selectionner au moins un produit avec une quantite positive.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: create.php');
    exit;
}

// On recharge les produits choisis depuis la base pour fiabiliser le prix et le stock.
$productIds = array_keys($selectedProducts);
$placeholders = implode(',', array_fill(0, count($productIds), '?'));

$customer = fetchOneFromDB(
    "SELECT
        c.id,
        c.name
    FROM customers c
    WHERE c.id = ?
    AND c.is_active = 1
    AND COALESCE(
        (SELECT MAX(o.order_date) FROM orders o WHERE o.customer_id = c.id),
        c.created_at
    ) >= DATE_SUB(NOW(), INTERVAL 2 YEAR)",
    [$customerId]
);

if ($customer === false) {
    $_SESSION['flash_message'] = 'Le client selectionne est introuvable ou inactif.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: create.php');
    exit;
}

$products = fetchManyFromDB(
    "SELECT id, name, price, stock FROM products WHERE id IN ($placeholders)",
    $productIds
);

if ($products === false || count($products) !== count($productIds)) {
    $_SESSION['flash_message'] = 'Un ou plusieurs produits selectionnes sont introuvables.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: create.php');
    exit;
}

$productsById = [];
$totalAmount = 0.0;

foreach ($products as $product) {
    $productsById[(int) $product['id']] = $product;
}

foreach ($selectedProducts as $productId => $quantity) {
    $product = $productsById[$productId];

    if ($quantity > (int) $product['stock']) {
        $_SESSION['flash_message'] = 'Le stock est insuffisant pour le produit ' . $product['name'] . '.';
        $_SESSION['flash_type'] = 'danger';
        header('Location: create.php');
        exit;
    }

    $totalAmount += (float) $product['price'] * $quantity;
}

try {
    // La transaction garantit que la commande, ses lignes et le stock restent coherents ensemble.
    $pdo->beginTransaction();

    $insertOrder = $pdo->prepare(
        'INSERT INTO orders (customer_id, order_date, status, total_amount) VALUES (:customer_id, NOW(), :status, :total_amount)'
    );
    $insertOrder->execute([
        ':customer_id' => $customerId,
        ':status' => 'En_attente',
        ':total_amount' => number_format($totalAmount, 2, '.', ''),
    ]);

    $orderId = (int) $pdo->lastInsertId();

    $insertItem = $pdo->prepare(
        'INSERT INTO order_items (order_id, product_id, quantity, unit_price, line_total) VALUES (:order_id, :product_id, :quantity, :unit_price, :line_total)'
    );
    $updateStock = $pdo->prepare(
        'UPDATE products SET stock = stock - :quantity WHERE id = :product_id'
    );

    foreach ($selectedProducts as $productId => $quantity) {
        $product = $productsById[$productId];
        $lineTotal = (float) $product['price'] * $quantity;

        $insertItem->execute([
            ':order_id' => $orderId,
            ':product_id' => $productId,
            ':quantity' => $quantity,
            ':unit_price' => number_format((float) $product['price'], 2, '.', ''),
            ':line_total' => number_format($lineTotal, 2, '.', ''),
        ]);

        $updateStock->execute([
            ':quantity' => $quantity,
            ':product_id' => $productId,
        ]);
    }

    $pdo->commit();

    unset($_SESSION['old_order']);
    $_SESSION['flash_message'] = 'La commande a ete enregistree avec succes.';
    $_SESSION['flash_type'] = 'success';
    header('Location: show.php?id=' . $orderId);
    exit;
} catch (Exception $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION['flash_message'] = 'Erreur lors de l\'enregistrement de la commande.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: create.php');
    exit;
}
