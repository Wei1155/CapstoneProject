<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

if (!isset($_FILES['profile_picture'])) {
    $_SESSION['upload_error'] = "No file selected.";
    header("Location: profile.php");
    exit();
}

if ($_FILES['profile_picture']['error'] !== 0) {
    $_SESSION['upload_error'] = "Upload failed. Error code: " . $_FILES['profile_picture']['error'];
    header("Location: profile.php");
    exit();
}

$fileTmpPath = $_FILES['profile_picture']['tmp_name'];
$fileName = $_FILES['profile_picture']['name'];
$fileSize = $_FILES['profile_picture']['size'];

$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (!in_array($fileExtension, $allowedExtensions)) {
    $_SESSION['upload_error'] = "Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.";
    header("Location: profile.php");
    exit();
}

if ($fileSize > 5 * 1024 * 1024) {
    $_SESSION['upload_error'] = "File size must be less than 5MB.";
    header("Location: profile.php");
    exit();
}

$newFileName = "profile_" . $userId . "_" . time() . "." . $fileExtension;
$uploadDirectory = __DIR__ . '/../media/';
$destination = $uploadDirectory . $newFileName;

if (!is_dir($uploadDirectory)) {
    if (!mkdir($uploadDirectory, 0777, true)) {
        $_SESSION['upload_error'] = "Failed to create upload folder.";
        header("Location: profile.php");
        exit();
    }
}

if (!is_writable($uploadDirectory)) {
    $_SESSION['upload_error'] = "Upload folder is not writable.";
    header("Location: profile.php");
    exit();
}

if (move_uploaded_file($fileTmpPath, $destination)) {
    $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
    $stmt->bind_param("si", $newFileName, $userId);

    if ($stmt->execute()) {
        $_SESSION['profile_picture'] = $newFileName;
        $_SESSION['upload_success'] = "Profile picture updated successfully!";
    } else {
        $_SESSION['upload_error'] = "Database update failed.";
    }

    $stmt->close();
} else {
    $_SESSION['upload_error'] = "Failed to save uploaded file.";
}

header("Location: profile.php");
exit();
?>