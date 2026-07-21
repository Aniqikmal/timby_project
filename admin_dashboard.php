<?php
/**
 * Admin Dashboard Controller
 * Handles product management, order tracking, and sales reporting.
 */

session_start();
include 'db_conn.php';

// ==========================================
// 1. AUTHENTICATION & ACCESS CONTROL
// ==========================================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ==========================================
// 2. ACTION HANDLERS (POST/GET REQUESTS)
// ==========================================

// Action: Delete Product
if (isset($_GET['delete_product'])) {
    $id = intval($_GET['delete_product']); // Sanitize input
    $conn->query("DELETE FROM products WHERE product_id = $id");
    header("Location: admin_dashboard.php");
    exit();
}

// Action: Add New Product
if (isset($_POST['add_product'])) {
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock_quantity, category, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdiss", $_POST['name'], $_POST['desc'], $_POST['price'], $_POST['stock'], $_POST['category'], $_POST['image']);
    $stmt->execute();
}

// Action: Update Custom Request Status
if (isset($_POST['update_req_status'])) {
    $req_id = $_POST['req_id'];
    $new_status = $_POST['req_status'];
    
    $stmt = $conn->prepare("UPDATE custom_requests SET status = ? WHERE request_id = ?");
    $stmt->bind_param("si", $new_status, $req_id);
    $stmt->execute();
    
    // Redirect to anchor to keep user context
    header("Location: admin_dashboard.php#requests");
    exit();
}

// ==========================================
// 3. REPORTING & ANALYTICS LOGIC
// ==========================================

// Transaction Filter Logic
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$trans_sql = "SELECT t.*, u.full_name FROM transactions t JOIN users u ON t.user_id = u.user_id";

if ($filter == 'daily') {
    $trans_sql .= " WHERE DATE(trans_date) = CURDATE()";
    $report_title = "Daily Transaction Report (" . date("d M Y") . ")";
} elseif ($filter == 'weekly') {
    $trans_sql .= " WHERE WEEK(trans_date) = WEEK(CURDATE()) AND YEAR(trans_date) = YEAR(CURDATE())";
    $report_title = "Weekly Transaction Report";
} elseif ($filter == 'monthly') {
    $trans_sql .= " WHERE MONTH(trans_date) = MONTH(CURDATE()) AND YEAR(trans_date) = YEAR(CURDATE())";
    $report_title = "Monthly Transaction Report (" . date("F Y") . ")";
} else {
    $report_title = "All Transaction Records";
}
$trans_sql .= " ORDER BY t.trans_date DESC";
$transactions = $conn->query($trans_sql);

// Chart Data Aggregation (Last 7 Days)
$chart_sql = "SELECT DATE(trans_date) as date, SUM(amount) as total FROM transactions GROUP BY DATE(trans_date) ORDER BY date DESC LIMIT 7";
$chart_res = $conn->query($chart_sql);
$dates = [];
$totals = [];
while($row = $chart_res->fetch_assoc()) {
    $dates[] = date("d M", strtotime($row['date'])); 
    $totals[] = $row['total'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; display: flex; min-height: 100vh; }
        
        /* Layout Structure */
        .sidebar { width: 250px; background-color: #2F4F4F; color: white; padding: 20px; flex-shrink: 0; display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; overflow-y: auto; }
        .main-content { flex-grow: 1; padding: 30px; overflow-y: auto; }
        
        /* Navigation Links */
        .sidebar a { color: #ccc; text-decoration: none; display: block; padding: 12px; margin-bottom: 5px; border-radius: 5px; }
        .sidebar a:hover { background-color: rgba(255,255,255,0.1); color: white; }

        /* Dashboard Cards */
        .section-card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 40px; }
        .section-title { font-size: 1.2rem; color: #2F4F4F; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; background: #eee; padding: 10px; color: #555; font-size: 0.9rem; }
        td { padding: 12px 10px; border-bottom: 1px solid #f0f0f0; font-size: 0.9rem; vertical-align: top; }
        
        /* Utility Classes & Buttons */
        .btn-print { background: #2F4F4F; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; float: right; }
        .filter-btn { text-decoration: none; padding: 5px 15px; border: 1px solid #ccc; color: #555; border-radius: 20px; margin-left: 5px; font-size: 0.85rem; }
        .filter-btn.active { background: #2F4F4F; color: white; border-color: #2F4F4F; }
        
        .action-link { text-decoration: none; color: white; padding: 5px 10px; border-radius: 4px; font-size: 0.8rem; margin-right: 5px; }
        .btn-edit { background-color: #007bff; }
        .btn-delete { background-color: #dc3545; }
        .btn-update { background-color: #28a745; border:none; color:white; padding:5px 10px; border-radius:4px; cursor:pointer; }
        .btn-submit { background: #28a745; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; }

        /* Form Inputs */
        input, select, textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px; }
        
        .chart-container { width: 100%; max-width: 600px; margin: 0 auto 30px auto; }

        /* Print Styles */
        @media print {
            body * { visibility: hidden; }
            #sales, #sales * { visibility: visible; }
            #sales { position: absolute; top: 0; left: 0; width: 100%; margin: 0; padding: 20px; box-shadow: none; border: none; }
            .no-print { display: none !important; }
            table { border: 1px solid #ddd; }
            th { background-color: #f8f8f8 !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <nav class="sidebar">
        <h2 style="text-align:center; margin-bottom:20px;">Admin Panel</h2>
        <a href="#members">Members</a>
        <a href="#products">Products</a>
        <a href="#requests">Custom Requests</a> 
        <a href="#sales">Transactions</a>
        <a href="logout.php" style="margin-top:auto; background:#dc3545; color:white;">Logout</a>
    </nav>

    <main class="main-content">
        <h1 class="no-print" style="margin-bottom:20px;">Dashboard Overview</h1>

        <div id="members" class="section-card">
            <div class="section-title">Registered Members</div>
            <table>
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr></thead>
                <tbody>
                    <?php
                    $users = $conn->query("SELECT * FROM users WHERE role='member'");
                    while($row = $users->fetch_assoc()):
                    ?>
                    <tr>
                        <td>#<?php echo $row['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td>Member</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div id="products" class="section-card">
            <div class="section-title">Product Inventory</div>
            
            <form method="POST" style="background:#f9f9f9; padding:15px; margin-bottom:20px;">
                <input type="text" name="name" placeholder="Product Name" required style="width:30%; display:inline-block;">
                <input type="number" step="0.01" name="price" placeholder="Price" required style="width:20%; display:inline-block;">
                <input type="number" name="stock" placeholder="Stock" required style="width:20%; display:inline-block;">
                <input type="text" name="category" placeholder="Category" required style="width:25%; display:inline-block;">
                <input type="text" name="image" placeholder="Image Filename (e.g., toy.jpg)" required>
                <textarea name="desc" placeholder="Description" rows="1"></textarea>
                <button type="submit" name="add_product" class="btn-submit">Add New Product</button>
            </form>

            <table>
                <thead><tr><th>Name</th><th>Price</th><th>Stock</th><th>Action</th></tr></thead>
                <tbody>
                    <?php
                    $products = $conn->query("SELECT * FROM products");
                    while($p = $products->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                        <td>RM <?php echo $p['price']; ?></td>
                        <td><?php echo $p['stock_quantity']; ?></td>
                        <td>
                            <a href="edit_product.php?id=<?php echo $p['product_id']; ?>" class="action-link btn-edit">Edit</a>
                            <a href="admin_dashboard.php?delete_product=<?php echo $p['product_id']; ?>" onclick="return confirm('Confirm deletion?')" class="action-link btn-delete">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div id="requests" class="section-card">
            <div class="section-title">Custom Toy Requests</div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th style="width:40%;">Description</th>
                        <th>Budget</th>
                        <th>Status / Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $req_sql = "SELECT c.*, u.full_name FROM custom_requests c JOIN users u ON c.user_id = u.user_id ORDER BY c.request_date DESC";
                    $requests = $conn->query($req_sql);
                    
                    if ($requests->num_rows > 0):
                        while($req = $requests->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?php echo date("d/m/Y", strtotime($req['request_date'])); ?></td>
                        <td><?php echo htmlspecialchars($req['full_name']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($req['description'])); ?></td>
                        <td>RM <?php echo number_format($req['budget'], 2); ?></td>
                        <td>
                            <form method="POST" style="display:flex; gap:5px;">
                                <input type="hidden" name="req_id" value="<?php echo $req['request_id']; ?>">
                                <select name="req_status" style="padding:5px; margin:0; width:auto; font-size:0.85rem;">
                                    <option value="Pending" <?php if($req['status']=='Pending') echo 'selected'; ?>>Pending</option>
                                    <option value="Reviewing" <?php if($req['status']=='Reviewing') echo 'selected'; ?>>Reviewing</option>
                                    <option value="Accepted" <?php if($req['status']=='Accepted') echo 'selected'; ?>>Accepted</option>
                                    <option value="Rejected" <?php if($req['status']=='Rejected') echo 'selected'; ?>>Rejected</option>
                                    <option value="Completed" <?php if($req['status']=='Completed') echo 'selected'; ?>>Completed</option>
                                </select>
                                <button type="submit" name="update_req_status" class="btn-update">✓</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;">No custom requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="sales" class="section-card">
            <div class="section-title">
                <?php echo $report_title; ?>
                <div class="no-print">
                    <span style="font-size:0.8rem; color:#666;">Filter:</span>
                    <a href="?filter=all#sales" class="filter-btn <?php echo $filter=='all'?'active':''; ?>">All</a>
                    <a href="?filter=daily#sales" class="filter-btn <?php echo $filter=='daily'?'active':''; ?>">Daily</a>
                    <a href="?filter=weekly#sales" class="filter-btn <?php echo $filter=='weekly'?'active':''; ?>">Weekly</a>
                    <a href="?filter=monthly#sales" class="filter-btn <?php echo $filter=='monthly'?'active':''; ?>">Monthly</a>
                    <button onclick="window.print()" class="btn-print" style="margin-left:15px;">🖨️ Print Report</button>
                </div>
            </div>

            <div class="chart-container no-print">
                <canvas id="salesChart"></canvas>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Order ID</th><th>Customer</th><th>Date</th><th>Item</th><th>Amount</th><th>Status</th>
                        <th class="no-print">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($transactions->num_rows > 0): ?>
                        <?php while($t = $transactions->fetch_assoc()): 
                            $status_color = 'black';
                            if ($t['status'] == 'Pending') $status_color = 'orange';
                            elseif ($t['status'] == 'Shipped') $status_color = '#17a2b8'; 
                            elseif ($t['status'] == 'Delivered') $status_color = 'green';
                            elseif ($t['status'] == 'Cancelled') $status_color = 'red';
                        ?>
                        <tr>
                            <td>#<?php echo $t['trans_id']; ?></td>
                            <td><?php echo htmlspecialchars($t['full_name']); ?></td>
                            <td><?php echo date("d/m/Y", strtotime($t['trans_date'])); ?></td>
                            <td><?php echo $t['product_name']; ?></td>
                            <td>RM <?php echo $t['amount']; ?></td>
                            <td>
                                <span style="font-weight:bold; color: <?php echo $status_color; ?>;">
                                    <?php echo $t['status']; ?>
                                </span>
                            </td>
                            <td class="no-print">
                                <a href="edit_transaction.php?id=<?php echo $t['trans_id']; ?>" class="action-link btn-edit">Edit</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align:center;">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <div style="margin-top:30px; text-align:center; font-size:0.8rem; color:#888; visibility:hidden;" id="print-footer">
                Report Generated by Timby Admin System on <?php echo date("d M Y H:i:s"); ?>
            </div>
            <style>@media print { #print-footer { visibility: visible !important; } }</style>
        </div>

    </main>

    <script>
        const ctx = document.getElementById('salesChart');
        const labels = <?php echo json_encode(array_reverse($dates)); ?>;
        const data = <?php echo json_encode(array_reverse($totals)); ?>;
        if(ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{ label: 'Sales (RM)', data: data, backgroundColor: '#8B5E3C', borderColor: '#5D4037', borderWidth: 1 }]
                },
                options: { responsive: true, scales: { y: { beginAtZero: true } } }
            });
        }
    </script>
</body>
</html>