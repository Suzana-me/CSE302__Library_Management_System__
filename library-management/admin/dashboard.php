<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch stats
$dept_count = $conn->query("SELECT COUNT(*) as total FROM departments")->fetch_assoc()['total'] ?? 0;
$librarian_count = $conn->query("SELECT COUNT(*) as total FROM librarians")->fetch_assoc()['total'] ?? 0;
$member_count = $conn->query("SELECT COUNT(*) as total FROM members")->fetch_assoc()['total'] ?? 0;
$book_count = $conn->query("SELECT SUM(quantity) as total FROM books")->fetch_assoc()['total'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../style.css?v=<?php echo time(); ?>">
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">Admin Panel</div>
            <div class="nav-links">
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="books.php">Books</a>
                <a href="departments.php">Departments</a>
                <a href="librarians.php">Librarians</a>
                <a href="members.php">Members</a>
                <a href="logout.php" style="color: var(--danger-color);">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Welcome, Admin</h1>

        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-header">Departments</div>
                <div class="stat-value"><?php echo $dept_count; ?></div>
            </div>
            <div class="stat-card orange">
                <div class="stat-header">Librarians</div>
                <div class="stat-value"><?php echo $librarian_count; ?></div>
            </div>
            <div class="stat-card green">
                <div class="stat-header">Members</div>
                <div class="stat-value"><?php echo $member_count; ?></div>
            </div>
            <div class="stat-card purple">
                <div class="stat-header">Total Books</div>
                <div class="stat-value"><?php echo $book_count; ?></div>
            </div>
        </div>
    </div>
</body>

</html>