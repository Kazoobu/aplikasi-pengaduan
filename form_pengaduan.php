<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["login"])) {
    header("Location: login.php");
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama_pelapor = $_SESSION["username"];
    $kelas = trim($_POST["kelas"] ?? "");
    $lokasi_kerusakan = trim($_POST["lokasi_kerusakan"] ?? "");
    $jenis_kerusakan = trim($_POST["jenis_kerusakan"] ?? "");
    $deskripsi_kerusakan = trim($_POST["deskripsi_kerusakan"] ?? "");

    if (
        $nama_pelapor === "" || $kelas === "" || $lokasi_kerusakan === "" ||
        $jenis_kerusakan === "" || $deskripsi_kerusakan === ""
    ) {
        $error = "Semua Field wajid diisi.";
    } elseif (!isset($_FILES["foto"]) || $_FILES["foto"]["error"] !== UPLOAD_ERR_OK) {
        $error = "Foto wajib diupload.";
    } else {
        $allowedExtention = ["jpg", "jpeg", "png", "webp"];
        $maxSize = 2 * 1024 * 1024;

        $originalName = $_FILES["foto"]["name"];
        $tmpPath = $_FILES["foto"]["tmp_name"];
        $size = $_FILES["foto"]["size"];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExtention, true)) {
            $error = "Format foto harus JPG, JPEG, PNG, atau WEBP";
        } elseif ($size > $maxSize) {
            $error = "Ukuran foto maksimal 2MB.";
        } else {
            $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . "uploads";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $newFileName = "foto_" . date("Ymd_His") . "_" . bin2hex(random_bytes(4)) . "." . $ext;
            $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $newFileName;

            if (!move_uploaded_file($tmpPath, $targetPath)) {
                $error = "Gagal Menyimpan Foto. ";
            } else {
                $status = "menunggu";
                $stmt = mysqli_prepare($conn, "INSERT INTO pengaduan (nama_pelapor, kelas, lokasi_kerusakan, deskripsi_pengaduan, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sssssss", $nama_pelapor, $kelas, $lokasi_kerusakan, $jenis_kerusakan, $deskripsi_pengaduan, $newFileName, $status);
                $ok = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                if ($ok) {
                    header("Location: data_pengaduan.php");
                    exit;
                } else {
                    @unlink($targetPath);
                    $error = "Gagal menyimpan data pengaduan: " . mysqli_error($conn);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengaduan</title>
</head>

<body>
    <a href="form_pengaduan.php">Form Pengaduan</a>
    <a href="logout.php">logout</a>

    <h1>Form Pengaduan Kerusakan</h1>

    <form method="POST" enctype="multipart/form-data">
        <label for="nama_pelapor">Nama Pelapor</label><br>
        <input type="text" name="nama_pelapor" value="<?= htmlspecialchars($_SESSION["username"]) ?>" readonly><br><br>

        <label for="kelas">Kelas</label><br>
        <input type="text" name="kelas" required><br><br>

        <label for="lokasi_kerusakan">Lokasi Kerusakan</label><br>
        <input type="text" name="lokasi_kerusakan" required><br><br>

        <label for="jenis_kerusakan">Jenis Kerusakan</label><br>
        <input type="text" name="jenis_kerusakan" required><br><br>

        <label for="deskripsi_kerusakan">Deskripsi Kerusakan</label><br>
        <input type="text" name="deskripsi_kerusakan" required><br><br>

        <label for="foto">Foto</label><br>
        <input type="text" name="foto" required><br><br>

        <button type="submit">Kirim Pengaduan</button>

    </form>
    <?php if ($message !== ""): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <?php if ($error !== ""): ?>
        <p><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
</body>

</html>