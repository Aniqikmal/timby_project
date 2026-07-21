<?php
session_start();
include 'db_conn.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>About Us - Timby</title>
    <style>
        
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Georgia', serif; 
            background-color: #F5F5DC; 
            color: #333;
            display: flex; flex-direction: column; min-height: 100vh; 
        }
        a { text-decoration: none; color: inherit; }

        
        .main-header {
            width: 100%; height: 90px; background-color: #8B5E3C;
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 30px; position: relative; z-index: 100;
        }

        .logo-container { display: flex; align-items: center; gap: 20px; }
        .brand-logo {
            background-color: #E8E0D5; height: 60px; border-radius: 50px;
            padding: 5px 25px; display: flex; align-items: center;
        }
        .brand-logo img {
            height: 100%; width: auto; object-fit: contain;
            mix-blend-mode: darken; filter: contrast(1.1);
        }
        .header-slogan { color: #E8E0D5; font-size: 0.95rem; line-height: 1.2; }

        .header-buttons { display: flex; flex-direction: column; gap: 8px; }
        .btn {
            background-color: #E8E0D5; color: #2F4F4F; padding: 5px 20px;
            border-radius: 2px; font-weight: bold; font-size: 0.85rem;
            text-align: center; min-width: 100px;
        }
        .btn:hover { background-color: white; }

        
        .mission-hero {
            background-color: #8B5E3C; color: #E8E0D5;
            text-align: center; padding: 80px 20px; margin-bottom: 50px;
        }
        .mission-hero h1 { font-size: 3rem; margin-bottom: 30px; color: #fff; text-transform: uppercase; letter-spacing: 2px; }
        .mission-hero p { max-width: 800px; margin: 0 auto; font-size: 1.2rem; line-height: 1.8; }

        
        .content-container { max-width: 1100px; margin: 0 auto; padding: 0 20px; }
        .about-section { display: flex; align-items: center; gap: 60px; margin-bottom: 80px; }
        .about-section.reverse { flex-direction: row-reverse; }
        
        .about-text { flex: 1; }
        .about-label { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1.5px; color: #888; margin-bottom: 10px; font-weight: bold; }
        .about-text h2 { font-size: 2.5rem; color: #2F4F4F; margin-bottom: 20px; line-height: 1.2; }
        .about-text p { font-size: 1.05rem; color: #555; line-height: 1.6; }

        .about-image { flex: 1; height: 400px; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .about-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
        .about-image:hover img { transform: scale(1.05); }

        
        .site-footer { background-color: #5D4037; color: #F5F5DC; padding: 60px 40px 20px 40px; margin-top: auto; }
        .footer-columns { display: grid; grid-template-columns: 1fr 1fr; max-width: 1200px; margin: 0 auto; gap: 50px; padding-bottom: 40px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .footer-col h3 { font-size: 1.1rem; margin-bottom: 20px; color: white; font-weight: bold; }
        .footer-links a { display: block; color: #E8E0D5; margin-bottom: 12px; font-size: 0.95rem; transition: 0.3s; }
        .footer-links a:hover { color: white; text-decoration: underline; }
        .footer-info p { margin-bottom: 15px; line-height: 1.6; color: #E8E0D5; font-size: 0.95rem; }
        .footer-bottom { max-width: 1200px; margin: 30px auto 0 auto; display: flex; flex-direction: column; align-items: center; gap: 20px; }
        .payment-icons { display: flex; gap: 10px; }
        .pay-icon { background: white; color: #333; padding: 5px 10px; border-radius: 4px; font-weight: bold; font-size: 0.8rem; font-family: sans-serif; letter-spacing: 0.5px; }
        .copyright { font-size: 0.8rem; color: #D7CCC8; }

        @media (max-width: 768px) { .about-section, .about-section.reverse { flex-direction: column; gap: 30px; } .about-image { height: 250px; width: 100%; } }
    </style>
</head>
<body>

    <header class="main-header">
        <div class="logo-container">
            <div class="brand-logo"><img src="images/logo2.png" alt="Timby Logo"></div>
            <div class="header-slogan">Explore our collection of eco-friendly<br>wooden toys!</div>
        </div>
        <div class="header-buttons">
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if($_SESSION['role'] == 'marketing'): ?>
                    <a href="marketing_dashboard.php" class="btn">Marketing Panel</a>
                <?php elseif($_SESSION['role'] == 'admin'): ?>
                    <a href="admin_dashboard.php" class="btn">Admin Panel</a>
                <?php else: ?>
                    <a href="member_dashboard.php" class="btn">Dashboard</a>
                <?php endif; ?>
                <a href="logout.php" class="btn">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn">Login</a>
                <a href="index.php" class="btn">Shop</a>
            <?php endif; ?>
        </div>
    </header>

    <section class="mission-hero">
        <h1>TIMBY WOODEN TOYS</h1>
        <p>
            Started in 2025, we are the specialist toy store that empowers parents to raise curious, 
            able, and confident little problem-solvers with toys curated by nature lovers, for nature lovers.
            <br><br>
            Having served families globally, our mission is to <strong>SIMPLIFY</strong> play, 
            <strong>SPARK</strong> imagination, and <strong>STRENGTHEN</strong> family connections.
        </p>
    </section>

    <div class="content-container">
        
        <div class="about-section">
            <div class="about-text">
                <div class="about-label">WHAT WE STAND FOR</div>
                <h2>Only the Essentials</h2>
                <p>We understand that parenting can feel overwhelming. That's why we carefully select versatile, open-ended wooden toys designed to grow with your little one, encouraging creativity rather than consumption.</p>
            </div>
            <div class="about-image"><img src="images/about1.jpg" alt="Minimalist Toys"></div>
        </div>

        <div class="about-section reverse">
            <div class="about-text">
                <div class="about-label">QUALITY & SUSTAINABILITY</div>
                <h2>Only the Best</h2>
                <p>We're committed to offering toys that are as durable as they are delightful. Handmade, sustainably sourced, and crafted with your little one's safety in mind.</p>
            </div>
            <div class="about-image"><img src="images/about2.jpg" alt="Quality Wood"></div>
        </div>

        <div class="about-section">
            <div class="about-text">
                <div class="about-label">OUR PROMISE</div>
                <h2>Curated Service</h2>
                <p>Timby isn't just a store; it's a community. Whether you need advice on the perfect gift or help tracking an order, our team is dedicated to providing personal support.</p>
            </div>
            <div class="about-image"><img src="images/about3.jpg" alt="Customer Service"></div>
        </div>

    </div>

    <footer class="site-footer">
        <div class="footer-columns">
            <div class="footer-col">
                <h3>Quick links</h3>
                <div class="footer-links">
                    <a href="about_us.php">About Us</a>
                    <a href="contact.php">Contact Us</a>
                </div>
            </div>
            <div class="footer-col">
                <h3>Contact Us</h3>
                <div class="footer-info">
                    <p><strong>Address:</strong><br>UNIMAS<br>Jln Datuk Mohammad Musa, 94300 Kota Samarahan, Sarawak.</p>
                    <p><strong>Email:</strong> care@timby.com</p>
                    <p><strong>Instagram:</strong> @timby_official</p>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="payment-icons">
                <span class="pay-icon" style="border-bottom:3px solid #00579f;">Pay</span>
                <span class="pay-icon" style="border-bottom:3px solid #ea4335;">G-Pay</span>
                <span class="pay-icon" style="border-bottom:3px solid #eb001b;">Mastercard</span>
                <span class="pay-icon" style="border-bottom:3px solid #1a1f71;">VISA</span>
            </div>
            <p class="copyright">© 2025, Timby. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>