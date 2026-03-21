<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Student") {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['course_id'])) {
    header("Location: all_courses.php");
    exit();
}

$userId = $_SESSION['user_id'];
$courseId = (int) $_POST['course_id'];

/* Check if already enrolled */
$checkStmt = $conn->prepare("
    SELECT enrollment_id
    FROM enrollments
    WHERE user_id = ? AND course_id = ?
");
$checkStmt->bind_param("ii", $userId, $courseId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows == 0) {
    $insertStmt = $conn->prepare("
        INSERT INTO enrollments (user_id, course_id, progress)
        VALUES (?, ?, 0)
    ");
    $insertStmt->bind_param("ii", $userId, $courseId);

    if ($insertStmt->execute()) {
        $_SESSION['course_success'] = "Course enrolled successfully!";
    } else {
        $_SESSION['course_error'] = "Failed to enroll in course.";
    }

    $insertStmt->close();
} else {
    $_SESSION['course_error'] = "You are already enrolled in this course.";
}

$checkStmt->close();

header("Location: all_courses.php");
exit();
?>