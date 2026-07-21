<?php
session_start();
include 'db_conn.php';

$msg_sent = false;
if (isset($_POST['send_message'])) {
    $msg_sent = true;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Contact Us - Timby</title>
    <style>
        /* --- RESET & FONTS --- */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Georgia', serif; background-color: #F5F5DC; color: #333; display: flex; flex-direction: column; min-height: 100vh; }
        a { text-decoration: none; color: inherit; }

        /* --- HEADER --- */
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
        .brand-logo img { height: 100%; width: auto; object-fit: contain; mix-blend-mode: darken; filter: contrast(1.1); }
        .header-slogan { color: #E8E0D5; font-size: 0.95rem; line-height: 1.2; }
        .header-buttons { display: flex; flex-direction: column; gap: 8px; }
        .btn {
            background-color: #E8E0D5; color: #2F4F4F; padding: 5px 20px;
            border-radius: 2px; font-weight: bold; font-size: 0.85rem;
            text-align: center; min-width: 100px;
        }
        .btn:hover { background-color: white; }

        /* --- CONTACT STYLES --- */
        .contact-hero { text-align: center; padding: 60px 20px; background-color: #8B5E3C; color: #E8E0D5; }
        .contact-hero h1 { font-size: 3rem; margin-bottom: 10px; }
        .contact-hero p { font-size: 1.2rem; max-width: 600px; margin: 0 auto; }

        .content-wrapper {
            max-width: 1100px; margin: -40px auto 50px auto;
            display: grid; grid-template-columns: 1fr 1.2fr; gap: 40px; padding: 0 20px; z-index: 10;
        }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .info-card {
            background: white; padding: 30px 20px; border-radius: 15px; text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: transform 0.3s ease;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        .info-card:hover { transform: translateY(-5px); border-bottom: 5px solid #8B5E3C; }
        .icon-circle { width: 50px; height: 50px; background-color: #FDF5E6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; font-size: 1.5rem; }
        .info-card h3 { font-size: 1rem; color: #8B5E3C; margin-bottom: 10px; }
        .info-card p { font-size: 0.9rem; color: #666; line-height: 1.4; }

        .form-container {
            background: white; padding: 40px; border-radius: 2px;
            box-shadow: 10px 10px 0px rgba(139, 94, 60, 0.2); border: 1px solid #eee; position: relative;
        }
        .tape {
            position: absolute; top: -15px; left: 50%; transform: translateX(-50%);
            width: 100px; height: 30px; background-color: rgba(255,255,255,0.4);
            border-left: 1px dashed rgba(0,0,0,0.1); border-right: 1px dashed rgba(0,0,0,0.1);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .form-container h2 { color: #5D4037; margin-bottom: 25px; }
        label { display: block; font-weight: bold; font-size: 0.85rem; color: #8B5E3C; margin-bottom: 5px; }
        input, textarea { width: 100%; padding: 12px; margin-bottom: 20px; border: none; border-bottom: 2px solid #eee; background-color: transparent; font-family: 'Georgia', serif; font-size: 1rem; outline: none; transition: 0.3s; }
        input:focus, textarea:focus { border-bottom-color: #8B5E3C; background-color: #FDF5E6; }
        
        .btn-send { background-color: #8B5E3C; color: white; border: none; padding: 15px 30px; font-weight: bold; border-radius: 30px; cursor: pointer; float: right; transition: 0.3s; }
        .btn-send:hover { background-color: #5D4037; transform: scale(1.05); }
        .success-box { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center; }

        .map-section {
            width: 100%; height: 300px; background-color: #ddd; margin-top: 50px; position: relative;
            display: flex; align-items: center; justify-content: center; color: #666; font-size: 1.5rem; font-weight: bold;
            background-image: url('https://maps.googleapis.com/maps/api/staticmap?center=Kota+Samarahan&zoom=13&size=1200x300&sensor=false');
            background-size: cover; background-position: center;
        }
        .map-overlay { background: rgba(255,255,255,0.8); padding: 20px; border-radius: 10px; }

        /* --- FOOTER --- */
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

        @media(max-width:768px){ .content-wrapper { grid-template-columns: 1fr; } }
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

    <div class="contact-hero">
        <h1>We'd Love to Chat</h1>
        <p>Whether you have a question about a toy, shipping, or just want to say hello, our team is ready to help.</p>
    </div>

    <div class="content-wrapper">
        <div class="info-grid">
            <div class="info-card">
                <div class="icon-circle">📍</div>
                <h3>Visit Us</h3>
                <p>UNIMAS<br>Kota Samarahan</p>
            </div>
            <div class="info-card">
                <div class="icon-circle">📞</div>
                <h3>Call Us</h3>
                <p>+60 82-123 456<br>Mon-Fri, 9am-5pm</p>
            </div>
            <div class="info-card">
                <div class="icon-circle">✉️</div>
                <h3>Email</h3>
                <p>care@timby.com<br>Support Team</p>
            </div>
            <div class="info-card">
                <div class="icon-circle">❤️</div>
                <h3>Socials</h3>
                <p>@timby_official<br>Instagram & FB</p>
            </div>
        </div>

        <div class="form-container">
            <div class="tape"></div>
            <h2>Send a Message</h2>
            <?php if($msg_sent): ?>
                <div class="success-box">Thanks! We've received your message.</div>
            <?php endif; ?>
            <form method="POST">
                <label>Your Name</label>
                <input type="text" name="name" placeholder="John Doe" required>
                <label>Email Address</label>
                <input type="email" name="email" placeholder="john@example.com" required>
                <label>Message</label>
                <textarea name="message" rows="5" placeholder="Tell us what's on your mind..." required></textarea>
                <button type="submit" name="send_message" class="btn-send">Send Message ➔</button>
            </form>
        </div>
    </div>

    <div class="map-section">
        <div class="map-overlay">📍 We are located inside UNIMAS Campus</div>
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