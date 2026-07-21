<?php
// -------------------------------------------------------
// DATABASE CONFIGURATION TEMPLATE
// -------------------------------------------------------
// 1. Copy this file and rename it to: db_conn.php
// 2. Fill in your own database credentials below
// 3. NEVER commit the real db_conn.php to Git
// -------------------------------------------------------

define('DB_ENV', 'local'); // Change to 'live' when deploying

if (DB_ENV === 'live') {
    // --- LIVE (e.g. InfinityFree / cPanel) ---
    $sname   = "your_live_host";        // e.g. sql308.infinityfree.com
    $uname   = "your_live_username";    // e.g. if0_xxxxxxx
    $password = "your_live_password";
    $db_name  = "your_live_dbname";     // e.g. if0_xxxxxxx_timbydb
} else {
    // --- LOCAL (XAMPP) ---
    $sname   = "localhost";
    $uname   = "root";
    $password = "";           // Default XAMPP has no password
    $db_name  = "timbydb";    // Your local database name
}

$conn = mysqli_connect($sname, $uname, $password, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
