<?php
session_start();
include 'db_conn.php';

// Security: User must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle Form Submission
if (isset($_POST['submit_request'])) {
    $user_id = $_SESSION['user_id'];
    $desc = $_POST['description'];
    $budget = $_POST['budget'];

    $stmt = $conn->prepare("INSERT INTO custom_requests (user_id, description, budget) VALUES (?, ?, ?)");
    $stmt->bind_param("isd", $user_id, $desc, $budget);

    if ($stmt->execute()) {
        // --- THE FIX: SAVE MESSAGE TO SESSION & REDIRECT ---
        $_SESSION['flash_msg'] = "Request submitted! We will contact you soon.";
        $_SESSION['flash_type'] = "success";
        header("Location: request_custom.php"); 
        exit(); // Stop code here
    } else {
        $message = "Error: " . $conn->error;
        $msg_type = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Request Custom Toy - Timby</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="dashboard-wrapper">
        
        <?php include 'sidebar_member.php'; ?>

        <main class="main-content">
            <h2 class="page-title">Custom Toy Request</h2>
            <p style="color: #666; margin-bottom: 30px;">Have an idea? Let our artisans build it for you.</p>

            <?php if (isset($_SESSION['flash_msg'])): ?>
                <div class="alert <?php echo $_SESSION['flash_type']; ?>">
                    <?php echo $_SESSION['flash_msg']; ?>
                </div>
                <?php unset($_SESSION['flash_msg']); unset($_SESSION['flash_type']); ?>
            <?php endif; ?>

            <div class="request-card">
                <form method="POST">
                    <label>Describe your idea</label>
                    <textarea name="description" rows="5" placeholder="E.g. I want a wooden rocking horse shaped like a dragon..." required></textarea>

                    <label>Estimated Budget (RM)</label>
                    <input type="number" name="budget" placeholder="0.00" step="0.01" required>

                    <button type="submit" name="submit_request" class="btn-submit">Submit Request</button>
                </form>
            </div>

            <div class="history-section">
                <h3 style="color:#5D4037;">My Request History</h3>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 15%;">Date</th>
                            <th style="width: 55%;">Description</th>
                            <th style="width: 15%;">Budget</th>
                            <th style="width: 15%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $user_id = $_SESSION['user_id'];
                        $sql = "SELECT * FROM custom_requests WHERE user_id = $user_id ORDER BY request_date DESC";
                        $res = $conn->query($sql);
                        
                        if ($res->num_rows > 0) {
                            while($row = $res->fetch_assoc()) {
                                echo "<tr>
                                    <td>".date("d M Y", strtotime($row['request_date']))."</td>
                                    <td>".nl2br(htmlspecialchars($row['description']))."</td>
                                    <td>RM ".number_format($row['budget'], 2)."</td>
                                    <td><span style='font-weight:bold; color:#8B5E3C;'>".$row['status']."</span></td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align:center; padding:20px; color:#888;'>No requests yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>
</body>
</html>