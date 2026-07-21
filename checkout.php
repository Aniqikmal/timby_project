<?php
session_start();
include 'db_conn.php';

//Security Checks
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
if (empty($_SESSION['cart'])) { header("Location: index.php"); exit(); }

//Calculate totals
$total_price = 0;
$shipping = 5.00;
$cart_items = [];
$discount_percent = isset($_SESSION['discount_percent']) ? $_SESSION['discount_percent'] : 0;

$ids = implode(',', array_keys($_SESSION['cart']));
$sql = "SELECT * FROM products WHERE product_id IN ($ids)";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()) {
    $qty = $_SESSION['cart'][$row['product_id']];
    $subtotal = $row['price'] * $qty;
    $total_price += $subtotal;
    $row['qty'] = $qty;
    $cart_items[] = $row;
}

$discount_amount = ($total_price * ($discount_percent / 100));
$grand_total = ($total_price - $discount_amount) + $shipping;

$error_msg = "";

// HANDLE ORDER SUBMISSION 
if (isset($_POST['place_order'])) {
    
    // Validate Inputs
    $postcode = $_POST['postcode'];
    $raw_card = $_POST['card_number'];
    $clean_card = str_replace(' ', '', $raw_card);
    $cvv = $_POST['cvv'];
    
    // Address Parts
    $addr_street = $_POST['address'];
    $addr_city = $_POST['city'];
    
    // Security Validation
    if (!preg_match("/^[0-9]{5}$/", $postcode)) {
        $error_msg = "Error: Postcode must be exactly 5 digits.";
    } elseif (!preg_match("/^[0-9]{16}$/", $clean_card)) { 
        $error_msg = "Error: Card number must be 16 digits.";
    } elseif (!preg_match("/^[0-9]{3}$/", $cvv)) {
        $error_msg = "Error: CVV must be 3 digits.";
    } else {
        // SAVE TO DB 
        $user_id = $_SESSION['user_id'];
        $date = date("Y-m-d");
        $status = "Pending";
        $full_address = "$addr_street, $addr_city, $postcode";

        // 1. Calculate Total Quantity
        $total_qty = array_sum($_SESSION['cart']); 

        // 2. Create a List of all Item Names      
        $item_names = [];
        foreach ($cart_items as $item) {
            $item_names[] = $item['name'];
        }
        $order_name = implode(", ", $item_names); 

        // 3. Use the image of the first item
        $order_img = $cart_items[0]['image'];

        // INSERT into Database
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, trans_date, amount, status, product_image, product_name, delivery_address, total_qty) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdssssi", $user_id, $date, $grand_total, $status, $order_img, $order_name, $full_address, $total_qty);
        
        if ($stmt->execute()) {
            $order_id = $stmt->insert_id;
            unset($_SESSION['cart']);
            unset($_SESSION['discount_percent']);
            unset($_SESSION['promo_code']);
            header("Location: receipt.php?order_id=$order_id");
            exit();
        } else {
            $error_msg = "Database Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Secure Checkout - Timby</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Georgia', serif; background-color: #F5F5DC; color: #333; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px; }
        .checkout-container { background: white; width: 100%; max-width: 900px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; display: flex; flex-direction: row; }
        
        .checkout-form-area { padding: 40px; flex: 1.5; }
        h2 { color: #8B5E3C; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        h3 { color: #5D4037; margin-top: 30px; margin-bottom: 15px; font-size: 1.1rem; }
        label { display: block; font-weight: bold; font-size: 0.85rem; color: #666; margin-bottom: 5px; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 8px; background-color: #FDF5E6; outline: none; transition: 0.3s; }
        input:focus { border-color: #8B5E3C; background: white; }
        input:invalid { border-color: #ff6b6b; }
        .row { display: flex; gap: 15px; } .col { flex: 1; }

        .checkout-summary-area { background-color: #F9F3E8; padding: 40px; flex: 1; border-left: 1px solid #eee; display: flex; flex-direction: column; }
        .summary-item { display: flex; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #e0d8cc; padding-bottom: 15px; }
        .summary-img { width: 50px; height: 50px; border-radius: 5px; background: white; object-fit: cover; margin-right: 15px; }
        .summary-details { flex-grow: 1; font-size: 0.9rem; }
        .summary-price { font-weight: bold; color: #8B5E3C; }
        .totals { margin-top: auto; padding-top: 20px; border-top: 2px solid #dcc; }
        .totals-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.95rem; color: #555; }
        .grand-total { font-weight: bold; font-size: 1.3rem; color: #2F4F4F; margin-top: 10px; }

        .btn-pay { background-color: #8B5E3C; color: white; width: 100%; padding: 15px; border: none; border-radius: 8px; font-size: 1rem; font-weight: bold; cursor: pointer; margin-top: 20px; transition: 0.3s; }
        .btn-pay:hover { background-color: #6d4a2f; }
        .btn-cancel { display: block; text-align: center; margin-top: 15px; color: #888; text-decoration: none; font-size: 0.9rem; }
        .error-banner { background-color: #ffe6e6; color: #d63031; padding: 10px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ff7675; text-align: center; font-size: 0.9rem; }
        @media (max-width: 768px) { .checkout-container { flex-direction: column; } .checkout-summary-area { order: -1; } }
    </style>
</head>
<body>

    <div class="checkout-container">
        
        <div class="checkout-form-area">
            <h2>Secure Checkout</h2>

            <?php if(!empty($error_msg)): ?>
                <div class="error-banner">⚠️ <?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form method="POST">
                <h3>1. Shipping Details</h3>
                <label>Street Address</label>
                <input type="text" name="address" placeholder="123 Jalan Kayu" required>
                <div class="row">
                    <div class="col"><label>City</label><input type="text" name="city" placeholder="Sibu" required></div>
                    <div class="col">
                        <label>Postcode</label>
                        <input type="text" name="postcode" placeholder="96000" pattern="\d{5}" maxlength="5" title="5 digits" required>
                    </div>
                </div>

                <h3>2. Payment Method (Dummy)</h3>
                <label>Card Number</label>
                <input type="text" name="card_number" id="card_number" placeholder="0000 0000 0000 0000" maxlength="19" required>
                
                <div class="row">
                    <div class="col">
                        <label>MM/YY</label>
                        <input type="text" name="expiry" id="expiry" placeholder="01/25" pattern="(0[1-9]|1[0-2])\/[0-9]{2}" maxlength="5" title="Format: MM/YY" required>
                    </div>
                    <div class="col">
                        <label>CVV</label>
                        <input type="text" name="cvv" placeholder="123" pattern="\d{3}" maxlength="3" title="3 digits" required>
                    </div>
                </div>

                <button type="submit" name="place_order" class="btn-pay">Pay RM <?php echo number_format($grand_total, 2); ?></button>
                <a href="view_cart.php" class="btn-cancel">Cancel</a>
            </form>
        </div>

        <div class="checkout-summary-area">
            <h3 style="margin-top:0;">Order Summary</h3>
            <?php foreach($cart_items as $item): ?>
            <div class="summary-item">
                <img src="images/<?php echo $item['image']; ?>" class="summary-img" alt="Product">
                <div class="summary-details">
                    <div style="font-weight:bold;"><?php echo htmlspecialchars($item['name']); ?></div>
                    <div style="color:#888;">Qty: <?php echo $item['qty']; ?></div>
                </div>
                <div class="summary-price">RM <?php echo number_format($item['price'] * $item['qty'], 2); ?></div>
            </div>
            <?php endforeach; ?>

            <div class="totals">
                <div class="totals-row"><span>Subtotal</span><span>RM <?php echo number_format($total_price, 2); ?></span></div>
                <?php if($discount_percent > 0): ?>
                    <div class="totals-row" style="color:green; font-weight:bold;">
                        <span>Discount (<?php echo $discount_percent; ?>%)</span>
                        <span>- RM <?php echo number_format($discount_amount, 2); ?></span>
                    </div>
                <?php endif; ?>
                <div class="totals-row"><span>Shipping</span><span>RM <?php echo number_format($shipping, 2); ?></span></div>
                <div class="totals-row grand-total"><span>Total</span><span>RM <?php echo number_format($grand_total, 2); ?></span></div>
            </div>
        </div>

    </div>

    <script>
        //Auto-Space for Card Number
        document.getElementById('card_number').addEventListener('input', function (e) {
            var target = e.target;
            var position = target.selectionEnd; // Remember cursor position
            
            var rawValue = target.value.replace(/\D/g, '').substring(0, 16); 
            
            var formattedValue = rawValue.replace(/(\d{4})(?=\d)/g, '$1 ');
            
            target.value = formattedValue;
        });

        //Auto-Slash for Expiry Date
        document.getElementById('expiry').addEventListener('input', function (e) {
            var input = e.target.value;
            if (input.length === 2 && !input.includes('/')) { e.target.value = input + '/'; }
        });
    </script>
</body>
</html>

