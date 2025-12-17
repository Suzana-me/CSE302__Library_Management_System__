<?php
session_start();
require_once '../config.php';

if (isset($_SESSION['member_id'])) {
    header("Location: dashboard.php");
    exit();
}

$login_error = '';
$register_error = '';
$register_success = '';

// Handle Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM members WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if ($password === $row['password']) {
            $_SESSION['member_id'] = $row['member_id'];
            $_SESSION['member_name'] = $row['name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $login_error = "Invalid password.";
        }
    } else {
        $login_error = "Invalid email.";
    }
}

// Handle Registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $sql = "INSERT INTO members (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $email, $password, $phone, $address);

    try {
        if ($stmt->execute()) {
            $register_success = "Registration successful. Please login.";
        } else {
            $register_error = "Registration failed.";
        }
    } catch (Exception $e) {
        $register_error = "Email already exists.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Login/Register</title>
    <link rel="stylesheet" href="../style.css?v=<?php echo time(); ?>">
    <style>
        .auth-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 50px;
        }

        .auth-box {
            flex: 1;
            min-width: 300px;
            max-width: 400px;
        }
    </style>
</head>

<body>
    <div class="container auth-container">
        <!-- Login Form -->
        <div class="card auth-box">
            <h2 style="text-align: center;">Member Login</h2>
            <?php if ($login_error): ?>
                <div class="alert alert-danger"><?php echo $login_error; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" name="login" class="btn" style="width: 100%;">Login</button>
            </form>
        </div>

        <!-- Registration Form -->
        <div class="card auth-box">
            <h2 style="text-align: center;">Register</h2>
            <?php if ($register_success): ?>
                <div class="alert alert-success"><?php echo $register_success; ?></div>
            <?php endif; ?>
            <?php if ($register_error): ?>
                <div class="alert alert-danger"><?php echo $register_error; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea name="address" class="form-control"></textarea>
                </div>
                <button type="submit" name="register" class="btn" style="width: 100%;">Register</button>
            </form>
        </div>
    </div>
</body>

</html>