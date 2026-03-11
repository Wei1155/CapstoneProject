<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Student") {
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
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../css/student_dashboard.css">
</head>
<body>
    <div class="dashboard-page">

        <aside class="sidebar">
            <div class="sidebar-logo">GamiLearn</div>

            <nav class="sidebar-nav">
                <a href="#" class="active">Dashboard</a>
                <a href="#">Courses</a>
                <a href="#">Quests</a>
                <a href="#">Rewards</a>
                <a href="#">Library</a>
                <a href="#">Logs</a>
                <a href="#">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-title">
                    <h1>Student Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($firstName); ?>.</p>
                </div>

                <div class="topbar-actions">
                    <input type="text" placeholder="Search courses, quests..." class="search-input">
                    <button class="icon-btn">🔔</button>
                    <div class="profile-chip">
                        <img src="../media/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                        <span><?php echo htmlspecialchars($username); ?></span>
                    </div>
                </div>
            </header>

            <section class="stats-grid">
                <div class="stat-card">
                    <span class="stat-label">XP</span>
                    <h3>1,250</h3>
                    <p>+120 this week</p>
                </div>

                <div class="stat-card">
                    <span class="stat-label">Level</span>
                    <h3>5</h3>
                    <p>2 quests to next level</p>
                </div>

                <div class="stat-card">
                    <span class="stat-label">Rank</span>
                    <h3>#12</h3>
                    <p>Top 15%</p>
                </div>

                <div class="stat-card">
                    <span class="stat-label">Badges</span>
                    <h3>8</h3>
                    <p>1 new badge earned</p>
                </div>
            </section>

            <section class="content-grid">
                <div class="card continue-card">
                    <div class="card-header">
                        <h2>Continue Learning</h2>
                        <a href="#">View all</a>
                    </div>

                    <div class="course-progress">
                        <div class="course-row">
                            <div class="course-info">
                                <h4>Course 1</h4>
                                <span>Web Development Basics</span>
                            </div>
                            <div class="progress-wrap">
                                <div class="progress-bar">
                                    <div class="progress-fill fill-75"></div>
                                </div>
                                <small>75%</small>
                            </div>
                        </div>

                        <div class="course-row">
                            <div class="course-info">
                                <h4>Course 2</h4>
                                <span>Database Fundamentals</span>
                            </div>
                            <div class="progress-wrap">
                                <div class="progress-bar">
                                    <div class="progress-fill fill-50"></div>
                                </div>
                                <small>50%</small>
                            </div>
                        </div>

                        <div class="course-row">
                            <div class="course-info">
                                <h4>Course 3</h4>
                                <span>UI/UX Design Principles</span>
                            </div>
                            <div class="progress-wrap">
                                <div class="progress-bar">
                                    <div class="progress-fill fill-30"></div>
                                </div>
                                <small>30%</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card leaderboard-card">
                    <div class="card-header">
                        <h2>Leaderboard</h2>
                    </div>

                    <ol class="leaderboard-list">
                        <li><span>Alex</span><strong>2450 XP</strong></li>
                        <li><span>Sarah</span><strong>2320 XP</strong></li>
                        <li><span>John</span><strong>2210 XP</strong></li>
                        <li><span>You</span><strong>1250 XP</strong></li>
                    </ol>
                </div>
            </section>

            <section class="card activity-card">
                <div class="card-header">
                    <h2>Recent Activity</h2>
                </div>

                <ul class="activity-list">
                    <li>Completed Quiz 1 in Web Development Basics</li>
                    <li>Earned the “Fast Learner” badge</li>
                    <li>Reached Level 5</li>
                    <li>Finished Module 3 of Database Fundamentals</li>
                </ul>
            </section>
        </main>
    </div>
</body>
</html>