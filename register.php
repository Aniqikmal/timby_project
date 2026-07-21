<?php
session_start();
include 'db_conn.php';

if (isset($_POST['register'])) {
    $name     = htmlspecialchars(trim($_POST['name']));
    $email    = $_POST['email'];
    $password = $_POST['password'];

    if (strlen($password) < 6 || !preg_match("/[A-Z]/", $password) || !preg_match("/[0-9]/", $password) || !preg_match("/[\W]/", $password)) {
        $error = "Password must be 6+ chars with at least 1 uppercase letter, 1 number, and 1 special character.";
    } else {
        $check = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "This email is already registered. Please login.";
        } else {
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $role = 'member';
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed_pass, $role);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Account created successfully! Please log in.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Error creating account. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — Timby</title>
    <meta name="description" content="Create a new Timby account.">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

    <div class="auth-card">
        <div class="auth-logo">T</div>
        <h1>Create Account</h1>
        <p>Join the Timby community today</p>

        <?php if(isset($error)): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control"
                       placeholder="Your full name" required
                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control"
                       placeholder="example@email.com" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control"
                       placeholder="Create a strong password"
                       pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{6,}"
                       title="Must contain at least one number, one uppercase, one special character, and 6+ characters"
                       required>
                <p style="font-size:0.75rem; color:var(--color-on-surface-variant); margin-top:6px;">
                    6+ chars · 1 Uppercase · 1 Number · 1 Special char (!@#$&*)
                </p>
            </div>
            <button type="submit" name="register" class="btn btn-primary">Create Account</button>
        </form>

        <div class="auth-links">
            <a href="login.php">Already a member? Login →</a>
            <a href="index.php">← Back to Home</a>
        </div>
    </div>

</body>
</html>