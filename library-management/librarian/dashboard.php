<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['librarian_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch stats
$books_count = $conn->query("SELECT SUM(quantity) as total FROM books")->fetch_assoc()['total'] ?? 0;
$issued_count = $conn->query("SELECT COUNT(*) as total FROM issued_books WHERE return_date IS NULL")->fetch_assoc()['total'] ?? 0;
$members_count = $conn->query("SELECT COUNT(*) as total FROM members")->fetch_assoc()['total'] ?? 0;
$fines_total = $conn->query("SELECT SUM(amount) as total FROM fines")->fetch_assoc()['total'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Dashboard</title>
    <link rel="stylesheet" href="../style.css?v=<?php echo time(); ?>">
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">Librarian Panel</div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="books.php">Books</a>
                <a href="issue_book.php">Issue Book</a>
                <a href="return_book.php">Return Book</a>
                <a href="fines.php">Fines</a>
                <a href="logout.php" style="color: var(--danger-color);">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['librarian_name']); ?></h1>

        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 30px;">
            <div class="card" style="text-align: center;">
                <h3>Total Books</h3>
                <p style="font-size: 2em; font-weight: bold; color: var(--primary-color);"><?php echo $books_count; ?>
                </p>
            </div>
            <div class="card" style="text-align: center;">
                <h3>Issued Books</h3>
                <p style="font-size: 2em; font-weight: bold; color: #f39c12;"><?php echo $issued_count; ?></p>
            </div>
            <div class="card" style="text-align: center;">
                <h3>Members</h3>
                <p style="font-size: 2em; font-weight: bold; color: var(--success-color);"><?php echo $members_count; ?>
                </p>
            </div>
            <div class="card" style="text-align: center;">
                <h3>Total Fines Collected</h3>
                <p style="font-size: 2em; font-weight: bold; color: var(--danger-color);">
                    $<?php echo number_format($fines_total, 2); ?></p>
            </div>
        </div>
    </div>
</body>

</html>