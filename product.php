<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Our Products</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="dashboard-wrapper">
        <?php include 'sidebar_member.php'; ?>
        <main class="main-content">
            <h2 class="page-title">Product</h2>
            <div class="product-grid">
                <?php
                $result = $conn->query("SELECT * FROM products");
                while($row = $result->fetch_assoc()):
                ?>
                <div class="product-card">
                    <a href="product_details.php?id=<?php echo $row['product_id']; ?>" class="image-placeholder">
                        <img src="images/<?php echo $row['image']; ?>" alt="Toy">
                    </a>
                    
                    <div>
                        <div class="p-name"><?php echo htmlspecialchars($row['name']); ?></div>
                        <div class="p-price">RM <?php echo number_format($row['price'], 2); ?></div>
                    </div>

                    <div class="btn-group">
                        <a href="product_details.php?id=<?php echo $row['product_id']; ?>" class="btn-details">
                            Review
                        </a>
                        
                        <form method="POST" action="cart_actions.php" style="flex:1; display:flex;">
                            <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                            <?php if($row['stock_quantity'] > 0): ?>
                                <button type="submit" name="add_to_cart" class="btn-add">Cart</button>
                            <?php else: ?>
                                <button type="button" disabled style="background:#ccc; cursor:not-allowed;" class="btn-add">Sold</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </main>
    </div>
</body>
</html>