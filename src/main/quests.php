<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connection.php';
include 'GamificationSystem.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) != "student") {
    die("Access denied");
}

$userId = (int)$_SESSION['user_id'];
$firstName = $_SESSION['first_name'] ?? "Student";
$lastName = $_SESSION['last_name'] ?? "";
$fullName = trim($firstName . " " . $lastName);
$profilePic = !empty($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'default_profile.png';

$gamification = new GamificationSystem($conn);
$studentData = $gamification->get_student_data($userId);

$message = "";
$messageType = "";

/* Claim quest */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['claim_quest'])) {
    $questId = isset($_POST['quest_id']) ? (int)$_POST['quest_id'] : 0;

    if ($questId <= 0) {
        $message = "Invalid quest selected.";
        $messageType = "error";
    } else {
        $resultMessage = $gamification->complete_task($userId, $questId);

        if (stripos($resultMessage, "Success") !== false) {
            $message = $resultMessage;
            $messageType = "success";
        } else {
            $message = $resultMessage;
            $messageType = "error";
        }

        $studentData = $gamification->get_student_data($userId);
    }
}

/* Load quests */
$quests = [];

$stmt = $conn->prepare("
    SELECT 
        q.quest_id,
        q.title,
        q.description,
        q.xp_reward,
        q.badge_reward,
        q.status,
        sq.progress_status,
        sq.completed_at
    FROM quests q
    LEFT JOIN student_quests sq
        ON q.quest_id = sq.quest_id
        AND sq.user_id = ?
    ORDER BY q.created_at DESC
");

if (!$stmt) {
    die("Quest list prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $quests[] = $row;
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quests & Challenges</title>
    <link rel="stylesheet" href="../css/admin_users.css">
    <link rel="stylesheet" href="../css/quests.css">
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
                    <h1>Quests & Challenges</h1>
                    <p>Complete tasks to earn XP and badges</p>
                </div>

                <div class="topbar-actions">
                    <div class="profile-chip">
                        <img src="../media/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                        <span><?php echo htmlspecialchars($fullName); ?></span>
                    </div>
                </div>
            </header>

            <section class="quests-stats-grid">

                <div class="quest-stat-box">
                    <p>Total earned experience points</p>
                    <h2><?php echo (int)$studentData['xp']; ?> XP</h2>
                </div>

                <div class="quest-stat-box">
                    <p>Your current level</p>
                    <h2>Level <?php echo (int)$studentData['level']; ?></h2>
                </div>

                <div class="quest-stat-box">
                    <p>Total badges earned</p>
                    <h2><?php echo (int)$studentData['badges_count']; ?></h2>
                </div>

            </section>

            <section class="admin-section">
                <?php if ($message !== ""): ?>
                    <div class="<?php echo ($messageType === 'success') ? 'success-msg' : 'error-msg'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <div class="quest-list">
                    <?php if (count($quests) > 0): ?>
                        <?php foreach ($quests as $quest): ?>
                            <div class="quest-card">
                                <div class="quest-info">
                                    <h3><?php echo htmlspecialchars($quest['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($quest['description']); ?></p>

                                    <div class="quest-meta">
                                        <span class="quest-tag">XP Reward: <?php echo (int)$quest['xp_reward']; ?></span>

                                        <?php if (!empty($quest['badge_reward'])): ?>
                                            <span class="quest-tag">Badge: <?php echo htmlspecialchars($quest['badge_reward']); ?></span>
                                        <?php endif; ?>

                                        <span class="quest-tag">Status: <?php echo htmlspecialchars($quest['status']); ?></span>
                                    </div>
                                </div>

                                <div class="quest-action">
                                    <?php if (isset($quest['progress_status']) && $quest['progress_status'] === 'Completed'): ?>
                                        <span class="completed-badge">Completed</span>
                                    <?php elseif ($quest['status'] === 'Inactive'): ?>
                                        <span class="locked-badge">Inactive</span>
                                    <?php else: ?>
                                        <form method="POST">
                                            <input type="hidden" name="quest_id" value="<?php echo (int)$quest['quest_id']; ?>">
                                            <button type="submit" name="claim_quest" class="save-btn">Claim Quest</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No quests available yet.</p>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>