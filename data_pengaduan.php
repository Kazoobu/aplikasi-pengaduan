<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}

$isAdmin = $_SESSION["level"] === "Admin";
$info = $error = "";

if ($isAdmin && $_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_status"])) {

    $id = (int) $_POST["id"];
    $status = $_POST["status"];
    $allowed = ["Menunggu", "Diproses", "Selesai"];

    if ($id > 0 && in_array($status, $allowed)) {

        $stmt = mysqli_prepare($conn, "UPDATE pengaduan SET status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "si", $status, $id);

        $info = mysqli_stmt_execute($stmt)
            ? "Status berhasil diperbarui."
            : "Gagal update status.";

        mysqli_stmt_close($stmt);
    } else {
        $error = "Data update status tidak valid.";
    }
}

if ($isAdmin && $_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_pengaduan"])) {

    $id = (int) $_POST["id"];

    if ($id > 0) {
        $foto = "";
        $stmtFoto = mysqli_prepare($conn, "SELECT foto FROM pengaduan WHERE id=?");
        mysqli_stmt_bind_param($stmtFoto, "i", $id);
        mysqli_stmt_execute($stmtFoto);
        mysqli_stmt_bind_result($stmtFoto, $foto);
        mysqli_stmt_fetch($stmtFoto);
        mysqli_stmt_close($stmtFoto);

        $stmtDelete = mysqli_prepare($conn, "DELETE FROM pengaduan WHERE id=?");
        mysqli_stmt_bind_param($stmtDelete, "i", $id);

        if (mysqli_stmt_execute($stmtDelete)) {
            if ($foto !== "") {
                $pathFoto = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . $foto;
                if (file_exists($pathFoto)) {
                    unlink($pathFoto);
                }
            }
            $info = "Data pengaduan berhasil dihapus.";
        } else {
            $error = "Data pengaduan gagal dihapus.";
        }

        mysqli_stmt_close($stmtDelete);
    } else {
        $error = "Data hapus tidak valid.";
    }
}

$query = "SELECT * FROM pengaduan ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengaduan</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 30px;
        }

        .subtitle {
            margin: 0;
            color: #6b7280;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn,
        button {
            display: inline-block;
            padding: 10px 14px;
            border: none;
            border-radius: 10px;
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }

        .btn.secondary {
            background: #111827;
        }

        .card {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
            padding: 20px;
        }

        .alert {
            margin-bottom: 16px;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 14px;
        }

        .alert.info {
            background: #dcfce7;
            color: #166534;
        }

        .alert.error {
            background: #fee2e2;
            color: #b91c1c;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
            font-size: 14px;
        }

        th {
            background: #eff6ff;
            color: #1d4ed8;
        }

        tr:hover td {
            background: #f9fafb;
        }

        img {
            border-radius: 8px;
            object-fit: cover;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            background: #e5e7eb;
            font-size: 13px;
            font-weight: 600;
        }

        .form-inline {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .action-stack {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: flex-start;
        }

        select {
            padding: 9px 10px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            background: #ffffff;
        }

        .btn-delete {
            background: #dc2626;
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
        <div class="header">
            <div>
                <h1>Data Pengaduan</h1>
                <p class="subtitle">
                    Login sebagai
                    <strong><?= htmlspecialchars($_SESSION["username"]) ?> (<?= htmlspecialchars($_SESSION["level"]) ?>)</strong>
                </p>
            </div>

            <div class="actions">
                <a href="form_pengaduan.php" class="btn">Form Pengaduan</a>
                <a href="logout.php" class="btn secondary">Logout</a>
            </div>
        </div>

        <div class="card">
            <?php if ($info !== ""): ?>
                <div class="alert info"><?= htmlspecialchars($info) ?></div>
            <?php endif; ?>

            <?php if ($error !== ""): ?>
                <div class="alert error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="table-wrap">
                <table>
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
                                            <img src="<?= htmlspecialchars($img) ?>" width="100" alt="Foto pengaduan">
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="status"><?= htmlspecialchars($row["status"]) ?></span></td>

                                    <?php if ($isAdmin): ?>
                                        <td>
                                            <div class="action-stack">
                                                <form method="POST" class="form-inline">
                                                    <input type="hidden" name="id" value="<?= (int) $row["id"] ?>">
                                                    <select name="status" required>
                                                        <option value="Menunggu" <?= $row["status"] === "Menunggu" ? "selected" : "" ?>>Menunggu</option>
                                                        <option value="Diproses" <?= $row["status"] === "Diproses" ? "selected" : "" ?>>Diproses</option>
                                                        <option value="Selesai" <?= $row["status"] === "Selesai" ? "selected" : "" ?>>Selesai</option>
                                                    </select>
                                                    <button type="submit" name="update_status">Update</button>
                                                </form>

                                                <form method="POST" onsubmit="return confirm('Yakin ingin menghapus data pengaduan ini?');">
                                                    <input type="hidden" name="id" value="<?= (int) $row["id"] ?>">
                                                    <button type="submit" name="delete_pengaduan" class="btn-delete">Hapus</button>
                                                </form>
                                            </div>
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
            </div>
        </div>
    </div>
</body>

</html>
