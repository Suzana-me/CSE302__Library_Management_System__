<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

// Handle Add Department
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_dept'])) {
    $dept_name = $_POST['dept_name'];
    $sql = "INSERT INTO departments (dept_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $dept_name);
    if ($stmt->execute()) {
        $message = "Department added successfully.";
    } else {
        $error = "Error adding department: " . $conn->error;
    }
}

// Handle Delete Department
if (isset($_GET['delete'])) {
    $dept_id = $_GET['delete'];
    $sql = "DELETE FROM departments WHERE dept_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $dept_id);
    if ($stmt->execute()) {
        $message = "Department deleted successfully.";
    } else {
        $error = "Error deleting department. It might be linked to books.";
    }
}

$departments = $conn->query("SELECT * FROM departments");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments</title>
    <link rel="stylesheet" href="../style.css?v=<?php echo time(); ?>">
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">Admin Panel</div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="departments.php" class="active">Departments</a>
                <a href="librarians.php">Librarians</a>
                <a href="members.php">Members</a>
                <a href="logout.php" style="color: var(--danger-color);">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>Manage Departments</h2>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <h3>Add Department</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="dept_name">Department Name</label>
                    <input type="text" name="dept_name" id="dept_name" class="form-control" required>
                </div>
                <button type="submit" name="add_dept" class="btn">Add Department</button>
            </form>
        </div>

        <div class="card">
            <h3>Department List</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $departments->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['dept_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['dept_name']); ?></td>
                            <td>
                                <a href="?delete=<?php echo $row['dept_id']; ?>" class="btn btn-danger"
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