<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Вы не вошли в аккаунт!";
    exit;
}

if (isset($_GET['id'])) {
    $cart_id = $_GET['id'];

    // Удаляем товар из корзины
    $stmt = $pdo->prepare('DELETE FROM cart WHERE id = ?');
    $stmt->execute([$cart_id]);

    header('Location: cart.php');
    exit;
}
?>
