<?php

// Start the administrator session
session_start();

require_once '../config/database.php';

$error = '';

// Redirect administrators who are already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {

        $error = 'Please enter your username and password.';

    } else {

        // Find administrator by username
        $stmt = $pdo->prepare(
            "SELECT id, username, password
             FROM admins
             WHERE username = ?"
        );

        $stmt->execute([$username]);

        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify the hashed password
        if ($admin && password_verify($password, $admin['password'])) {

            // Generate a new session ID after login
            session_regenerate_id(true);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            header('Location: dashboard.php');
            exit;

        } else {
            $error = 'Invalid username or password.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login - Constructify</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f5f6f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: white;
            padding: 35px;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
        }

        .login-title {
            color: #14283d;
            font-weight: 700;
            margin-bottom: 25px;
        }

        .login-btn {
            background: #ed6a2a;
            color: white;
            border: none;
            padding: 12px;
            font-weight: 600;
        }

        .login-btn:hover {
            background: #d95820;
            color: white;
        }
    </style>
</head>

<body>

    <div class="login-card">

        <h2 class="login-title text-center">
            Constructify Admin
        </h2>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label for="username" class="form-label">
                    Username
                </label>

                <input
                    type="text"
                    class="form-control"
                    id="username"
                    name="username"
                    required
                    autocomplete="username"
                >
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">
                    Password
                </label>

                <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="login-btn w-100 rounded">
                Login
            </button>

        </form>

    </div>

</body>
</html>
