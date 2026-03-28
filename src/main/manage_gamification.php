<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connection.php';

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    (
        strtolower(trim($_SESSION['role'])) != "instructor" &&
        strtolower(trim($_SESSION['role'])) != "admin"
    )
) {
    header("Location: login.php");
    exit();
}

$userRole = strtolower(trim($_SESSION['role']));
$username = $_SESSION['username'] ?? "User";
$profilePic = !empty($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'default_profile.png';

$message = "";
$messageType = "";

/* =========================
   QUESTS
========================= */
$editQuestMode = false;
$editQuest = null;

/* Add quest */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_quest'])) {
    $title = trim($_POST['title'] ?? "");
    $description = trim($_POST['description'] ?? "");
    $xpReward = (int)($_POST['xp_reward'] ?? 0);
    $badgeReward = trim($_POST['badge_reward'] ?? "");
    $status = trim($_POST['quest_status'] ?? "Active");

    if ($title === "" || $description === "") {
        $message = "Please fill in all quest fields.";
        $messageType = "error";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO quests (title, description, xp_reward, badge_reward, status, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        if (!$stmt) {
            die("Insert Quest Error: " . $conn->error);
        }

        $stmt->bind_param("ssiss", $title, $description, $xpReward, $badgeReward, $status);

        if ($stmt->execute()) {
            $message = "Quest added successfully!";
            $messageType = "success";
        } else {
            $message = "Failed to add quest.";
            $messageType = "error";
        }

        $stmt->close();
    }
}

/* Load edit quest */
if (isset($_GET['edit_quest_id'])) {
    $editQuestId = (int)$_GET['edit_quest_id'];

    $stmt = $conn->prepare("
        SELECT quest_id, title, description, xp_reward, badge_reward, status
        FROM quests
        WHERE quest_id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        die("Load Quest Error: " . $conn->error);
    }

    $stmt->bind_param("i", $editQuestId);
    $stmt->execute();
    $result = $stmt->get_result();
    $editQuest = $result->fetch_assoc();
    $stmt->close();

    if ($editQuest) {
        $editQuestMode = true;
    }
}

/* Update quest */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_quest'])) {
    $questId = (int)($_POST['quest_id'] ?? 0);
    $title = trim($_POST['title'] ?? "");
    $description = trim($_POST['description'] ?? "");
    $xpReward = (int)($_POST['xp_reward'] ?? 0);
    $badgeReward = trim($_POST['badge_reward'] ?? "");
    $status = trim($_POST['quest_status'] ?? "Active");

    if ($title === "" || $description === "") {
        $message = "Please fill in all quest fields.";
        $messageType = "error";
    } else {
        $stmt = $conn->prepare("
            UPDATE quests
            SET title = ?, description = ?, xp_reward = ?, badge_reward = ?, status = ?
            WHERE quest_id = ?
        ");

        if (!$stmt) {
            die("Update Quest Error: " . $conn->error);
        }

        $stmt->bind_param("ssissi", $title, $description, $xpReward, $badgeReward, $status, $questId);

        if ($stmt->execute()) {
            $message = "Quest updated successfully!";
            $messageType = "success";
            $editQuestMode = false;
            $editQuest = null;
        } else {
            $message = "Failed to update quest.";
            $messageType = "error";
        }

        $stmt->close();
    }
}

/* Delete quest */
if (isset($_GET['delete_quest_id'])) {
    $deleteQuestId = (int)$_GET['delete_quest_id'];

    $stmt = $conn->prepare("DELETE FROM quests WHERE quest_id = ?");
    if (!$stmt) {
        die("Delete Quest Error: " . $conn->error);
    }

    $stmt->bind_param("i", $deleteQuestId);

    if ($stmt->execute()) {
        $message = "Quest deleted successfully!";
        $messageType = "success";
    } else {
        $message = "Failed to delete quest.";
        $messageType = "error";
    }

    $stmt->close();
}

/* =========================
   BADGES
========================= */
$editBadgeMode = false;
$editBadge = null;

/* Add badge */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_badge'])) {
    $badgeName = trim($_POST['badge_name'] ?? "");
    $description = trim($_POST['badge_description'] ?? "");
    $xpRequired = (int)($_POST['xp_required'] ?? 0);

    if ($badgeName === "") {
        $message = "Please fill in the badge name.";
        $messageType = "error";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO badges (badge_name, description, xp_required)
            VALUES (?, ?, ?)
        ");

        if (!$stmt) {
            die("Add Badge Error: " . $conn->error);
        }

        $stmt->bind_param("ssi", $badgeName, $description, $xpRequired);

        if ($stmt->execute()) {
            $message = "Badge added successfully!";
            $messageType = "success";
        } else {
            $message = "Failed to add badge.";
            $messageType = "error";
        }

        $stmt->close();
    }
}

/* Load edit badge */
if (isset($_GET['edit_badge_id'])) {
    $editBadgeId = (int)$_GET['edit_badge_id'];

    $stmt = $conn->prepare("
        SELECT badge_id, badge_name, description, xp_required
        FROM badges
        WHERE badge_id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        die("Load Badge Error: " . $conn->error);
    }

    $stmt->bind_param("i", $editBadgeId);
    $stmt->execute();
    $result = $stmt->get_result();
    $editBadge = $result->fetch_assoc();
    $stmt->close();

    if ($editBadge) {
        $editBadgeMode = true;
    }
}

/* Update badge */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_badge'])) {
    $badgeId = (int)($_POST['badge_id'] ?? 0);
    $badgeName = trim($_POST['badge_name'] ?? "");
    $description = trim($_POST['badge_description'] ?? "");
    $xpRequired = (int)($_POST['xp_required'] ?? 0);

    if ($badgeName === "") {
        $message = "Please fill in the badge name.";
        $messageType = "error";
    } else {
        $stmt = $conn->prepare("
            UPDATE badges
            SET badge_name = ?, description = ?, xp_required = ?
            WHERE badge_id = ?
        ");

        if (!$stmt) {
            die("Update Badge Error: " . $conn->error);
        }

        $stmt->bind_param("ssii", $badgeName, $description, $xpRequired, $badgeId);

        if ($stmt->execute()) {
            $message = "Badge updated successfully!";
            $messageType = "success";
            $editBadgeMode = false;
            $editBadge = null;
        } else {
            $message = "Failed to update badge.";
            $messageType = "error";
        }

        $stmt->close();
    }
}

/* Delete badge */
if (isset($_GET['delete_badge_id'])) {
    $deleteBadgeId = (int)$_GET['delete_badge_id'];

    $stmt = $conn->prepare("DELETE FROM badges WHERE badge_id = ?");
    if (!$stmt) {
        die("Delete Badge Error: " . $conn->error);
    }

    $stmt->bind_param("i", $deleteBadgeId);

    if ($stmt->execute()) {
        $message = "Badge deleted successfully!";
        $messageType = "success";
    } else {
        $message = "Failed to delete badge.";
        $messageType = "error";
    }

    $stmt->close();
}

/* =========================
   LOAD DATA
========================= */
$quests = [];
$questResult = $conn->query("
    SELECT quest_id, title, description, xp_reward, badge_reward, status, created_at
    FROM quests
    ORDER BY created_at DESC
");
if ($questResult) {
    while ($row = $questResult->fetch_assoc()) {
        $quests[] = $row;
    }
}

$badges = [];
$badgeResult = $conn->query("
    SELECT badge_id, badge_name, description, xp_required
    FROM badges
    ORDER BY xp_required ASC, badge_name ASC
");
if ($badgeResult) {
    while ($row = $badgeResult->fetch_assoc()) {
        $badges[] = $row;
    }
}

$dashboardLink = ($userRole === "instructor") ? "instructor_dashboard.php" : "admin_dashboard.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gamification Management</title>
    <link rel="stylesheet" href="../css/admin_users.css">
</head>
<body>
    <div class="dashboard-page">
        <aside class="sidebar">
            <div class="sidebar-logo">GamiLearn</div>

            <nav class="sidebar-nav">
                <a href="<?php echo $dashboardLink; ?>">Dashboard</a>
                <a href="#">My Courses</a>
                <a href="#">Lesson</a>
                <a href="#">Students</a>
                <a href="#">Quizzes</a>
                <a href="view_logs.php">Activity Logs</a>
                <a href="manage_gamification.php" class="active">Manage Gamification</a>
                <a href="library.php">Library</a>
                <a href="report_issues.php">Report Issue</a>
                <a href="notifications.php">Notifications</a>
                <a href="profile.php">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-title">
                    <h1>Gamification Management</h1>
                    <p>Manage quests and badges in one place</p>
                </div>

                <div class="topbar-actions">
                    <div class="profile-chip">
                        <img src="../media/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                        <span><?php echo htmlspecialchars($username); ?></span>
                    </div>
                </div>
            </header>

            <?php if ($message !== "") { ?>
                <div class="<?php echo ($messageType === "success") ? "success-msg" : "error-msg"; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php } ?>

            <section class="admin-section">
                <h2 style="margin-bottom: 20px;">Quests Management</h2>

                <div class="form-card dashboard-form-card">
                    <form method="POST">
                        <?php if ($editQuestMode && $editQuest) { ?>
                            <input type="hidden" name="quest_id" value="<?php echo (int)$editQuest['quest_id']; ?>">
                        <?php } ?>

                        <label>Quest Title</label>
                        <input type="text" name="title" required value="<?php echo htmlspecialchars($editQuest['title'] ?? ''); ?>">

                        <label>Description</label>
                        <textarea name="description" rows="5" class="issue-textarea" required><?php echo htmlspecialchars($editQuest['description'] ?? ''); ?></textarea>

                        <label>XP Reward</label>
                        <input type="number" name="xp_reward" min="0" required value="<?php echo htmlspecialchars($editQuest['xp_reward'] ?? '0'); ?>">

                        <label>Badge Reward (Optional)</label>
                        <input type="text" name="badge_reward" value="<?php echo htmlspecialchars($editQuest['badge_reward'] ?? ''); ?>">

                        <label>Status</label>
                        <select name="quest_status" required>
                            <option value="Active" <?php echo (($editQuest['status'] ?? '') === 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo (($editQuest['status'] ?? '') === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>

                        <div class="form-actions">
                            <?php if ($editQuestMode) { ?>
                                <button type="submit" name="update_quest" class="save-btn">Update Quest</button>
                                <a href="manage_gamification.php" class="cancel-btn">Cancel</a>
                            <?php } else { ?>
                                <button type="submit" name="add_quest" class="save-btn">Add Quest</button>
                            <?php } ?>
                        </div>
                    </form>
                </div>

                <div style="margin-top: 28px;" class="table-card">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>XP Reward</th>
                                <th>Badge Reward</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($quests) > 0) { ?>
                                <?php foreach ($quests as $quest) { ?>
                                    <tr>
                                        <td><?php echo (int)$quest['quest_id']; ?></td>
                                        <td><?php echo htmlspecialchars($quest['title']); ?></td>
                                        <td><?php echo htmlspecialchars($quest['description']); ?></td>
                                        <td><?php echo (int)$quest['xp_reward']; ?></td>
                                        <td><?php echo htmlspecialchars($quest['badge_reward'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($quest['status']); ?></td>
                                        <td><?php echo htmlspecialchars($quest['created_at']); ?></td>
                                        <td class="action-cell">
                                            <a href="manage_gamification.php?edit_quest_id=<?php echo (int)$quest['quest_id']; ?>" class="edit-btn">Edit</a>
                                            <a href="manage_gamification.php?delete_quest_id=<?php echo (int)$quest['quest_id']; ?>" class="delete-btn" onclick="return confirm('Delete this quest?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="8">No quests found.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="admin-section" style="margin-top: 28px;">
                <h2 style="margin-bottom: 20px;">Badges Management</h2>

                <div class="form-card dashboard-form-card">
                    <form method="POST">
                        <?php if ($editBadgeMode && $editBadge) { ?>
                            <input type="hidden" name="badge_id" value="<?php echo (int)$editBadge['badge_id']; ?>">
                        <?php } ?>

                        <label>Badge Name</label>
                        <input type="text" name="badge_name" required value="<?php echo htmlspecialchars($editBadge['badge_name'] ?? ''); ?>">

                        <label>Description</label>
                        <textarea name="badge_description" rows="5" class="issue-textarea"><?php echo htmlspecialchars($editBadge['description'] ?? ''); ?></textarea>

                        <label>XP Required</label>
                        <input type="number" name="xp_required" min="0" required value="<?php echo htmlspecialchars($editBadge['xp_required'] ?? '0'); ?>">

                        <div class="form-actions">
                            <?php if ($editBadgeMode) { ?>
                                <button type="submit" name="update_badge" class="save-btn">Update Badge</button>
                                <a href="manage_gamification.php" class="cancel-btn">Cancel</a>
                            <?php } else { ?>
                                <button type="submit" name="add_badge" class="save-btn">Add Badge</button>
                            <?php } ?>
                        </div>
                    </form>
                </div>

                <div style="margin-top: 28px;" class="table-card">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Badge Name</th>
                                <th>Description</th>
                                <th>XP Required</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($badges) > 0) { ?>
                                <?php foreach ($badges as $badge) { ?>
                                    <tr>
                                        <td><?php echo (int)$badge['badge_id']; ?></td>
                                        <td><?php echo htmlspecialchars($badge['badge_name']); ?></td>
                                        <td><?php echo htmlspecialchars($badge['description']); ?></td>
                                        <td><?php echo (int)$badge['xp_required']; ?></td>
                                        <td class="action-cell">
                                            <a href="manage_gamification.php?edit_badge_id=<?php echo (int)$badge['badge_id']; ?>" class="edit-btn">Edit</a>
                                            <a href="manage_gamification.php?delete_badge_id=<?php echo (int)$badge['badge_id']; ?>" class="delete-btn" onclick="return confirm('Delete this badge?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="5">No badges found.</td>
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