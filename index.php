<?php
session_start();
include 'db_conn.php';

// Process newsletter subscription
$sub_msg = '';
if(isset($_POST['subscribe'])){
    $email = $_POST['sub_email'];
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("INSERT IGNORE INTO newsletter (email) VALUES (?)");
        $stmt->bind_param("s", $email);
        $sub_msg = $stmt->execute()
            ? '<p class="alert success">✓ Subscribed successfully! Welcome to our community.</p>'
            : '<p class="alert error">Something went wrong. Please try again.</p>';
    } else {
        $sub_msg = '<p class="alert error">Invalid email format.</p>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timby — Eco-Friendly Wooden Toys</title>
    <meta name="description" content="Timby crafts premium eco-friendly wooden toys for children. Handcrafted, sustainable, and timeless.">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
</head>
<body>

<!-- ===================== HEADER ===================== -->
<header class="site-header">
    <div class="header-inner">
        <a href="index.php" class="site-logo">Timby</a>

        <nav class="site-nav">
            <a href="about_us.php">About Us</a>
            <a href="contact.php">Contact</a>
        </nav>

        <div style="display:flex; align-items:center; gap:20px;">
            <form method="GET" action="index.php" class="search-bar">
                <input type="text" name="search" placeholder="Search toys…"
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit">Search</button>
            </form>

            <div class="header-buttons">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php $role = $_SESSION['role'] ?? 'member'; ?>
                    <?php if($role == 'marketing'): ?>
                        <a href="marketing_dashboard.php" class="btn btn-secondary">Marketing Panel</a>
                    <?php elseif($role == 'admin'): ?>
                        <a href="admin_dashboard.php" class="btn btn-secondary">Admin Panel</a>
                    <?php else: ?>
                        <a href="member_dashboard.php" class="btn btn-secondary">Dashboard</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-teal">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-ghost" style="background:none;">Login</a>
                    <a href="register.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- ===================== HERO ===================== -->
<section class="hero">
    <div class="hero-inner">
        <div class="hero-text">
            <h1>Nurturing Play,<br><em>Naturally.</em></h1>
            <p>Handcrafted wooden toys for a sustainable future.</p>
            <a href="#collection" class="btn btn-primary" style="font-size:0.85rem; padding:14px 32px;">Explore Collection</a>
        </div>
        <div class="hero-img">
            <img src="images/shop2.png" alt="Child playing with wooden toys">
        </div>
    </div>
</section>

<!-- ===================== PRODUCT GRID ===================== -->
<section id="collection" class="section-gap">
    <div class="container">
        <h2 class="section-title">Our Handcrafted Collection</h2>

        <?php
        $sql = "SELECT * FROM products";
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $conn->real_escape_string($_GET['search']);
            $sql .= " WHERE name LIKE '%$search%' OR category LIKE '%$search%'";
        }
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0):
        ?>
        <div class="product-grid">
            <?php while($row = $result->fetch_assoc()): ?>
            <div class="product-card">
                <div class="product-card-img">
                    <img src="images/<?php echo htmlspecialchars($row['image']); ?>"
                         alt="<?php echo htmlspecialchars($row['name']); ?>">
                </div>
                <div class="product-card-body">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <div class="product-card-price">RM <?php echo number_format($row['price'], 2); ?></div>
                    <a href="product_details.php?id=<?php echo $row['product_id']; ?>"
                       class="btn btn-secondary w-full" style="justify-content:center;">View Details</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <p style="text-align:center; color:var(--color-on-surface-variant); padding: 48px 0; font-size:1.1rem;">
            No products found matching your search.
        </p>
        <?php endif; ?>
    </div>
</section>

<!-- ===================== NEWSLETTER ===================== -->
<section class="newsletter-section">
    <h2>Join Our Community</h2>
    <p>Subscribe to receive updates on new collections and sustainable play ideas.</p>
    <?php echo $sub_msg; ?>
    <form method="POST" class="newsletter-form">
        <input type="email" name="sub_email" placeholder="Your email address" required>
        <button type="submit" name="subscribe" class="btn btn-primary">Join Us</button>
    </form>
</section>

<!-- ===================== FOOTER ===================== -->
<footer class="site-footer">
    <div class="footer-logo">Timby</div>
    <nav class="footer-links">
        <a href="about_us.php">About Us</a>
        <a href="contact.php">Contact</a>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    </nav>
    <div style="display:flex; gap:10px; justify-content:center; margin-bottom:24px;">
        <span style="background:white;color:#333;padding:4px 10px;border-radius:4px;font-weight:700;font-size:0.75rem;border-bottom:3px solid #00579f;">Pay</span>
        <span style="background:white;color:#333;padding:4px 10px;border-radius:4px;font-weight:700;font-size:0.75rem;border-bottom:3px solid #ea4335;">G-Pay</span>
        <span style="background:white;color:#333;padding:4px 10px;border-radius:4px;font-weight:700;font-size:0.75rem;border-bottom:3px solid #eb001b;">Mastercard</span>
        <span style="background:white;color:#333;padding:4px 10px;border-radius:4px;font-weight:700;font-size:0.75rem;border-bottom:3px solid #1a1f71;">VISA</span>
    </div>
    <p class="footer-copy">© 2025 Timby. Handcrafted for a sustainable future.</p>
</footer>

</body>
</html>