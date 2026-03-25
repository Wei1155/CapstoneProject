<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];
$firstName = $_SESSION['first_name'] ?? "User";
$lastName = $_SESSION['last_name'] ?? "";
$fullName = trim($firstName . " " . $lastName);
$profilePic = !empty($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'default_profile.png';

$dashboardLink = "student_dashboard.php";
if ($role == "Instructor") {
    $dashboardLink = "instructor_dashboard.php";
} elseif ($role == "Admin") {
    $dashboardLink = "admin_dashboard.php";
}

$stmt = $conn->prepare("
    SELECT notification_id, message, is_read, created_at
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

/* Mark all as read */
$markStmt = $conn->prepare("
    UPDATE notifications
    SET is_read = 1
    WHERE user_id = ?
");
$markStmt->bind_param("i", $userId);
$markStmt->execute();
$markStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="../css/admin_users.css">
</head>
<body>
    <div class="dashboard-page">
        <aside class="sidebar">
            <div class="sidebar-logo">GamiLearn</div>

            <nav class="sidebar-nav">
                <a href="<?php echo $dashboardLink; ?>">Dashboard</a>
                <a href="notifications.php" class="active">Notifications</a>
                <a href="profile.php">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-title">
                    <h1>Notifications</h1>
                    <p>Recent updates from the system</p>
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
                                <th>Message</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($notifications) > 0) { ?>
                                <?php foreach ($notifications as $notification) { ?>
                                    <tr>
                                        <td><?php echo $notification['notification_id']; ?></td>
                                        <td><?php echo htmlspecialchars($notification['message']); ?></td>
                                        <td><?php echo htmlspecialchars($notification['created_at']); ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="3">No notifications yet.</td>
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