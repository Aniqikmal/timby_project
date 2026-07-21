<nav class="sidebar">
    <div class="sidebar-profile">
        <div class="profile-pill"><img src="images/leaf.png" alt="Icon"></div>
        <div class="profile-name"><?php echo htmlspecialchars($_SESSION['name']); ?></div>
    </div>
    <ul>
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
        
        <li><a href="member_dashboard.php" class="<?php echo ($current_page == 'member_dashboard.php') ? 'active' : ''; ?>">Dashboard</a></li>
        <li><a href="edit_profile.php" class="<?php echo ($current_page == 'edit_profile.php') ? 'active' : ''; ?>">Edit Profile</a></li>
        
        <li><a href="request_custom.php" class="<?php echo ($current_page == 'request_custom.php') ? 'active' : ''; ?>">Custom Request</a></li>
        
        <li><a href="product.php" class="<?php echo ($current_page == 'product.php') ? 'active' : ''; ?>">Product</a></li>
        <li><a href="view_cart.php" class="<?php echo ($current_page == 'view_cart.php') ? 'active' : ''; ?>">Cart</a></li>
        <li><a href="index.php">View Shop</a></li>
        <li style="margin-top: 50px;"><a href="logout.php">Logout</a></li>
    </ul>
</nav>