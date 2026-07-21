<?php
session_start();
include 'db_conn.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$msg_type = "";

// 2. Handle Form Submission
if (isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];

    // Update Database
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $full_name, $email, $user_id);

    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
        $msg_type = "success";
        $_SESSION['name'] = $full_name; // Update session name immediately
    } else {
        $message = "Error updating profile: " . $conn->error;
        $msg_type = "error";
    }
}

// 3. Fetch Current User Data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile - Timby</title>
    <style>
        /* --- 1. PAGE SETUP & BACKGROUND (Matches Login Page) --- */
        body {
            margin: 0; padding: 0; font-family: 'Georgia', serif;
            height: 100vh; display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }
        body::before {
            content: ""; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: url('images/shop3.png') no-repeat center center;
            background-size: cover;
            filter: blur(8px); transform: scale(1.1); z-index: -1;
        }

        /* --- 2. CARD STYLE --- */
        .edit-card {
            background: white; padding: 40px; width: 400px;
            border-radius: 15px; text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        /* LOGO / ICON AREA */
        .icon-circle {
            width: 60px; height: 60px; background-color: #E8E0D5;
            border-radius: 50%; margin: 0 auto 15px;
            display: flex; align-items: center; justify-content: center; overflow: hidden;
        }
        .icon-circle img { width: 60%; height: auto; mix-blend-mode: multiply; }

        h2 { color: #8B5E3C; margin-bottom: 25px; font-size: 1.5rem; }

        /* FORM ELEMENTS */
        label { display: block; text-align: left; font-size: 0.85rem; font-weight: bold; color: #555; margin-bottom: 5px; }
        
        input { 
            width: 100%; padding: 12px; margin-bottom: 15px; 
            border: 1px solid #ddd; border-radius: 6px; 
            background: #FFF8F0; box-sizing: border-box;
        }

        .btn-save {
            width: 100%; background-color: #8B5E3C; color: white;
            padding: 12px; border: none; border-radius: 6px;
            font-size: 1rem; cursor: pointer; font-weight: bold; transition: 0.3s;
        }
        .btn-save:hover { background-color: #6d4a2f; }

        /* LINKS & ALERTS */
        .back-link { display: block; margin-top: 15px; font-size: 0.85rem; color: #8B5E3C; text-decoration: none; font-weight: bold; }
        .back-link:hover { text-decoration: underline; }

        .alert { padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 0.9rem; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <div class="edit-card">
        <div class="icon-circle">
            <img src="images/leaf.png" alt="Edit Icon">
        </div>

        <h2>Edit Profile</h2>

        <?php if ($message): ?>
            <div class="alert <?php echo $msg_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

            <label>Email Address</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <button type="submit" name="update_profile" class="btn-save">Save Changes</button>
        </form>

        <a href="member_dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>

</body>
</html>