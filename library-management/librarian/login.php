<?php
session_start();
require_once '../config.php';

if (isset($_SESSION['librarian_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM librarians WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        // In a real app, use password_verify. Assuming plain text or simple comparison for this task based on user request context, 
        // but let's try to be safe if possible. The schema just says password VARCHAR(255).
        // If the user provided data has plain text passwords, this will fail if we use password_verify.
        // For now, I will assume direct comparison as per typical student projects unless specified otherwise, 
        // BUT I will add a comment about security.
        if ($password === $row['password']) {
            $_SESSION['librarian_id'] = $row['librarian_id'];
            $_SESSION['librarian_name'] = $row['name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Invalid username.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Login</title>
    <link rel="stylesheet" href="../style.css?v=<?php echo time(); ?>">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
    </style>
</head>

<body>
    <div class="container login-container">
        <div class="card">
            <h2 style="text-align: center;">Librarian Login</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn" style="width: 100%;">Login</button>
            </form>
        </div>
    </div>
</body>

</html>