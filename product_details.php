<?php
session_start();
include 'db_conn.php';

// Check if ID is set
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';

// ---  FETCH PRODUCT ---
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) { echo "Product not found."; exit(); }

// ---  HANDLE CART (BLOCKED FOR STAFF) ---
if (isset($_POST['add_to_cart'])) {
    if ($user_role == 'admin' || $user_role == 'marketing') {
        header("Location: product_details.php?id=$id"); 
        exit();
    }
    if ($user_id == 0) { header("Location: login.php"); exit(); }
    
    if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }
    if (isset($_SESSION['cart'][$id])) { $_SESSION['cart'][$id]++; } else { $_SESSION['cart'][$id] = 1; }
    header("Location: view_cart.php");
    exit();
}

// ---  REVIEWS LOGIC ---

// Add Review
if (isset($_POST['submit_review'])) {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $ins = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    $ins->bind_param("iiis", $id, $user_id, $rating, $comment);
    $ins->execute();
    header("Location: product_details.php?id=$id"); exit();
}

//  Delete Review 
if (isset($_POST['delete_review'])) {
    $review_id_to_delete = $_POST['review_id'];
    
    // Security: Only delete if the review belongs to the logged-in user
    $del = $conn->prepare("DELETE FROM reviews WHERE review_id = ? AND user_id = ?");
    $del->bind_param("ii", $review_id_to_delete, $user_id);
    $del->execute();
    
    header("Location: product_details.php?id=$id"); 
    exit();
}

//  Check Eligibility
$can_review = false;
$eligibility_msg = "";
$has_reviewed_already = false;

if ($user_id > 0 && $user_role == 'member') {
    //  Check if they bought it
    $check_sql = "SELECT * FROM transactions WHERE user_id = ? AND product_name = ? AND status = 'Delivered'";
    $chk_stmt = $conn->prepare($check_sql);
    $chk_stmt->bind_param("is", $user_id, $product['name']);
    $chk_stmt->execute();
    
    if ($chk_stmt->get_result()->num_rows > 0) {
        //  Check if they ALREADY reviewed it
        $dup_check = $conn->query("SELECT * FROM reviews WHERE user_id = $user_id AND product_id = $id");
        if ($dup_check->num_rows > 0) {
            $has_reviewed_already = true;
            $eligibility_msg = "You have already reviewed this item.";
        } else {
            $can_review = true;
        }
    } else {
        $eligibility_msg = "You can only review items you have purchased and received.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $product['name']; ?> - Timby</title>
    <style>
        body { font-family: 'Georgia', serif; background-color: #F9F9F9; color: #333; margin: 0; padding: 40px; }
        a { text-decoration: none; color: inherit; }
        .back-link { display: inline-block; color: #8B5E3C; font-weight: bold; margin-bottom: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .product-wrapper { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; }
        .product-img { width: 100%; height: 350px; object-fit: cover; border-radius: 10px; border: 1px solid #eee; }
        .product-info h1 { color: #2F4F4F; margin-top: 0; }
        .price { font-size: 1.5rem; color: #8B5E3C; font-weight: bold; margin-bottom: 15px; }
        
        .btn-add { background-color: #2F4F4F; color: white; padding: 15px 30px; border: none; border-radius: 5px; width: 100%; cursor: pointer; font-weight: bold; }
        .btn-disabled { background-color: #ccc; color: #666; padding: 15px 30px; border: none; border-radius: 5px; width: 100%; cursor: not-allowed; font-weight: bold; }
        
        .reviews-section { border-top: 1px solid #eee; padding-top: 30px; }
        .review-card { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; position: relative; }
        
        /* Delete Button Style */
        .btn-delete {
            background: none; border: none; color: #dc3545; font-size: 0.8rem; 
            cursor: pointer; text-decoration: underline; margin-left: 10px;
        }
        .btn-delete:hover { color: #a71d2a; }

        select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 10px; }
        textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 10px; resize: vertical; }
    </style>
</head>
<body>

    <div class="container">
        <a href="index.php" class="back-link">← Back to Shop</a>

        <div class="product-wrapper">
            <img src="images/<?php echo $product['image']; ?>" class="product-img">
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="price">RM <?php echo number_format($product['price'], 2); ?></div>
                <p>Category: <strong><?php echo $product['category']; ?></strong></p>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

                <?php if($user_role == 'admin' || $user_role == 'marketing'): ?>
                    <button class="btn-disabled" disabled>Staff View Only (Cannot Buy)</button>
                <?php else: ?>
                    <form method="POST">
                        <button type="submit" name="add_to_cart" class="btn-add">Add to Cart</button>
                    </form>
                <?php endif; ?>

            </div>
        </div>

        <div class="reviews-section">
            <h3>Customer Reviews</h3>
            
            <?php if ($can_review): ?>
                <form method="POST" style="background:#fdf5e6; padding:20px; border-radius:10px; margin-bottom:20px;">
                    <label style="font-weight:bold; display:block; margin-bottom:5px;">Rating:</label>
                    <select name="rating" required>
                        <option value="5">★★★★★ (5 Stars)</option>
                        <option value="4">★★★★☆ (4 Stars)</option>
                        <option value="3">★★★☆☆ (3 Stars)</option>
                        <option value="2">★★☆☆☆ (2 Stars)</option>
                        <option value="1">★☆☆☆☆ (1 Star)</option>
                    </select>
                    <textarea name="comment" rows="3" placeholder="Share your experience..." required></textarea>
                    <button type="submit" name="submit_review" class="btn-add" style="background:#8B5E3C; width:auto; padding:10px 20px;">Post Review</button>
                </form>
            <?php elseif($user_role == 'member'): ?>
                <p style="color:#777; font-style:italic; background:#f4f4f4; padding:10px; border-radius:5px;"><?php echo $eligibility_msg; ?></p>
            <?php endif; ?>

            <?php
            $revs = $conn->query("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.user_id WHERE product_id=$id ORDER BY created_at DESC");
            
            if ($revs->num_rows > 0) {
                while($r = $revs->fetch_assoc()) {
                    // Check if this review belongs to the logged-in user
                    $is_my_review = ($user_id == $r['user_id']);
                    
                    echo "<div class='review-card'>
                            <div style='display:flex; justify-content:space-between; align-items:center;'>
                                <div>
                                    <strong>{$r['full_name']}</strong> 
                                    <span style='color:#f39c12;'>".str_repeat('★', $r['rating'])."</span>";
                                    
                    // Show Delete button if it's my review
                    if ($is_my_review) {
                        echo "<form method='POST' style='display:inline;' onsubmit='return confirm(\"Delete your review?\");'>
                                <input type='hidden' name='review_id' value='{$r['review_id']}'>
                                <button type='submit' name='delete_review' class='btn-delete'>Delete</button>
                              </form>";
                    }

                    echo "      </div>
                                <small style='color:#999;'>".date("d M Y", strtotime($r['created_at']))."</small>
                            </div>
                            <p style='margin-top:5px;'>{$r['comment']}</p>
                          </div>";
                }
            } else {
                echo "<p style='color:#999;'>No reviews yet. Be the first!</p>";
            }
            ?>
        </div>
    </div>

</body>
</html>