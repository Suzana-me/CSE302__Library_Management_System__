<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

// Handle Add Librarian
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_librarian'])) {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password']; // Plain text as per context

    $sql = "INSERT INTO librarians (name, username, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $username, $password);
    if ($stmt->execute()) {
        $message = "Librarian added successfully.";
    } else {
        $error = "Error adding librarian: " . $conn->error;
    }
}

// Handle Delete Librarian
if (isset($_GET['delete'])) {
    $lib_id = $_GET['delete'];
    $sql = "DELETE FROM librarians WHERE librarian_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $lib_id);
    if ($stmt->execute()) {
        $message = "Librarian deleted successfully.";
    } else {
        $error = "Error deleting librarian.";
    }
}

$librarians = $conn->query("SELECT * FROM librarians");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Librarians</title>
    <link rel="stylesheet" href="../style.css?v=<?php echo time(); ?>">
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">Admin Panel</div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="departments.php">Departments</a>
                <a href="librarians.php" class="active">Librarians</a>
                <a href="members.php">Members</a>
                <a href="logout.php" style="color: var(--danger-color);">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>Manage Librarians</h2>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <h3>Add Librarian</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <button type="submit" name="add_librarian" class="btn">Add Librarian</button>
            </form>
        </div>

        <div class="card">
            <h3>Librarian List</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $librarians->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['librarian_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td>
                                <a href="?delete=<?php echo $row['librarian_id']; ?>" class="btn btn-danger"
                                    onclick="return confirm('Are you sure?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>