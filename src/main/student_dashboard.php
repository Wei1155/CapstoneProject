<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Student") {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

/* Notification count */
$notifCount = 0;
$notifStmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = ? AND is_read = 0
");

if ($notifStmt) {
    $notifStmt->bind_param("i", $userId);
    $notifStmt->execute();
    $notifResult = $notifStmt->get_result();

    if ($notifRow = $notifResult->fetch_assoc()) {
        $notifCount = $notifRow['total'];
    }

    $notifStmt->close();
}

$firstName = $_SESSION['first_name'];
$lastName = $_SESSION['last_name'];
$fullName = $firstName . " " . $lastName;
$profilePic = !empty($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'default_profile.png';

/* Default values */
$xp = 0;
$level = 1;
$rank = 0;
$badges = 0;

$courses = [];
$activities = [];
$leaderboard = [];

/* Get student progress */
$progressStmt = $conn->prepare("
    SELECT xp, level, rank_position, badges_count
    FROM student_progress
    WHERE user_id = ?
");
$progressStmt->bind_param("i", $userId);
$progressStmt->execute();
$progressResult = $progressStmt->get_result();

if ($progressResult->num_rows > 0) {
    $progressRow = $progressResult->fetch_assoc();
    $xp = $progressRow['xp'];
    $level = $progressRow['level'];
    $rank = $progressRow['rank_position'];
    $badges = $progressRow['badges_count'];
}
$progressStmt->close();

/* Get enrolled courses and progress */
$courseStmt = $conn->prepare("
    SELECT courses.course_title, enrollments.progress
    FROM enrollments
    INNER JOIN courses ON enrollments.course_id = courses.course_id
    WHERE enrollments.user_id = ?
");
$courseStmt->bind_param("i", $userId);
$courseStmt->execute();
$courseResult = $courseStmt->get_result();

while ($row = $courseResult->fetch_assoc()) {
    $courses[] = $row;
}
$courseStmt->close();

/* Get recent activity */
$activityStmt = $conn->prepare("
    SELECT activity_text
    FROM activity_logs
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 5
");
$activityStmt->bind_param("i", $userId);
$activityStmt->execute();
$activityResult = $activityStmt->get_result();

while ($row = $activityResult->fetch_assoc()) {
    $activities[] = $row['activity_text'];
}
$activityStmt->close();

/* Get leaderboard */
$leaderboardStmt = $conn->prepare("
    SELECT users.first_name, users.last_name, student_progress.xp
    FROM student_progress
    INNER JOIN users ON student_progress.user_id = users.user_id
    WHERE users.role = 'Student'
    ORDER BY student_progress.xp DESC
    LIMIT 5
");
$leaderboardStmt->execute();
$leaderboardResult = $leaderboardStmt->get_result();

while ($row = $leaderboardResult->fetch_assoc()) {
    $leaderboard[] = $row;
}
$leaderboardStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../css/student_dashboard.css">
</head>
<body>
    <div class="dashboard-page">

        <aside class="sidebar">
            <div class="sidebar-logo">GamiLearn</div>

            <nav class="sidebar-nav">
                <a href="student_dashboard.php" class="active">Dashboard</a>
                <a href="all_courses.php">Courses</a>
                <a href="#">Quests</a>
                <a href="#">Rewards</a>
                <a href="#">Library</a>
                <a href="notifications.php">Notifications</a>
                <a href="report_issues.php">Report Issue</a>
                <a href="profile.php">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-title">
                    <h1>Student Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($fullName); ?>.</p>
                </div>

                <div class="topbar-actions">
                    <input type="text" placeholder="Search courses, quests..." class="search-input">

                    <a href="notifications.php" class="icon-btn notification-btn">
                        <span class="bell-icon">🔔</span>
                        <?php if ($notifCount > 0) { ?>
                            <span class="notification-badge">
                                <?php echo $notifCount; ?>
                            </span>
                        <?php } ?>
                    </a>

                    <div class="profile-chip">
                        <img src="../media/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                        <span><?php echo htmlspecialchars($fullName); ?></span>
                    </div>
                </div>
            </header>

            <section class="stats-grid">
                <div class="stat-card">
                    <span class="stat-label">XP</span>
                    <h3><?php echo htmlspecialchars($xp); ?></h3>
                    <p>Your total experience points</p>
                </div>

                <div class="stat-card">
                    <span class="stat-label">Level</span>
                    <h3><?php echo htmlspecialchars($level); ?></h3>
                    <p>Your current level</p>
                </div>

                <div class="stat-card">
                    <span class="stat-label">Rank</span>
                    <h3>#<?php echo htmlspecialchars($rank); ?></h3>
                    <p>Your current leaderboard rank</p>
                </div>

                <div class="stat-card">
                    <span class="stat-label">Badges</span>
                    <h3><?php echo htmlspecialchars($badges); ?></h3>
                    <p>Total badges earned</p>
                </div>
            </section>

            <section class="content-grid">
                <div class="card continue-card">
                    <div class="card-header">
                        <h2>Continue Learning</h2>
                        <a href="my_courses.php">View all</a>
                    </div>

                    <div class="course-progress">
                        <?php if (count($courses) > 0) { ?>
                            <?php foreach ($courses as $course) { ?>
                                <div class="course-row">
                                    <div class="course-info">
                                        <h4><?php echo htmlspecialchars($course['course_title']); ?></h4>
                                        <span>Your current progress</span>
                                    </div>
                                    <div class="progress-wrap">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo (int)$course['progress']; ?>%;"></div>
                                        </div>
                                        <small><?php echo (int)$course['progress']; ?>%</small>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <p>No enrolled courses yet.</p>
                        <?php } ?>
                    </div>
                </div>

                <div class="card leaderboard-card">
                    <div class="card-header">
                        <h2>Leaderboard</h2>
                    </div>

                    <ol class="leaderboard-list">
                        <?php if (count($leaderboard) > 0) { ?>
                            <?php foreach ($leaderboard as $leader) { ?>
                                <li>
                                    <span><?php echo htmlspecialchars($leader['first_name'] . " " . $leader['last_name']); ?></span>
                                    <strong><?php echo htmlspecialchars($leader['xp']); ?> XP</strong>
                                </li>
                            <?php } ?>
                        <?php } else { ?>
                            <li>
                                <span>No leaderboard data</span>
                                <strong>0 XP</strong>
                            </li>
                        <?php } ?>
                    </ol>
                </div>
            </section>

            <section class="card activity-card">
                <div class="card-header">
                    <h2>Recent Activity</h2>
                </div>

                <ul class="activity-list">
                    <?php if (count($activities) > 0) { ?>
                        <?php foreach ($activities as $activity) { ?>
                            <li><?php echo htmlspecialchars($activity); ?></li>
                        <?php } ?>
                    <?php } else { ?>
                        <li>No recent activity yet.</li>
                    <?php } ?>
                </ul>
            </section>
        </main>
    </div>
</body>
</html>