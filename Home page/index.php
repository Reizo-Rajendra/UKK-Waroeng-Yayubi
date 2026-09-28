<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../sistem_admin/view/index.php");
    exit();
} else {
    header("Location: home.php");
    exit();
}
