<?php
session_start();
include 'db_conn.php';

// Security: Check if Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get Transaction Details
if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$id = $_GET['id'];

// Handle Update Submission
if (isset($_POST['update_order'])) {
    $status = $_POST['status'];
    $item_name = $_POST['product_name'];
    $amount = $_POST['amount'];
    
    // NEW: Get the customer ID from the hidden form field
    $customer_id = $_POST['customer_id']; 

    $update_sql = "UPDATE transactions SET status = ?, product_name = ?, amount = ? WHERE trans_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssdi", $status, $item_name, $amount, $id);

    if ($stmt->execute()) {
        // Send Notification to User
        $notif_msg = "Your order #$id status has been updated to: $status";
        
        $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notif_stmt->bind_param("is", $customer_id, $notif_msg);
        $notif_stmt->execute();
        // -----------------------------

        echo "<script>alert('Order updated & User Notified!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Fetch Current Data
$sql = "SELECT t.*, u.full_name FROM transactions t JOIN users u ON t.user_id = u.user_id WHERE t.trans_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo "Order not found.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Order #<?php echo $id; ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #eee; display: flex; justify-content: center; padding-top: 50px; }
        .form-card { background: white; padding: 30px; border-radius: 10px; width: 400px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2F4F4F; margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input, select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .readonly-field { background-color: #f9f9f9; color: #888; border: 1px solid #eee; }
        
        .btn { width: 100%; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 1rem; margin-top: 10px; }
        .btn-save { background: #28a745; color: white; }
        .btn-save:hover { background: #218838; }
        .btn-cancel { background: #6c757d; color: white; display: block; text-align: center; text-decoration: none; box-sizing: border-box; }
        .btn-cancel:hover { background: #5a6268; }
    </style>
</head>
<body>

    <div class="form-card">
        <h2>Edit Order #<?php echo $order['trans_id']; ?></h2>
        
        <form method="POST">
            <input type="hidden" name="customer_id" value="<?php echo $order['user_id']; ?>">

            <label>Customer Name</label>
            <input type="text" value="<?php echo htmlspecialchars($order['full_name']); ?>" class="readonly-field" readonly>

            <label>Order Date</label>
            <input type="text" value="<?php echo $order['trans_date']; ?>" class="readonly-field" readonly>

            <label>Item Name</label>
            <input type="text" name="product_name" value="<?php echo htmlspecialchars($order['product_name']); ?>" required>

            <label>Total Amount (RM)</label>
            <input type="number" step="0.01" name="amount" value="<?php echo $order['amount']; ?>" required>

            <label>Order Status</label>
            <select name="status">
                <option value="Pending" <?php if($order['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                <option value="Shipped" <?php if($order['status'] == 'Shipped') echo 'selected'; ?>>Shipped</option>
                <option value="Delivered" <?php if($order['status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                <option value="Cancelled" <?php if($order['status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
            </select>
            
            <button type="submit" name="update_order" class="btn btn-save">Save Changes & Notify</button>
            <a href="admin_dashboard.php" class="btn btn-cancel">Cancel</a>
        </form>
    </div>

</body>
</html>