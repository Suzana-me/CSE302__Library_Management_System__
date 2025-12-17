<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

// Handle Delete Member
if (isset($_GET['delete'])) {
    $member_id = $_GET['delete'];
    $sql = "DELETE FROM members WHERE member_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $member_id);
    if ($stmt->execute()) {
        $message = "Member deleted successfully.";
    } else {
        $error = "Error deleting member. They might have issued books.";
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $password = $_POST['password']; // In real app, hash this!
    $member_id = $_POST['member_id'] ?? '';

    if ($member_id) {
        // Update
        $sql = "UPDATE members SET name=?, email=?, phone=?, address=? WHERE member_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $email, $phone, $address, $member_id);
        
        // Only update password if provided
        if (!empty($password)) {
             $conn->query("UPDATE members SET password='$password' WHERE member_id=$member_id");
        }

        if ($stmt->execute()) {
            $message = "Member updated successfully.";
        } else {
            $error = "Error updating member: " . $conn->error;
        }
    } else {
        // Insert
        // Check duplicate email
        $check = $conn->query("SELECT * FROM members WHERE email='$email'");
        if ($check->num_rows > 0) {
            $error = "Email already exists.";
        } else {
            $sql = "INSERT INTO members (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $name, $email, $password, $phone, $address);
            if ($stmt->execute()) {
                $message = "Member added successfully.";
            } else {
                $error = "Error adding member: " . $conn->error;
            }
        }
    }
}

// Get Member for Edit
$edit_member = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM members WHERE member_id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_member = $stmt->get_result()->fetch_assoc();
}

$members = $conn->query("SELECT * FROM members ORDER BY member_id DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members</title>
    <link rel="stylesheet" href="../style.css?v=<?php echo time(); ?>">
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">Admin Panel</div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="books.php">Books</a>
                <a href="departments.php">Departments</a>
                <a href="librarians.php">Librarians</a>
                <a href="members.php" class="active">Members</a>
                <a href="logout.php" style="color: var(--danger-color);">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>Manage Members</h2>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <h3><?php echo $edit_member ? 'Edit Member' : 'Add New Member'; ?></h3>
            <form method="POST" action="members.php">
                <?php if ($edit_member): ?>
                    <input type="hidden" name="member_id" value="<?php echo $edit_member['member_id']; ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" required class="form-control" value="<?php echo $edit_member['name'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required class="form-control" value="<?php echo $edit_member['email'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="text" name="password" class="form-control" placeholder="<?php echo $edit_member ? 'Leave blank to keep current' : 'Enter password'; ?>" <?php echo $edit_member ? '' : 'required'; ?>>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo $edit_member['phone'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" class="form-control"><?php echo $edit_member['address'] ?? ''; ?></textarea>
                </div>
                <button type="submit" class="btn"><?php echo $edit_member ? 'Update Member' : 'Add Member'; ?></button>
                <?php if ($edit_member): ?>
                    <a href="members.php" class="btn btn-warning">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <h3>Member List</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Joined Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($members->num_rows > 0): ?>
                        <?php while ($row = $members->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['member_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo $row['joined_date']; ?></td>
                                <td>
                                    <a href="?edit=<?php echo $row['member_id']; ?>" class="btn btn-warning" style="padding: 5px 10px; font-size: 0.8em;">Edit</a>
                                    <a href="?delete=<?php echo $row['member_id']; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8em;" onclick="return confirm('Are you sure?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No members found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>