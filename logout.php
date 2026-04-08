<?php
session_start();
require "koneksi.php";

session_destroy();
session_unset();

header("Location: login.php");
