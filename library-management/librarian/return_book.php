<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['librarian_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $issue_id = $_POST['issue_id'];
    $return_date = date('Y-m-d');

    // Get issue details
    $sql = "SELECT * FROM issued_books WHERE issue_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $issue_id);
    $stmt->execute();
    $issue = $stmt->get_result()->fetch_assoc();

    if ($issue) {
        $conn->begin_transaction();
        try {
            // Update return date
            $update_sql = "UPDATE issued_books SET return_date = ? WHERE issue_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("si", $return_date, $issue_id);
            $stmt->execute();

            // Increase book quantity
            $book_sql = "UPDATE books SET quantity = quantity + 1 WHERE book_id = ?";
            $stmt = $conn->prepare($book_sql);
            $stmt->bind_param("i", $issue['book_id']);
            $stmt->execute();

            // Calculate fine
            $due_date = new DateTime($issue['due_date']);
            $returned = new DateTime($return_date);

            if ($returned > $due_date) {
                $diff = $returned->diff($due_date);
                $days_overdue = $diff->days;
                $fine_amount = $days_overdue * 1.00; // $1 per day fine

                $fine_sql = "INSERT INTO fines (issue_id, amount) VALUES (?, ?)";
                $stmt = $conn->prepare($fine_sql);
                $stmt->bind_param("id", $issue_id, $fine_amount);
                $stmt->execute();

                $message = "Book returned. Fine generated: $" . number_format($fine_amount, 2);
            } else {
                $message = "Book returned successfully. No fine.";
            }

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error returning book: " . $e->getMessage();
        }
    }
}

// Fetch issued books
$sql = "SELECT ib.issue_id, b.title, m.name, ib.issue_date, ib.due_date 
        FROM issued_books ib 
        JOIN books b ON ib.book_id = b.book_id 
        JOIN members m ON ib.member_id = m.member_id 
        WHERE ib.return_date IS NULL";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Book</title>
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
                <a href="return_book.php" class="active">Return Book</a>
                <a href="fines.php">Fines</a>
                <a href="logout.php" style="color: var(--danger-color);">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>Return Book</h2>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Issue ID</th>
                        <th>Book</th>
                        <th>Member</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['issue_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo $row['issue_date']; ?></td>
                                <td><?php echo $row['due_date']; ?></td>
                                <td>
                                    <form method="POST" action=""
                                        onsubmit="return confirm('Are you sure you want to return this book?');">
                                        <input type="hidden" name="issue_id" value="<?php echo $row['issue_id']; ?>">
                                        <button type="submit" class="btn btn-danger">Return</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No issued books found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>