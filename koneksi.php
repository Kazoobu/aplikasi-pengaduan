<?php
$conn = mysqli_connect("localhost", "root", "", "aplikasi_pengaduan");

if (!$conn) {
    die("koneksi database gagal:" . mysqli_connect_error());
}
