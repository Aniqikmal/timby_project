<?php
session_start();
include 'db_conn.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['name']    = $user['full_name'];

            if ($user['role'] == 'admin')          { header("Location: admin_dashboard.php"); }
            elseif ($user['role'] == 'marketing')  { header("Location: marketing_dashboard.php"); }
            else                                    { header("Location: member_dashboard.php"); }
            exit();
        } else { $error = "Incorrect password. Please try again."; }
    } else { $error = "No account found with that email."; }
}

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Timby</title>
    <meta name="description" content="Login to your Timby account.">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

    <div class="auth-card">
        <div class="auth-logo">T</div>
        <h1>Welcome Back</h1>
        <p>Sign in to your Timby account</p>

        <?php if($success): ?>
            <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control"
                       placeholder="example@email.com" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control"
                       placeholder="Enter your password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary">Login</button>
        </form>

        <div class="auth-links">
            <a href="register.php">Don't have an account? Create one →</a>
            <a href="index.php">← Back to Home</a>
        </div>
    </div>

</body>
</html>