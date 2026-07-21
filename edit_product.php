<?php
session_start();
include 'db_conn.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$id = intval($_GET['id']);
if ($id <= 0) { header("Location: admin_dashboard.php"); exit(); }
$product = $conn->query("SELECT * FROM products WHERE product_id = $id")->fetch_assoc();
if (!$product) { header("Location: admin_dashboard.php"); exit(); }

if (isset($_POST['update_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $cat = $_POST['category'];
    $desc = $_POST['desc'];

    $stmt = $conn->prepare("UPDATE products SET name=?, price=?, stock_quantity=?, category=?, description=? WHERE product_id=?");
    $stmt->bind_param("sdisss", $name, $price, $stock, $cat, $desc, $id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Product Updated!'); window.location.href='admin_dashboard.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <style>
        body { font-family: sans-serif; background: #eee; display: flex; justify-content: center; padding-top: 50px; }
        .form-card { background: white; padding: 30px; border-radius: 10px; width: 400px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        input, textarea { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .btn { width: 100%; padding: 10px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-save { background: #28a745; color: white; margin-bottom: 10px; }
        .btn-cancel { background: #6c757d; color: white; display: block; text-align: center; text-decoration: none; }
    </style>
</head>
<body>
    <div class="form-card">
        <h2 style="text-align:center; color:#333;">Edit Product</h2>
        <form method="POST">
            <label>Product Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            
            <label>Price (RM)</label>
            <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required>
            
            <label>Stock Quantity</label>
            <input type="number" name="stock" value="<?php echo $product['stock_quantity']; ?>" required>

            <label>Category</label>
            <input type="text" name="category" value="<?php echo htmlspecialchars($product['category']); ?>" required>

            <label>Description</label>
            <textarea name="desc" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
            
            <button type="submit" name="update_product" class="btn btn-save">Save Changes</button>
            <a href="admin_dashboard.php" class="btn btn-cancel">Cancel</a>
        </form>
    </div>
</body>
</html>