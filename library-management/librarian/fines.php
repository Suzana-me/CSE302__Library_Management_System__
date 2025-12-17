<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['librarian_id'])) {
    header("Location: login.php");
    exit();
}

$sql = "SELECT f.fine_id, f.amount, ib.issue_date, ib.due_date, ib.return_date, b.title, m.name 
        FROM fines f 
        JOIN issued_books ib ON f.issue_id = ib.issue_id 
        JOIN books b ON ib.book_id = b.book_id 
        JOIN members m ON ib.member_id = m.member_id";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fines</title>
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
                <a href="fines.php" class="active">Fines</a>
                <a href="logout.php" style="color: var(--danger-color);">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>Fines Collected</h2>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Fine ID</th>
                        <th>Member</th>
                        <th>Book</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['fine_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo $row['due_date']; ?></td>
                                <td><?php echo $row['return_date']; ?></td>
                                <td style="color: var(--danger-color); font-weight: bold;">
                                    $<?php echo number_format($row['amount'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No fines found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>