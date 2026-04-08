<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama_pelapor = $_SESSION["username"];
    $kelas = trim($_POST["kelas"] ?? "");
    $lokasi_kerusakan = trim($_POST["lokasi_kerusakan"] ?? "");
    $jenis_kerusakan = trim($_POST["jenis_kerusakan"] ?? "");
    $deskripsi_pengaduan = trim($_POST["deskripsi_pengaduan"] ?? "");

    if (
        $nama_pelapor === "" || $kelas === "" || $lokasi_kerusakan === "" ||
        $jenis_kerusakan === "" || $deskripsi_pengaduan === ""
    ) {
        $error = "Semua field wajib diisi.";
    } elseif (!isset($_FILES["foto"]) || $_FILES["foto"]["error"] !== UPLOAD_ERR_OK) {
        $error = "Foto wajib diupload.";
    } else {
        $allowedExtensions = ["jpg", "jpeg", "png", "webp"];
        $maxSize = 2 * 1024 * 1024;

        $originalName = $_FILES["foto"]["name"];
        $tmpPath = $_FILES["foto"]["tmp_name"];
        $size = $_FILES["foto"]["size"];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExtensions, true)) {
            $error = "Format foto harus JPG, JPEG, PNG, atau WEBP.";
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
                $error = "Gagal menyimpan foto.";
            } else {
                $status = "Menunggu";
                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO pengaduan (nama_pelapor, kelas, lokasi_kerusakan, jenis_kerusakan, deskripsi_pengaduan, foto, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param(
                    $stmt,
                    "sssssss",
                    $nama_pelapor,
                    $kelas,
                    $lokasi_kerusakan,
                    $jenis_kerusakan,
                    $deskripsi_pengaduan,
                    $newFileName,
                    $status
                );
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
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengaduan</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }

        .container {
            max-width: 760px;
            margin: 0 auto;
            padding: 32px 20px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 10px 14px;
            border-radius: 10px;
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
            font-size: 14px;
        }

        .btn.secondary {
            background: #111827;
        }

        .card {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
            padding: 24px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 30px;
        }

        .subtitle {
            margin: 0 0 24px;
            color: #6b7280;
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

        .field label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 600;
        }

        .field input,
        .field textarea {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            background: #ffffff;
        }

        .field input:focus,
        .field textarea:focus {
            border-color: #2563eb;
        }

        .field textarea {
            min-height: 120px;
            resize: vertical;
        }

        .field input[readonly] {
            background: #f9fafb;
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

        @media (max-width: 768px) {
            .container {
                padding: 24px 16px;
            }

            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="topbar">
            <div class="actions">
                <a href="data_pengaduan.php" class="btn">Lihat Data</a>
                <a href="logout.php" class="btn secondary">Logout</a>
            </div>
        </div>

        <div class="card">
            <h1>Form Pengaduan Kerusakan</h1>
            <p class="subtitle">Isi data dengan lengkap agar pengaduan mudah diproses.</p>

            <?php if ($message !== ""): ?>
                <div class="alert" style="background:#dcfce7;color:#166534;"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if ($error !== ""): ?>
                <div class="alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="field">
                    <label for="nama_pelapor">Nama Pelapor</label>
                    <input type="text" id="nama_pelapor" name="nama_pelapor" value="<?= htmlspecialchars($_SESSION["username"]) ?>" readonly>
                </div>

                <div class="field">
                    <label for="kelas">Kelas</label>
                    <input type="text" id="kelas" name="kelas" required>
                </div>

                <div class="field">
                    <label for="lokasi_kerusakan">Lokasi Kerusakan</label>
                    <input type="text" id="lokasi_kerusakan" name="lokasi_kerusakan" required>
                </div>

                <div class="field">
                    <label for="jenis_kerusakan">Jenis Kerusakan</label>
                    <input type="text" id="jenis_kerusakan" name="jenis_kerusakan" required>
                </div>

                <div class="field">
                    <label for="deskripsi_pengaduan">Deskripsi Pengaduan</label>
                    <textarea id="deskripsi_pengaduan" name="deskripsi_pengaduan" required></textarea>
                </div>

                <div class="field">
                    <label for="foto">Foto</label>
                    <input type="file" id="foto" name="foto" accept=".jpg,.jpeg,.png,.webp" required>
                </div>

                <button type="submit">Kirim Pengaduan</button>
            </form>
        </div>
    </div>
</body>

</html>