<?php
session_start();

$admin_password = "password";

if (isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['authenticated'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = "Incorrect password!";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <style>
        body {
            font-family: system-ui;
            background: #0b1220;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        form {
            background: #111a2e;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #1f2a44;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            background: #0b1220;
            border: 1px solid #1f2a44;
            color: white;
            border-radius: 6px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #2563eb;
            border: none;
            color: white;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <form method="POST">
        <h2>Admin Login</h2>
        <?php if (isset($error))
            echo "<p style='color:red;'>$error</p>"; ?>
        <input type="password" name="password" placeholder="Enter Password" required autofocus>
        <button type="submit">Login</button>
    </form>
</body>

</html>