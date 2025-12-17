<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>East West University library-management system</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>

<body>
    <div class="landing-container">
        <div class="landing-header">
            <h1>East West University</h1>
            <h2>Library Management System</h2>
            <p style="font-size: 1.2em; max-width: 600px; margin: 0 auto; opacity: 0.9;">
                Welcome to our digital library. Please select your role to continue.
            </p>
        </div>

        <div class="roles-container">
            <div class="role-card">
                <div class="role-icon">👤</div>
                <div class="role-title">Member</div>
                <p>Login to view your books, history, and add new books.</p>
                <a href="member/login.php" class="btn-role">Member Login</a>
            </div>

            <div class="role-card">
                <div class="role-icon">📚</div>
                <div class="role-title">Librarian</div>
                <p>Manage books, issues, returns, and fines.</p>
                <a href="librarian/login.php" class="btn-role">Librarian Login</a>
            </div>

            <div class="role-card">
                <div class="role-icon">⚙️</div>
                <div class="role-title">Admin</div>
                <p>Manage departments, librarians, members, and books.</p>
                <a href="admin/login.php" class="btn-role">Admin Login</a>
            </div>
        </div>
    </div>
</body>

</html>