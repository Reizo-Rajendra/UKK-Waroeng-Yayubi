<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: Website Yayubi/sistem_admin/view/index.php");
    exit();
} else {
    if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'penjual')) {
        header("Location: Website Yayubi/sistem_admin/view/admin/dashboard.php");
    } else {
        header("Location: Website Yayubi/Home page/home.php");
    }
    exit();
}
