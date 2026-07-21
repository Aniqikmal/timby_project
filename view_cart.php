<?php
/**
 * Shopping Cart Controller
 * Handles item management, quantity updates, coupon validation, and total calculations.
 */

session_start();
include 'db_conn.php';

// ==========================================
// 1. ACTION HANDLERS (CONTROLLER LOGIC)
// ==========================================

// Action: Remove Item from Cart
if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header("Location: view_cart.php");
    exit();
}

// Action: Update Item Quantity
if (isset($_POST['update_qty'])) {
    $pid = $_POST['product_id'];
    $qty = intval($_POST['qty']); 

    // If quantity is valid, update session; otherwise remove item
    if ($qty > 0) {
        $_SESSION['cart'][$pid] = $qty; 
    } else {
        unset($_SESSION['cart'][$pid]); 
    }
    header("Location: view_cart.php");
    exit();
}

// Action: Apply Promotional Coupon
if (isset($_POST['apply_coupon'])) {
    $code = $_POST['coupon_code'];
    
    // Verify coupon against database
    $stmt = $conn->prepare("SELECT * FROM promotions WHERE code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $promo = $result->fetch_assoc();
        $_SESSION['discount_percent'] = $promo['discount_percent'];
        $_SESSION['promo_code'] = $code;
        $_SESSION['coupon_msg'] = "Coupon '" . htmlspecialchars($code) . "' applied! You get " . $promo['discount_percent'] . "% off.";
        $_SESSION['coupon_msg_type'] = "success";
    } else {
        $_SESSION['coupon_msg'] = "Invalid coupon code. Please try again.";
        $_SESSION['coupon_msg_type'] = "error";
        unset($_SESSION['discount_percent']);
        unset($_SESSION['promo_code']);
    }
    header("Location: view_cart.php");
    exit();
}

// ==========================================
// 2. DATA AGGREGATION & CALCULATIONS (MODEL LOGIC)
// ==========================================

$total_price = 0;
$total_qty = 0;
$shipping = 5.00;
$cart_items = [];

// Fetch product details for items currently in session cart
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    
    if (!empty($ids)) {
        $sql = "SELECT * FROM products WHERE product_id IN ($ids)";
        $result = $conn->query($sql);
        
        while($row = $result->fetch_assoc()) {
            $qty = $_SESSION['cart'][$row['product_id']];
            
            // Calculate line totals
            $subtotal = $row['price'] * $qty;
            $total_price += $subtotal;
            $total_qty += $qty;
            
            // Append qty to product data for display
            $row['qty'] = $qty;
            $cart_items[] = $row;
        }
    }
}

// Calculate Final Totals with Discounts
$discount_percent = isset($_SESSION['discount_percent']) ? $_SESSION['discount_percent'] : 0;
$discount_amount = ($total_price * ($discount_percent / 100));
$final_subtotal = $total_price - $discount_amount;

// Ensure grand total is not negative
$grand_total = ($final_subtotal > 0) ? ($final_subtotal + $shipping) : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Cart</title>
    <link rel="stylesheet" href="style.css"> 
    <style>
        /* Cart Layout Grid */
        .cart-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        
        /* Item List Components */
        .cart-list-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .cart-table { width: 100%; border-collapse: collapse; }
        .cart-table th { text-align: left; color: #2F4F4F; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .cart-table td { padding: 20px 0; border-bottom: 1px solid #f9f9f9; vertical-align: middle; }
        
        /* Product Thumbnail & Info */
        .product-info { display: flex; align-items: center; gap: 15px; }
        .product-thumb { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; background: #eee; }
        .remove-link { color: #d9534f; font-size: 0.8rem; text-decoration: none; }

        /* Order Summary Components */
        .summary-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); height: fit-content; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; color: #555; }
        .summary-total { display: flex; justify-content: space-between; margin-top: 20px; padding-top: 20px; border-top: 2px solid #eee; font-weight: bold; color: #2F4F4F; font-size: 1.1rem; }
        
        /* Coupon & Actions */
        .coupon-box { display: flex; gap: 5px; margin-bottom: 20px; }
        .coupon-input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px; }
        .btn-apply { background-color: #2F4F4F; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; }
        .btn-checkout { display: block; width: 100%; text-align: center; background-color: white; border: 1px solid #2F4F4F; color: #2F4F4F; font-weight: bold; padding: 12px; border-radius: 8px; cursor: pointer; text-decoration: none; margin-top: 20px; transition: 0.3s; }
        .btn-checkout:hover { background-color: #2F4F4F; color: white; }
    </style>
</head>
<body>

    <div class="dashboard-wrapper">
        <?php include 'sidebar_member.php'; ?>
        
        <main class="main-content">
            <h2 class="page-title">Shopping Cart</h2>

            <?php if (isset($_SESSION['coupon_msg'])): ?>
                <div class="alert <?php echo $_SESSION['coupon_msg_type']; ?>" style="margin-bottom:20px;">
                    <?php echo $_SESSION['coupon_msg']; ?>
                </div>
                <?php unset($_SESSION['coupon_msg']); unset($_SESSION['coupon_msg_type']); ?>
            <?php endif; ?>
            
            <div class="cart-grid">
                
                <div class="cart-list-card">
                    <h3 style="color:#2F4F4F; margin-bottom:20px;">List of Items</h3>
                    <table class="cart-table">
                        <thead><tr><th>Product</th><th style="text-align:center;">Qty</th></tr></thead>
                        <tbody>
                            <?php if (!empty($cart_items)): ?>
                                <?php foreach($cart_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="product-info">
                                            <img src="images/<?php echo $item['image']; ?>" class="product-thumb">
                                            <div>
                                                <div style="font-weight:bold;"><?php echo htmlspecialchars($item['name']); ?></div>
                                                <div style="color:#888;">RM <?php echo number_format($item['price'], 2); ?></div>
                                                <a href="view_cart.php?remove=<?php echo $item['product_id']; ?>" class="remove-link">Remove</a>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td style="text-align:center;">
                                        <form method="POST" style="display:flex; align-items:center; gap:5px; justify-content:center;">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <input type="number" name="qty" value="<?php echo $item['qty']; ?>" min="1" 
                                                   style="width:60px; padding:5px; border:1px solid #ccc; border-radius:5px; text-align:center;">
                                            <button type="submit" name="update_qty" 
                                                    style="background:#8B5E3C; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer; font-size:0.8rem; font-weight:bold;">
                                                Update
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="2" style="padding:20px; text-align:center;">Your cart is currently empty.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="summary-card">
                    <h3 style="color:#2F4F4F; margin-bottom:15px;">Order Summary</h3>
                    
                    <form method="POST" class="coupon-box">
                        <input type="text" name="coupon_code" class="coupon-input" placeholder="Coupon Code" value="<?php echo isset($_SESSION['promo_code']) ? $_SESSION['promo_code'] : ''; ?>">
                        <button type="submit" name="apply_coupon" class="btn-apply">Apply</button>
                    </form>

                    <div class="summary-row"><span>Total QTY.</span><span><?php echo $total_qty; ?></span></div>
                    <div class="summary-row"><span>Subtotal</span><span>RM <?php echo number_format($total_price, 2); ?></span></div>
                    
                    <?php if($discount_percent > 0): ?>
                        <div class="summary-row" style="color:green; font-weight:bold;">
                            <span>Discount (<?php echo $discount_percent; ?>%)</span>
                            <span>- RM <?php echo number_format($discount_amount, 2); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="summary-row"><span>Shipping</span><span>RM <?php echo number_format($total_price > 0 ? $shipping : 0, 2); ?></span></div>
                    
                    <div class="summary-total">
                        <span>Total Price</span>
                        <span>RM <?php echo number_format($grand_total, 2); ?></span>
                    </div>
                    
                    <?php if ($total_price > 0): ?>
                        <a href="checkout.php" class="btn-checkout">Proceed to Checkout</a>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>
</body>
</html>