<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['librarian_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $book_id = $_GET['delete'];
    $sql = "DELETE FROM books WHERE book_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $book_id);
    if ($stmt->execute()) {
        $message = "Book deleted successfully.";
    } else {
        $error = "Error deleting book.";
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $dept_id = $_POST['dept_id'];
    $isbn = $_POST['isbn'];
    $quantity = $_POST['quantity'];
    $book_id = $_POST['book_id'] ?? '';

    if ($book_id) {
        // Update
        $sql = "UPDATE books SET title=?, author=?, dept_id=?, isbn=?, quantity=? WHERE book_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisii", $title, $author, $dept_id, $isbn, $quantity, $book_id);
        if ($stmt->execute()) {
            $message = "Book updated successfully.";
        } else {
            $error = "Error updating book.";
        }
    } else {
        // Insert
        $sql = "INSERT INTO books (title, author, dept_id, isbn, quantity) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisi", $title, $author, $dept_id, $isbn, $quantity);
        if ($stmt->execute()) {
            $message = "Book added successfully.";
        } else {
            $error = "Error adding book.";
        }
    }
}

// Fetch Departments
$departments = $conn->query("SELECT * FROM departments");

// Search & List Books
$search = $_GET['search'] ?? '';
$sql = "SELECT b.*, d.dept_name 
        FROM books b 
        LEFT JOIN departments d ON b.dept_id = d.dept_id 
        WHERE b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?";
$stmt = $conn->prepare($sql);
$searchParam = "%$search%";
$stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
$stmt->execute();
$books = $stmt->get_result();

// Get Book for Edit
$edit_book = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_book = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - Librarian</title>
    <link rel="stylesheet" href="../style.css?v=<?php echo time(); ?>">
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">Librarian Panel</div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="books.php" class="active">Books</a>
                <a href="issue_book.php">Issue Book</a>
                <a href="return_book.php">Return Book</a>
                <a href="fines.php">Fines</a>
                <a href="logout.php" style="color: var(--danger-color);">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>Manage Books</h2>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <h3><?php echo $edit_book ? 'Edit Book' : 'Add New Book'; ?></h3>
            <form method="POST" action="books.php">
                <?php if ($edit_book): ?>
                    <input type="hidden" name="book_id" value="<?php echo $edit_book['book_id']; ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" required class="form-control" value="<?php echo $edit_book['title'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label>Author</label>
                    <input type="text" name="author" required class="form-control" value="<?php echo $edit_book['author'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <select name="dept_id" class="form-control">
                        <?php 
                        $departments->data_seek(0);
                        while ($dept = $departments->fetch_assoc()): ?>
                            <option value="<?php echo $dept['dept_id']; ?>" <?php echo ($edit_book && $edit_book['dept_id'] == $dept['dept_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['dept_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>ISBN</label>
                    <input type="text" name="isbn" class="form-control" value="<?php echo $edit_book['isbn'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" name="quantity" required class="form-control" value="<?php echo $edit_book['quantity'] ?? 1; ?>">
                </div>
                <button type="submit" class="btn"><?php echo $edit_book ? 'Update Book' : 'Add Book'; ?></button>
                <?php if ($edit_book): ?>
                    <a href="books.php" class="btn btn-warning">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Book List</h3>
                <form method="GET" action="" style="display: flex; gap: 10px;">
                    <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>" class="form-control">
                    <button type="submit" class="btn">Search</button>
                </form>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>ISBN</th>
                        <th>Dept</th>
                        <th>Qty</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($books->num_rows > 0): ?>
                        <?php while ($row = $books->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['book_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['author']); ?></td>
                                <td><?php echo htmlspecialchars($row['isbn']); ?></td>
                                <td><?php echo htmlspecialchars($row['dept_name']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td>
                                    <a href="?edit=<?php echo $row['book_id']; ?>" class="btn btn-warning" style="padding: 5px 10px; font-size: 0.8em;">Edit</a>
                                    <a href="?delete=<?php echo $row['book_id']; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8em;" onclick="return confirm('Are you sure?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No books found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>