<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connection.php';
include 'SystemMonitor.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];
$userRole = trim($_SESSION['role']);
$firstName = $_SESSION['first_name'] ?? "User";
$lastName = $_SESSION['last_name'] ?? "";
$fullName = trim($firstName . " " . $lastName);
$profilePic = !empty($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'default_profile.png';

$monitor = new SystemMonitor($conn);

/* Dashboard link */
$dashboardLink = "student_dashboard.php";
if (strtolower($userRole) === "instructor") {
    $dashboardLink = "instructor_dashboard.php";
} elseif (strtolower($userRole) === "admin") {
    $dashboardLink = "admin_dashboard.php";
}

/* Load logs based on role */
if (strtolower($userRole) === "admin") {
    $logs = $monitor->getAllLogs();
} else {
    $logs = $monitor->getUserLogs($userId);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs</title>
    <link rel="stylesheet" href="../css/admin_users.css">
</head>
<body>
    <div class="dashboard-page">
        <aside class="sidebar">
            <div class="sidebar-logo">GamiLearn</div>

            <nav class="sidebar-nav">
                <a href="<?php echo $dashboardLink; ?>">Dashboard</a>

                <?php if (strtolower($userRole) === "student") { ?>
                    <a href="all_courses.php">Courses</a>
                    <a href="quests.php">Quests</a>
                    <a href="rewards.php">Rewards</a>
                    <a href="view_logs.php" class="active">Activity Log</a>
                    <a href="library.php">Library</a>
                    <a href="notifications.php">Notifications</a>
                    <a href="report_issue.php">Report Issue</a>
                <?php } ?>

                <?php if (strtolower($userRole) === "instructor") { ?>
                    <a href="library.php">Library</a>
                    <a href="manage_gamification.php">Gamification</a>
                    <a href="view_logs.php" class="active">Activity Log</a>
                    <a href="report_issue.php">Report Issue</a>
                    <a href="notifications.php">Notifications</a>
                <?php } ?>

                <?php if (strtolower($userRole) === "admin") { ?>
                    <a href="admin_users.php">Users</a>
                    <a href="create_course.php">Courses</a>
                    <a href="generate_report.php">Reports</a>
                    <a href="view_logs.php" class="active">Activity Log</a>
                    <a href="notifications.php">Notifications</a>
                    <a href="system_settings.php">System Settings</a>
                <?php } ?>

                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-title">
                    <h1>Activity Logs</h1>
                    <p>
                        <?php if (strtolower($userRole) === "admin") { ?>
                            Monitor user activity across the system
                        <?php } else { ?>
                            View your recent activity
                        <?php } ?>
                    </p>
                </div>

                <div class="topbar-actions">
                    <div class="profile-chip">
                        <img src="../media/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                        <span><?php echo htmlspecialchars($fullName); ?></span>
                    </div>
                </div>
            </header>

            <section class="admin-section">
                <div class="table-card">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>ID</th>

                                <?php if (strtolower($userRole) === "admin") { ?>
                                    <th>User ID</th>
                                    <th>Username</th>
                                <?php } ?>

                                <th>Activity</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($logs) > 0) { ?>
                                <?php foreach ($logs as $log) { ?>
                                    <tr>
                                        <td><?php echo (int)$log['log_id']; ?></td>

                                        <?php if (strtolower($userRole) === "admin") { ?>
                                            <td><?php echo (int)$log['user_id']; ?></td>
                                            <td><?php echo htmlspecialchars($log['username'] ?? 'Unknown'); ?></td>
                                        <?php } ?>

                                        <td><?php echo htmlspecialchars($log['activity_text']); ?></td>
                                        <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="<?php echo (strtolower($userRole) === "admin") ? 5 : 3; ?>">
                                        No logs found.
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>