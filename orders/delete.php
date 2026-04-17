<?php
// La suppression doit aussi restaurer le stock des produits vendus.
session_start();
require_once __DIR__ . '/../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_message'] = 'Methode non autorisee.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$orderId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($orderId === false || $orderId === null || $orderId <= 0) {
    $_SESSION['flash_message'] = 'Commande introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$order = fetchOneFromDB('SELECT id FROM orders WHERE id = :id', [':id' => $orderId]);

if ($order === false) {
    $_SESSION['flash_message'] = 'Commande introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$items = fetchManyFromDB(
    'SELECT product_id, quantity FROM order_items WHERE order_id = :order_id',
    [':order_id' => $orderId]
);

try {
    // La transaction garantit que le stock et la suppression restent synchronises.
    $pdo->beginTransaction();

    if ($items !== false) {
        $restoreStock = $pdo->prepare(
            'UPDATE products SET stock = stock + :quantity WHERE id = :product_id'
        );

        foreach ($items as $item) {
            $restoreStock->execute([
                ':quantity' => (int) $item['quantity'],
                ':product_id' => (int) $item['product_id'],
            ]);
        }
    }

    $deleteItems = $pdo->prepare('DELETE FROM order_items WHERE order_id = :order_id');
    $deleteItems->execute([':order_id' => $orderId]);

    $deleteOrder = $pdo->prepare('DELETE FROM orders WHERE id = :id');
    $deleteOrder->execute([':id' => $orderId]);

    $pdo->commit();

    $_SESSION['flash_message'] = 'La commande a ete supprimee avec succes.';
    $_SESSION['flash_type'] = 'success';
    header('Location: index.php');
    exit;
} catch (Exception $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION['flash_message'] = 'Erreur lors de la suppression de la commande.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: show.php?id=' . $orderId);
    exit;
}
