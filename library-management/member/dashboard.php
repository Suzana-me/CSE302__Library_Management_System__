<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['member_id'];

// Fetch stats
$issued_count = $conn->query("SELECT COUNT(*) as total FROM issued_books WHERE member_id = $member_id AND return_date IS NULL")->fetch_assoc()['total'] ?? 0;
$total_issued = $conn->query("SELECT COUNT(*) as total FROM issued_books WHERE member_id = $member_id")->fetch_assoc()['total'] ?? 0;
$fine_total = $conn->query("SELECT SUM(f.amount) as total FROM fines f JOIN issued_books ib ON f.issue_id = ib.issue_id WHERE ib.member_id = $member_id")->fetch_assoc()['total'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard</title>
    <link rel="stylesheet" href="../style.css?v=<?php echo time(); ?>">
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">Member Panel</div>
            <div class="nav-links">
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="books.php">Browse Books</a>
                <a href="my_books.php">My History</a>
                <a href="logout.php" style="color: var(--danger-color);">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['member_name']); ?></h1>

        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-header">Currently Issued</div>
                <div class="stat-value"><?php echo $issued_count; ?></div>
            </div>
            <div class="stat-card orange">
                <div class="stat-header">Total Books Taken</div>
                <div class="stat-value"><?php echo $total_issued; ?></div>
            </div>
            <div class="stat-card red">
                <div class="stat-header">Total Fines</div>
                <div class="stat-value">$<?php echo number_format($fine_total, 2); ?></div>
            </div>
        </div>
    </div>
</body>

</html>