<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {
    header("Location: login.php");
    exit();
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
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css">
</head>
<body>
    <div class="dashboard-page">

        <aside class="sidebar">
            <div class="sidebar-logo">GamiLearn</div>

            <nav class="sidebar-nav">
                <a href="admin_dashboard.php" class="active">Dashboard</a>
                <a href="admin_users.php">Users</a>
                <a href="create_course.php">Courses</a>
                <a href="generate_report.php">Reports</a>
                <a href="view_logs.php">Logs</a>
                <a href="profile.php">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-title">
                    <h1>Admin Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($firstName); ?>.</p>
                </div>

                <div class="topbar-actions">
                    <input type="text" placeholder="Search users, courses, reports..." class="search-input">
                    <button class="icon-btn">🔔</button>
                    <div class="profile-chip">
                        <img src="../media/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                        <span><?php echo htmlspecialchars($username); ?></span>
                    </div>
                </div>
            </header>

            <section class="stats-grid">
                <div class="stat-card">
                    <span class="stat-label">Total Users</span>
                    <h3>520</h3>
                    <p>All registered users</p>
                </div>

                <div class="stat-card">
                    <span class="stat-label">Students</span>
                    <h3>460</h3>
                    <p>Active student accounts</p>
                </div>

                <div class="stat-card">
                    <span class="stat-label">Instructors</span>
                    <h3>60</h3>
                    <p>Teaching staff accounts</p>
                </div>

                <div class="stat-card">
                    <span class="stat-label">Total Courses</span>
                    <h3>35</h3>
                    <p>Published courses</p>
                </div>
            </section>

            <section class="content-grid">
                <div class="card analytics-card">
                    <div class="card-header">
                        <h2>System Analytics</h2>
                    </div>

                    <div class="analytics-list">
                        <div class="analytics-item">
                            <span>Total Enrollments</span>
                            <strong>1,200</strong>
                        </div>

                        <div class="analytics-item">
                            <span>Course Completion Rate</span>
                            <strong>64%</strong>
                        </div>

                        <div class="analytics-item">
                            <span>Active Users Today</span>
                            <strong>89</strong>
                        </div>
                    </div>
                </div>

                <div class="card activity-card">
                    <div class="card-header">
                        <h2>Recent User Activity</h2>
                    </div>

                    <ul class="activity-list">
                        <li>New student registered</li>
                        <li>Instructor created a new course</li>
                        <li>Admin updated course category</li>
                        <li>Student completed final quiz</li>
                    </ul>
                </div>
            </section>

            <section class="content-grid two-column">
                <div class="card quick-actions-card">
                    <div class="card-header">
                        <h2>Quick Admin Action</h2>
                    </div>

                    <div class="quick-actions">
                        <a href="add_user.php" class="action-btn">Add User</a>
                        <a href="create_course.php" class="action-btn">Create Course</a>
                        <a href="generate_report.php" class="action-btn">Generate Report</a>
                        <a href="view_logs.php" class="action-btn">View Logs</a>
                    </div>
                </div>

                <div class="card course-overview-card">
                    <div class="card-header">
                        <h2>Course Overview</h2>
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
        </main>
    </div>
</body>
</html>