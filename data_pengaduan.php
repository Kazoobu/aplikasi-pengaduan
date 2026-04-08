<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["login"])) {
    header("Location: login.php");
}

$isAdmin = $_SESSION["level"] === "Admin";
$info = $error = "";

if ($isAdmin && $_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_status"])) {

    $id = (int) $_POST["id"];
    $status = $_POST["status"];
    $allowed = ["Menunggu", "Diproses", "Selesai"];

    if ($id > 0 && in_array($status, $allowed)) {

        $stmt = mysqli_prepare($conn, "UPDATE pengaduan SET status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "si", $status, $allowed);

        $info = mysqli_stmt_execute($stmt) ? "Status berhasil diperbarui." : "Gagal update status.";

        mysqli_stmt_close($stmt);
    } else {
        $error = "Data update status tidak valid.";
    }
}

$query = "SELECT * FROM pengaduan ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengaduan</title>
</head>

<body>

    <a href="form_pengaduan.php">Form Pengaduan</a>
    <a href="logout.php">logout</a>

    <h1>Data Pengaduan</h1>

    <p>
        Login sebagai :
        <strong>
            <?= htmlspecialchars($_SESSION["username"]) ?>
            (<?= htmlspecialchars($_SESSION["level"]) ?>)
        </strong>
    </p>

    <?php if ($info !== ""): ?>
        <p><?= htmlspecialchars($info) ?></p>
    <?php endif; ?>
    <?php if ($error !== ""): ?>
        <p><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Pelapor</th>
                <th>Kelas</th>
                <th>Lokasi</th>
                <th>Jenis</th>
                <th>Deskripsi</th>
                <th>Foto</th>
                <th>Status</th>
                <?php if ($isAdmin): ?>
                    <th>Aksi</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= (int) $row["id"] ?></td>
                        <td><?= htmlspecialchars($row["nama_pelapor"]) ?></td>
                        <td><?= htmlspecialchars($row["kelas"]) ?></td>
                        <td><?= htmlspecialchars($row["lokasi_kerusakan"]) ?></td>
                        <td><?= htmlspecialchars($row["jenis_kerusakan"]) ?></td>
                        <td><?= htmlspecialchars($row["deskripsi_pengaduan"]) ?></td>
                        <td>
                            <?php
                            $img = "uploads/" . $row["foto"];
                            if ($row["foto"] !== "" && file_exists(__DIR__ . DIRECTORY_SEPARATOR . $img)):
                            ?>
                                <img src="<?= htmlspecialchars($img) ?>" width="100">
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row["status"]) ?></td>

                        <?php if ($isAdmin): ?>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="id" value="<?= (int) $row["id"] ?>">
                                    <select name="status" required>
                                        <option value="Menunggu" <?= $row["status"] === "Menunggu" ? "selected" : "" ?>>Menunggu</option>
                                        <option value="Diproses" <?= $row["status"] === "Diproses" ? "selected" : "" ?>>Diproses</option>
                                        <option value="Selesai" <?= $row["status"] === "Selesai" ? "selected" : "" ?>>Selesai</option>
                                    </select>
                                    <button type="submit" name="update_status">Update</button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?= $isAdmin ? 9 : 8 ?>">Belum ada data pengaduan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>

</html>