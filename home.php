<?php
include "db_connection.php";
session_start();
$user_id = $_SESSION['user_id'];

$sql = "SELECT role FROM users WHERE user_id = $user_id";
$userrole_result = $conn->query($sql);
$user = $userrole_result->fetch_assoc();
$role = $user['role'];
?>

<a href="progress_report.php">Progress</a><br>
<a href="reward_redemption.php">Badges</a><br>
<?php if ($role == "Instructor"): ?>
<a href="performance_report.php">Performance</a>
<?php endif; ?>

<!-- ADD TO MAIN PAGE -->