<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit();
}

// Search
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Books</title>
    <link rel="stylesheet" href="../style.css?v=<?php echo time(); ?>">
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">Member Panel</div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="books.php" class="active">Browse Books</a>
                <a href="my_books.php">My History</a>
                <a href="logout.php" style="color: var(--danger-color);">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Browse Books</h2>
            <form method="GET" action="" style="display: flex; gap: 10px;">
                <input type="text" name="search" placeholder="Search Title, Author, ISBN..." value="<?php echo htmlspecialchars($search); ?>" class="form-control" style="width: 300px;">
                <button type="submit" class="btn">Search</button>
            </form>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>ISBN</th>
                        <th>Department</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($books->num_rows > 0): ?>
                        <?php while ($row = $books->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['author']); ?></td>
                                <td><?php echo htmlspecialchars($row['isbn']); ?></td>
                                <td><?php echo htmlspecialchars($row['dept_name']); ?></td>
                                <td>
                                    <?php if ($row['quantity'] > 0): ?>
                                        <span class="badge" style="background: var(--success-color); color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.8em;">Available (<?php echo $row['quantity']; ?>)</span>
                                        <a href="borrow_book.php?book_id=<?php echo $row['book_id']; ?>" class="btn" style="padding: 5px 10px; font-size: 0.8em; margin-left: 10px;" onclick="return confirm('Do you want to borrow this book?');">Borrow</a>
                                    <?php else: ?>
                                        <span class="badge" style="background: var(--danger-color); color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.8em;">Out of Stock</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No books found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
