<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['member_id'];

$sql = "SELECT ib.issue_id, b.title, b.author, ib.issue_date, ib.due_date, ib.return_date, f.amount 
        FROM issued_books ib 
        JOIN books b ON ib.book_id = b.book_id 
        LEFT JOIN fines f ON ib.issue_id = f.issue_id 
        WHERE ib.member_id = $member_id 
        ORDER BY ib.issue_date DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Books</title>
    <link rel="stylesheet" href="../style.css?v=<?php echo time(); ?>">
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">Member Panel</div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="my_books.php" class="active">My Books</a>
                <a href="logout.php" style="color: var(--danger-color);">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>My Issued Books</h2>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Fine</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['author']); ?></td>
                                <td><?php echo $row['issue_date']; ?></td>
                                <td><?php echo $row['due_date']; ?></td>
                                <td><?php echo $row['return_date'] ? $row['return_date'] : '-'; ?></td>
                                <td><?php echo $row['amount'] ? '$' . number_format($row['amount'], 2) : '-'; ?></td>
                                <td>
                                    <?php
                                    if ($row['return_date']) {
                                        echo '<span style="color: var(--success-color);">Returned</span>';
                                    } else {
                                        $due = new DateTime($row['due_date']);
                                        $now = new DateTime();
                                        if ($now > $due) {
                                            echo '<span style="color: var(--danger-color);">Overdue</span>';
                                        } else {
                                            echo '<span style="color: #f39c12;">Issued</span>';
                                        }
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No books found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>