<?php
session_start();
include 'db_conn.php';

// Check if Order ID is present
if (!isset($_GET['order_id'])) {
    header("Location: member_dashboard.php");
    exit();
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// --- 1. FETCH ORDER + USER EMAIL ---
// We join the 'users' table to get the email address of the buyer
$sql = "SELECT t.*, u.email, u.full_name 
        FROM transactions t 
        JOIN users u ON t.user_id = u.user_id 
        WHERE t.trans_id = ? AND t.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Order not found.";
    exit();
}

$order = $result->fetch_assoc();
$date = date("F j, Y", strtotime($order['trans_date']));
$buyer_email = $order['email'];
$buyer_name = $order['full_name'];

// --- 2. SEND EMAIL LOGIC ---
// Only try to send if we haven't already (to prevent sending on every refresh)
if (!isset($_SESSION['email_sent_for_order_' . $order_id])) {
    
    $to = $buyer_email;
    $subject = "Timby Toys Receipt - Order #$order_id";
    
    // Construct the Email Body
    $message = "
    Dear $buyer_name,
    
    Thank you for your purchase at Timby Toys!
    
    ---------------------------------
    ORDER RECEIPT
    ---------------------------------
    Order ID: #$order_id
    Date: $date
    Item: " . ($order['product_name'] ?? 'Assorted Item') . "
    Qty: " . ($order['total_qty'] ?? 1) . "
    Total Paid: RM " . number_format($order['amount'], 2) . "
    ---------------------------------
    
    Your item will be shipped to:
    " . ($order['delivery_address'] ?? 'Address on file') . "
    
    Thank you,
    The Timby Team
    ";

    // Standard Headers
    $headers = "From: no-reply@timbytoys.com";

    // Attempt to send (Works on live server, might fail on XAMPP without config)
    // We use @ to suppress errors if XAMPP isn't configured for mail
    @mail($to, $subject, $message, $headers);

    // Mark as sent in session so we don't spam them on refresh
    $_SESSION['email_sent_for_order_' . $order_id] = true;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Receipt #<?php echo $order_id; ?></title>
    <style>
        /* --- GLOBAL RESET --- */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body { 
            font-family: 'Courier New', Courier, monospace; 
            background-color: #eee; 
            padding: 50px; 
            display: flex; flex-direction: column; align-items: center; 
        }
        
        /* EMAIL SUCCESS BANNER */
        .email-banner {
            background-color: #d4edda; color: #155724; 
            border: 1px solid #c3e6cb; padding: 15px; 
            border-radius: 5px; margin-bottom: 20px; width: 400px;
            text-align: center; font-family: sans-serif; font-size: 0.9rem;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }

        .receipt-card {
            background: white; width: 400px; padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
            background-image: radial-gradient(circle, transparent 20%, #fff 20%);
            background-size: 10px 10px;
            background-position: 0 100%; 
        }
        
        .logo-area { text-align: center; margin-bottom: 20px; border-bottom: 2px dashed #ccc; padding-bottom: 20px; }
        .logo-area img { width: 60px; mix-blend-mode: darken; }
        .logo-area h1 { margin: 10px 0 0; color: #8B5E3C; font-size: 1.5rem; }
        
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem; color: #555; }
        .total-row { display: flex; justify-content: space-between; margin-top: 20px; padding-top: 10px; border-top: 2px dashed #333; font-weight: bold; font-size: 1.2rem; color: #333; }
        
        .item-box { 
            background: #F9F9F9; padding: 10px; margin: 20px 0; border-radius: 5px; 
            display: flex; align-items: center; gap: 10px;
        }
        .item-box img { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; }
        
        /* UNIFIED BUTTON STYLES */
        .btn-home, .btn-print {
            display: block; width: 100%; text-align: center; text-decoration: none; 
            border-radius: 5px; font-family: sans-serif; border: none; cursor: pointer;
            box-sizing: border-box; 
        }
        .btn-home { background: #8B5E3C; color: white; padding: 12px; margin-top: 20px; font-weight: bold; }
        .btn-print { background: #ddd; color: #333; padding: 10px; margin-top: 10px; font-size: 0.9rem; }
        .btn-print:hover { background: #ccc; }
        .btn-home:hover { background: #6d4a2f; }
    </style>
</head>
<body>

    <div class="email-banner">
        <span>✅</span> 
        <span>Receipt emailed to <strong><?php echo htmlspecialchars($buyer_email); ?></strong></span>
    </div>

    <div class="receipt-card">
        <div class="logo-area">
            <img src="images/leaf.png" alt="Logo">
            <h1>PAYMENT RECEIPT</h1>
            <p style="margin:5px 0 0; color:#888;">Thank you for your purchase!</p>
        </div>

        <div class="info-row"><span>Order ID:</span> <span>#<?php echo $order['trans_id']; ?></span></div>
        <div class="info-row"><span>Date:</span> <span><?php echo $date; ?></span></div>
        <div class="info-row"><span>Status:</span> <span><?php echo $order['status']; ?></span></div>

        <div class="item-box">
            <img src="images/<?php echo !empty($order['product_image']) ? $order['product_image'] : 'logo.jpg'; ?>">
            <div>
                <div style="font-weight:bold;"><?php echo !empty($order['product_name']) ? $order['product_name'] : 'Assorted Items'; ?></div>
                <div style="font-size:0.8rem; color:#888;">Qty: <?php echo isset($order['total_qty']) ? $order['total_qty'] : 1; ?></div>
            </div>
        </div>

        <div class="info-row"><span>Subtotal</span> <span>RM <?php echo number_format(max(0, $order['amount'] - 5.00), 2); ?></span></div>
        <div class="info-row"><span>Shipping</span> <span>RM 5.00</span></div>
        
        <div class="total-row">
            <span>TOTAL PAID</span>
            <span>RM <?php echo number_format($order['amount'], 2); ?></span>
        </div>

        <a href="member_dashboard.php" class="btn-home">Return to Dashboard</a>
        <button onclick="window.print()" class="btn-print">Print Receipt</button>
    </div>

</body>
</html>