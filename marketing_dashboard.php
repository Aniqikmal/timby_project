<?php
session_start();
include 'db_conn.php';

// --- SECURITY: ONLY MARKETING ALLOWED ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'marketing') {
    header("Location: login.php");
    exit();
}

$message = "";
$msg_type = "";

// --- ADD PROMO ---
if (isset($_POST['add_promo'])) {
    $code = $_POST['code'];
    $discount = $_POST['discount'];
    $desc = $_POST['desc'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO promotions (code, discount_percent, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $code, $discount, $desc);
        $stmt->execute();
        
        $_SESSION['flash_message'] = "Promo code '$code' added successfully!";
        $_SESSION['flash_type'] = "success";
        header("Location: marketing_dashboard.php");
        exit();

    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $message = "Error: The code '$code' already exists!";
            $msg_type = "error";
        } else {
            $message = "Database Error: " . $e->getMessage();
            $msg_type = "error";
        }
    }
}

// --- DELETE PROMO ---
if (isset($_GET['delete_promo'])) {
    $id = $_GET['delete_promo'];
    $conn->query("DELETE FROM promotions WHERE promo_id = $id");
    header("Location: marketing_dashboard.php");
    exit();
}

// --- SEND NEWSLETTER ---
if (isset($_POST['send_newsletter'])) {
    $subject = $_POST['news_subject'];
    $body = $_POST['news_body'];
    
    // Fetch all subscribers
    $subs = $conn->query("SELECT email FROM newsletter");
    $count = 0;
    
    if ($subs->num_rows > 0) {
        $headers = "From: no-reply@timbytoys.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8";

        while($row = $subs->fetch_assoc()) {
            // Send email 
            @mail($row['email'], $subject, $body, $headers);
            $count++;
        }
        $message = "Newsletter sent to $count subscribers!";
        $msg_type = "success";
    } else {
        $message = "No subscribers found in the database.";
        $msg_type = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Marketing Panel - Timby</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f4f4; display: flex; min-height: 100vh; margin:0; }
        
        .sidebar { width: 250px; background: #8B5E3C; color: white; padding: 20px; display: flex; flex-direction: column; position:sticky; top:0; height:100vh; }
        .sidebar a { color: #eee; text-decoration: none; padding: 12px; display: block; margin-bottom: 5px; border-radius: 5px; }
        .sidebar a:hover { background: rgba(255,255,255,0.2); }
        
        .main { flex: 1; padding: 40px; overflow-y:auto; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        h2 { color: #5D4037; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        
        /* Form Styling */
        input, textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        textarea { resize: vertical; font-family: inherit; }
        .btn-submit { background: #28a745; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px; font-weight:bold; }
        .btn-send { background: #007bff; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px; font-weight:bold; }
        
        /* Alerts */
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .alert.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { text-align: left; background: #eee; padding: 10px; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        .btn-del { color: red; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body>

    <?php include 'sidebar_marketing.php'; ?>

    <div class="main">
        <h1>Marketing Dashboard</h1>
        
        <?php if(isset($_SESSION['flash_message'])): ?>
            <div class="alert <?php echo $_SESSION['flash_type']; ?>">
                <?php echo $_SESSION['flash_message']; ?>
            </div>
            <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
        <?php endif; ?>

        <?php if($message != ""): ?>
            <div class="alert <?php echo $msg_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div id="promos" class="card">
            <h2>Active Promotions</h2>
            <p style="color:#666; margin-bottom:20px;">Create discount codes for customers to use at checkout.</p>
            
            <form method="POST" style="background:#f9f9f9; padding:20px; border-radius:8px;">
                <strong>Create New Code:</strong><br><br>
                <div style="display:flex; gap:10px;">
                    <input type="text" name="code" placeholder="e.g. SAVE10" required style="flex:1;">
                    <input type="number" name="discount" placeholder="%" required style="width:80px;">
                    <input type="text" name="desc" placeholder="Description" required style="flex:2;">
                    <button type="submit" name="add_promo" class="btn-submit">Add</button>
                </div>
            </form>

            <table>
                <thead><tr><th>Code</th><th>Discount</th><th>Description</th><th>Action</th></tr></thead>
                <tbody>
                    <?php
                    $promos = $conn->query("SELECT * FROM promotions");
                    while($row = $promos->fetch_assoc()):
                    ?>
                    <tr>
                        <td style="font-weight:bold; color:green;"><?php echo $row['code']; ?></td>
                        <td><?php echo $row['discount_percent']; ?>%</td>
                        <td><?php echo $row['description']; ?></td>
                        <td><a href="?delete_promo=<?php echo $row['promo_id']; ?>" class="btn-del" onclick="return confirm('Delete this code?')">Remove</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div id="newsletter" class="card">
            <h2>Send Newsletter</h2>
            <p style="color:#666; margin-bottom:20px;">Send email updates to all subscribed customers.</p>
            
            <?php 
            $sub_count = $conn->query("SELECT COUNT(*) as c FROM newsletter")->fetch_assoc()['c']; 
            ?>
            <p style="font-weight:bold; color:#8B5E3C;">Total Subscribers: <?php echo $sub_count; ?></p>

            <form method="POST" style="background:#f9f9f9; padding:20px; border-radius:8px;">
                <label style="font-weight:bold;">Email Subject</label>
                <input type="text" name="news_subject" placeholder="e.g. New Wooden Trains Arrived!" required>
                
                <label style="font-weight:bold;">Message Body</label>
                <textarea name="news_body" rows="6" placeholder="Write your newsletter content here..." required></textarea>
                
                <button type="submit" name="send_newsletter" class="btn-send" onclick="return confirm('Send to all <?php echo $sub_count; ?> subscribers?')">Send </button>
            </form>
        </div>

        <div id="sales" class="card">
            <h2>Sales Overview (Read Only)</h2>
            <table>
                <thead><tr><th>Date</th><th>Customer</th><th>Item</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                    <?php
                    $sales = $conn->query("SELECT t.*, u.full_name FROM transactions t JOIN users u ON t.user_id = u.user_id ORDER BY t.trans_date DESC LIMIT 5");
                    while($s = $sales->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?php echo date("d M Y", strtotime($s['trans_date'])); ?></td>
                        <td><?php echo htmlspecialchars($s['full_name']); ?></td>
                        <td><?php echo $s['product_name']; ?></td>
                        <td>RM <?php echo $s['amount']; ?></td>
                        <td><?php echo $s['status']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>