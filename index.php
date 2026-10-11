<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /sistem_admin/view/index.php");
    exit();
} else {
    if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'penjual')) {
        header("Location: /sistem_admin/view/admin/dashboard.php");
    } else {
        header("Location: /Home%20page/home.php");
    }
    exit();
}
