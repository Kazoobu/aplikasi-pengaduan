<?php
session_start();
require "koneksi.php";

if (isset($_SESSION["login"])) {
    header("Location: data_pengaduan.php");
}

if (isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);

        if ($password === $row["password"]) {
            //buat sesi
            $_SESSION["login"] = true;
            $_SESSION["id"] = $row["id"];
            $_SESSION["username"] = $row["username"];
            $_SESSION["level"] = $row["level"];

            header("Location: data_pengaduan.php");
            exit;
        }
    }
    $error = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN</title>
</head>

<body>
    <h1>LOGIN</h1>
    <form action="" method="post">
        <ul>
            <li>
                <label for="username"></label>
                <input type="text" name="username" id="username" autocomplete="off" required>
            </li>
            <li>
                <label for="password"></label>
                <input type="password" name="password" id="password" required>
            </li>
            <li>
                <button type="submit" name="login">Login</button>
            </li>
        </ul>
    </form>

</body>

</html>