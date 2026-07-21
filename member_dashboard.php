<?php
session_start();
include 'db_conn.php';

// 1. AUTHENTICATION
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'] ?? 'Member';

// 2. DISMISS NOTIFICATION
if (isset($_GET['dismiss_notif'])) {
    $n_id = intval($_GET['dismiss_notif']);
    $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $n_id AND user_id = $user_id");
    header("Location: member_dashboard.php");
    exit();
}

// 3. FETCH NOTIFICATIONS
$notif_sql = "SELECT * FROM notifications WHERE user_id = $user_id AND is_read = 0 ORDER BY created_at DESC";
$notifications = $conn->query($notif_sql);

// 4. FETCH ORDERS
$sql = "SELECT t.*, p.product_id
        FROM transactions t
        LEFT JOIN products p ON t.product_name = p.name
        WHERE t.user_id = ?
        ORDER BY t.trans_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard — Timby</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
</head>
<body>
<div class="dashboard-wrap">

    <!-- ===== SIDEBAR ===== -->
    <nav class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-avatar">
                <img src="images/leaf.png" alt="Profile" style="object-fit:contain; padding:8px; mix-blend-mode:multiply;">
            </div>
            <div class="sidebar-name"><?php echo htmlspecialchars($user_name); ?></div>
            <div class="sidebar-role">Premium Member</div>
        </div>
        <div class="sidebar-nav">
            <a href="member_dashboard.php" class="active">
                <span class="material-symbols-outlined">dashboard</span> Dashboard
            </a>
            <a href="edit_profile.php">
                <span class="material-symbols-outlined">person_edit</span> Edit Profile
            </a>
            <a href="request_custom.php">
                <span class="material-symbols-outlined">edit_note</span> Custom Request
            </a>
            <a href="product.php">
                <span class="material-symbols-outlined">toys</span> Products
            </a>
            <a href="view_cart.php">
                <span class="material-symbols-outlined">shopping_cart</span> My Cart
            </a>
            <a href="index.php">
                <span class="material-symbols-outlined">store</span> View Shop
            </a>
        </div>
        <div class="sidebar-logout">
            <a href="logout.php">
                <span class="material-symbols-outlined">logout</span> Logout
            </a>
        </div>
    </nav>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="dashboard-main">
        <h1>Dashboard</h1>

        <!-- NOTIFICATIONS -->
        <?php if ($notifications && $notifications->num_rows > 0): ?>
            <?php while($note = $notifications->fetch_assoc()): ?>
            <div class="notif-banner">
                <div>
                    <strong>🔔 Update:</strong> <?php echo htmlspecialchars($note['message']); ?>
                    <span style="font-size:0.8rem; color:#3b82f6; margin-left:8px;">
                        (<?php echo date("d M, h:i A", strtotime($note['created_at'])); ?>)
                    </span>
                </div>
                <a href="member_dashboard.php?dismiss_notif=<?php echo $note['id']; ?>" class="notif-dismiss" title="Dismiss">&times;</a>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>

        <!-- ORDERS CARD -->
        <div class="card">
            <h2>My Orders</h2>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Item Details</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders && $orders->num_rows > 0): ?>
                            <?php while($row = $orders->fetch_assoc()):
                                $status     = $row['status'] ?: 'Pending';
                                $badge_class = 'badge-' . strtolower(str_replace(' ', '-', $status));
                                $img         = !empty($row['product_image']) ? $row['product_image'] : 'logo.jpg';
                                $pname       = !empty($row['product_name']) ? $row['product_name'] : 'Assorted Items';
                                $action      = '<span style="color:var(--color-on-surface-variant)">—</span>';
                                if ($status == 'Delivered' && !empty($row['product_id'])) {
                                    $action = "<a href='product_details.php?id={$row['product_id']}' class='btn-review'>Write Review</a>";
                                } elseif ($status == 'Pending') {
                                    $action = "<a href='receipt.php?order_id={$row['trans_id']}' class='btn btn-secondary btn-sm'>View Receipt</a>";
                                }
                            ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <img src="images/<?php echo htmlspecialchars($img); ?>" class="order-thumb" alt="Item">
                                        <span style="font-weight:700; color:var(--color-on-tertiary-fixed-variant);"><?php echo htmlspecialchars($pname); ?></span>
                                    </div>
                                </td>
                                <td style="color:var(--color-on-surface-variant);"><?php echo htmlspecialchars($row['trans_date']); ?></td>
                                <td><span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                                <td style="font-weight:700; color:var(--color-secondary);">RM <?php echo number_format($row['amount'], 2); ?></td>
                                <td><?php echo $action; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding:48px; color:var(--color-on-surface-variant);">
                                    You have no orders yet. <a href="product.php" style="color:var(--color-primary); font-weight:700;">Start shopping →</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>