<?php
session_start();
require 'koneksi.php';

if (isset($_SESSION["login"])) {
    header("Location: data_pengaduan");
}

if (isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);

        if ($password === $row["password"]) {
            // Login berhasil
            $_SESSION["login"] = true;
            $_SESSION["level"] = $row["level"];
            $_SESSION["id"] = $row["id"];
            $_SESSION['username'] = $row['username'];

            header("Location: data_pengaduan.php");
            exit;
        }
    }

    $error = true;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card {
            width: 100%;
            max-width: 380px;
            background: #ffffff;
            padding: 28px;
            border-radius: 14px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
        }

        h1 {
            margin: 0 0 8px;
            font-size: 28px;
            text-align: center;
        }

        .subtitle {
            margin: 0 0 24px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }

        .alert {
            margin-bottom: 16px;
            padding: 12px 14px;
            border-radius: 10px;
            background: #fee2e2;
            color: #b91c1c;
            font-size: 14px;
        }

        form {
            display: grid;
            gap: 16px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
        }

        input:focus {
            border-color: #2563eb;
        }

        button {
            border: none;
            border-radius: 10px;
            padding: 12px;
            background: #2563eb;
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>Login</h1>
        <p class="subtitle">Masuk untuk mengelola pengaduan</p>

        <?php if (isset($error)): ?>
            <div class="alert">Username atau password salah.</div>
        <?php endif; ?>

        <form action="" method="post">
            <div>
                <label for="username">Username</label>
                <input type="text" name="username" id="username" autocomplete="off" required>
            </div>

            <div>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit" name="login">Login</button>
        </form>
    </div>
</body>

</html>