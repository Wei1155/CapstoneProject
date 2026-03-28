<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) != "student") {
    header("Location: login.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];
$firstName = $_SESSION['first_name'] ?? "Student";
$lastName = $_SESSION['last_name'] ?? "";
$fullName = trim($firstName . " " . $lastName);
$profilePic = !empty($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'default_profile.png';

/* student progress */
$xp = 0;
$level = 1;
$rank = 0;
$badgesCount = 0;

$progressStmt = $conn->prepare("
    SELECT xp, level, rank_position, badges_count
    FROM student_progress
    WHERE user_id = ?
    LIMIT 1
");

if (!$progressStmt) {
    die("Student progress query error: " . $conn->error);
}

$progressStmt->bind_param("i", $userId);
$progressStmt->execute();
$progressResult = $progressStmt->get_result();
$progressRow = $progressResult->fetch_assoc();
$progressStmt->close();

if ($progressRow) {
    $xp = (int)$progressRow['xp'];
    $level = (int)$progressRow['level'];
    $rank = (int)$progressRow['rank_position'];
    $badgesCount = (int)$progressRow['badges_count'];
}

/* earned badges */
$earnedBadges = [];

$earnedStmt = $conn->prepare("
    SELECT b.badge_id, b.badge_name, b.description, b.xp_required, sb.earned_at
    FROM student_badges sb
    INNER JOIN badges b ON sb.badge_id = b.badge_id
    WHERE sb.user_id = ?
    ORDER BY sb.earned_at DESC
");

if (!$earnedStmt) {
    die("Earned badges query error: " . $conn->error);
}

$earnedStmt->bind_param("i", $userId);
$earnedStmt->execute();
$earnedResult = $earnedStmt->get_result();

while ($row = $earnedResult->fetch_assoc()) {
    $earnedBadges[] = $row;
}
$earnedStmt->close();

/* all badges */
$allBadges = [];
$allBadgesResult = $conn->query("
    SELECT badge_id, badge_name, description, xp_required
    FROM badges
    ORDER BY xp_required ASC
");

if ($allBadgesResult) {
    while ($row = $allBadgesResult->fetch_assoc()) {
        $allBadges[] = $row;
    }
}

/* earned badge ids */
$earnedBadgeIds = [];
foreach ($earnedBadges as $badge) {
    $earnedBadgeIds[] = (int)$badge['badge_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rewards & Badges</title>
    <link rel="stylesheet" href="../css/admin_users.css">
    <link rel="stylesheet" href="../css/rewards.css">
</head>
<body>
    <div class="dashboard-page">
        <aside class="sidebar">
            <div class="sidebar-logo">GamiLearn</div>

            <nav class="sidebar-nav">
                <a href="student_dashboard.php" class="active">Dashboard</a>
                <a href="all_courses.php">Courses</a>
                <a href="quests.php">Quests</a>
                <a href="rewards.php">Rewards</a>
                <a href="view_logs.php">Activity Log</a>
                <a href="library.php">Library</a>
                <a href="notifications.php">Notifications</a>
                <a href="report_issues.php">Report Issue</a>
                <a href="profile.php">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-title">
                    <h1>Rewards & Badges</h1>
                    <p>Track your achievements and collected badges</p>
                </div>

                <div class="topbar-actions">
                    <div class="profile-chip">
                        <img src="../media/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                        <span><?php echo htmlspecialchars($fullName); ?></span>
                    </div>
                </div>
            </header>

            <section class="rewards-stats-grid">
                <div class="reward-stat-box">
                    <p>Total XP</p>
                    <h2><?php echo $xp; ?> XP</h2>
                </div>

                <div class="reward-stat-box">
                    <p>Current Level</p>
                    <h2>Level <?php echo $level; ?></h2>
                </div>

                <div class="reward-stat-box">
                    <p>Total Badges</p>
                    <h2><?php echo $badgesCount; ?></h2>
                </div>

                <div class="reward-stat-box">
                    <p>Leaderboard Rank</p>
                    <h2>#<?php echo $rank; ?></h2>
                </div>
            </section>

            <section class="admin-section">
                <div class="rewards-section-title">
                    <h2>Earned Badges</h2>
                </div>

                <div class="badge-grid">
                    <?php if (count($earnedBadges) > 0) { ?>
                        <?php foreach ($earnedBadges as $badge) { ?>
                            <div class="badge-card earned">
                                <div class="badge-icon">🏆</div>
                                <h3><?php echo htmlspecialchars($badge['badge_name']); ?></h3>
                                <p><?php echo htmlspecialchars($badge['description'] ?? ''); ?></p>
                                <span class="badge-meta">Required XP: <?php echo (int)$badge['xp_required']; ?></span>
                                <span class="badge-earned-date">Earned: <?php echo htmlspecialchars($badge['earned_at']); ?></span>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <p class="empty-text">You have not earned any badges yet.</p>
                    <?php } ?>
                </div>
            </section>

            <section class="admin-section" style="margin-top: 24px;">
                <div class="rewards-section-title">
                    <h2>All Available Badges</h2>
                </div>

                <div class="badge-grid">
                    <?php if (count($allBadges) > 0) { ?>
                        <?php foreach ($allBadges as $badge) { ?>
                            <?php $isEarned = in_array((int)$badge['badge_id'], $earnedBadgeIds); ?>

                            <div class="badge-card <?php echo $isEarned ? 'earned' : 'locked'; ?>">
                                <div class="badge-icon"><?php echo $isEarned ? '🏆' : '🔒'; ?></div>
                                <h3><?php echo htmlspecialchars($badge['badge_name']); ?></h3>
                                <p><?php echo htmlspecialchars($badge['description'] ?? ''); ?></p>
                                <span class="badge-meta">Required XP: <?php echo (int)$badge['xp_required']; ?></span>

                                <?php if ($isEarned) { ?>
                                    <span class="badge-status earned-status">Unlocked</span>
                                <?php } else { ?>
                                    <span class="badge-status locked-status">Locked</span>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <p class="empty-text">No badges available yet.</p>
                    <?php } ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>