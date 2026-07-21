<?php
session_start();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to Cart
if (isset($_POST['add_to_cart'])) {
    $pid = intval($_POST['product_id']); // Sanitize input
    if ($pid > 0) {
        $qty = 1; 
        if (isset($_SESSION['cart'][$pid])) {
            $_SESSION['cart'][$pid] += $qty;
        } else {
            $_SESSION['cart'][$pid] = $qty;
        }
    }
    header("Location: view_cart.php");
    exit(); // Always exit after redirect
}

// Remove from Cart
if (isset($_GET['remove'])) {
    $pid = intval($_GET['remove']); // Sanitize input
    unset($_SESSION['cart'][$pid]);
    header("Location: view_cart.php");
    exit(); // Always exit after redirect
}
?>