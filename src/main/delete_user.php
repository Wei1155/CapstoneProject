<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['user_id'])) {
    header("Location: admin_users.php");
    exit();
}

$userId = (int) $_GET['user_id'];

if ($userId == $_SESSION['user_id']) {
    $_SESSION['admin_error'] = "You cannot delete your own admin account.";
    header("Location: admin_users.php");
    exit();
}

$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    $_SESSION['admin_success'] = "User deleted successfully!";
} else {
    $_SESSION['admin_error'] = "Failed to delete user.";
}

$stmt->close();

header("Location: admin_users.php");
exit();
?>