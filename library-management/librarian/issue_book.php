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
    $book_id = $_POST['book_id'];
    $member_id = $_POST['member_id'];
    $due_date = $_POST['due_date'];

    // Check availability
    $check_sql = "SELECT quantity FROM books WHERE book_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();

    if ($book && $book['quantity'] > 0) {
        // Issue book
        $conn->begin_transaction();
        try {
            $issue_sql = "INSERT INTO issued_books (book_id, member_id, due_date) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($issue_sql);
            $stmt->bind_param("iis", $book_id, $member_id, $due_date);
            $stmt->execute();

            // Update quantity
            $update_sql = "UPDATE books SET quantity = quantity - 1 WHERE book_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("i", $book_id);
            $stmt->execute();

            $conn->commit();
            $message = "Book issued successfully.";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error issuing book: " . $e->getMessage();
        }
    } else {
        $error = "Book not available.";
    }
}

// Fetch books and members for dropdowns
$books = $conn->query("SELECT book_id, title FROM books WHERE quantity > 0");
$members = $conn->query("SELECT member_id, name FROM members");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Book</title>
    <link rel="stylesheet" href="../style.css?v=<?php echo time(); ?>">
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">Librarian Panel</div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="books.php">Books</a>
                <a href="issue_book.php" class="active">Issue Book</a>
                <a href="return_book.php">Return Book</a>
                <a href="fines.php">Fines</a>
                <a href="logout.php" style="color: var(--danger-color);">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <h2>Issue Book</h2>
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="book_id">Select Book</label>
                    <select name="book_id" id="book_id" class="form-control" required>
                        <option value="">-- Select Book --</option>
                        <?php while ($row = $books->fetch_assoc()): ?>
                            <option value="<?php echo $row['book_id']; ?>"><?php echo htmlspecialchars($row['title']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="member_id">Select Member</label>
                    <select name="member_id" id="member_id" class="form-control" required>
                        <option value="">-- Select Member --</option>
                        <?php while ($row = $members->fetch_assoc()): ?>
                            <option value="<?php echo $row['member_id']; ?>"><?php echo htmlspecialchars($row['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="due_date">Due Date</label>
                    <input type="date" name="due_date" id="due_date" class="form-control" required
                        min="<?php echo date('Y-m-d'); ?>">
                </div>
                <button type="submit" class="btn">Issue Book</button>
            </form>
        </div>
    </div>
</body>

</html>