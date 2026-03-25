<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Instructor") {
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
$username = $_SESSION['username'];
$profilePic = !empty($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'default_profile.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard</title>
    <link rel="stylesheet" href="../css/instructor_dashboard.css">
</head>
<body>
    <div class="dashboard-page">

        <aside class="sidebar">
            <div class="sidebar-logo">GamiLearn</div>

            <nav class="sidebar-nav">
                <a href="instructor_dashboard.php" class="active">Dashboard</a>
                <a href="#">My Courses</a>
                <a href="#">Lesson</a>
                <a href="#">Students</a>
                <a href="#">Quizzes</a>
                <a href="#">Library</a>
                <a href="report_issues.php">Report Issue</a>
                <a href="notifications.php">Notifications</a>
                <a href="profile.php">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-title">
                    <h1>Instructor Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($firstName); ?>.</p>
                </div>

                <div class="topbar-actions">
                    <input type="text" placeholder="Search courses / students" class="search-input">

                    <a href="notifications.php" class="icon-btn notification-btn">
                        <span class="bell-icon">🔔</span>
                        <?php if ($notifCount > 0) { ?>
                            <span class="notification-badge"><?php echo $notifCount; ?></span>
                        <?php } ?>
                    </a>

                    <div class="profile-chip">
                        <img src="../media/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                        <span><?php echo htmlspecialchars($username); ?></span>
                    </div>
                </div>
            </header>

            <section class="stats-grid">
                <div class="stat-card">
                    <span class="stat-label">Courses</span>
                    <h3>3</h3>
                    <p>Active teaching courses</p>
                </div>

                <div class="stat-card">
                    <span class="stat-label">Students</span>
                    <h3>85</h3>
                    <p>Total enrolled students</p>
                </div>

                <div class="stat-card">
                    <span class="stat-label">Pending Reviews</span>
                    <h3>12</h3>
                    <p>Assignments to check</p>
                </div>
            </section>

            <section class="content-grid">
                <div class="card courses-card">
                    <div class="card-header">
                        <h2>My Courses</h2>
                        <a href="#">View all</a>
                    </div>

                    <div class="course-list">
                        <div class="course-item">
                            <h4>Python Basics</h4>
                            <p>35 students enrolled</p>
                        </div>

                        <div class="course-item">
                            <h4>Web Development</h4>
                            <p>28 students enrolled</p>
                        </div>

                        <div class="course-item">
                            <h4>AI Fundamentals</h4>
                            <p>22 students enrolled</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content-grid two-column">
                <div class="card progress-card">
                    <div class="card-header">
                        <h2>Students Progress Chart</h2>
                    </div>

                    <div class="chart-placeholder">
                        <div class="chart-circle"></div>
                        <div class="chart-legend">
                            <p><span class="legend-dot dot1"></span> Excellent Progress</p>
                            <p><span class="legend-dot dot2"></span> Average Progress</p>
                            <p><span class="legend-dot dot3"></span> Needs Improvement</p>
                        </div>
                    </div>
                </div>

                <div class="card review-card">
                    <div class="card-header">
                        <h2>Pending Reviews</h2>
                    </div>

                    <ul class="review-list">
                        <li>Quiz 2 submissions - 5 pending</li>
                        <li>Assignment 1 reports - 4 pending</li>
                        <li>Project proposal reviews - 3 pending</li>
                    </ul>
                </div>
            </section>

            <section class="card activity-card">
                <div class="card-header">
                    <h2>Recent Activity</h2>
                </div>

                <ul class="activity-list">
                    <li>Uploaded new lesson materials for Python Basics</li>
                    <li>Created Quiz 2 for Web Development</li>
                    <li>Reviewed 7 student submissions</li>
                    <li>Updated AI Fundamentals course outline</li>
                </ul>
            </section>
        </main>
    </div>
</body>
</html>