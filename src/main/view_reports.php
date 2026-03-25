<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connection.php';
include 'SystemMonitor.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {
    header("Location: login.php");
    exit();
}

$adminId = $_SESSION['user_id'];
$adminName = $_SESSION['username'];
$firstName = $_SESSION['first_name'] ?? "Admin";
$lastName = $_SESSION['last_name'] ?? "";
$fullName = trim($firstName . " " . $lastName);
$profilePic = !empty($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'default_profile.png';

$monitor = new SystemMonitor($conn);

if (isset($_GET['delete_issue_id'])) {
    $issueId = (int) $_GET['delete_issue_id'];
    $monitor->deleteIssue($issueId, $adminId);
    header("Location: view_reports.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resolve_issue_id'])) {
    $issueId = (int) $_POST['resolve_issue_id'];
    $responseText = trim($_POST['response_text']);
    $monitor->resolveIssue($issueId, $adminId, $adminName, $responseText);
    header("Location: view_reports.php");
    exit();
}

$reports = $monitor->getAllReports();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Reports</title>
    <link rel="stylesheet" href="../css/admin_users.css">
</head>
<body>
    <div class="dashboard-page">
        <aside class="sidebar">
            <div class="sidebar-logo">GamiLearn</div>

            <nav class="sidebar-nav">
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="admin_users.php">Users</a>
                <a href="create_course.php">Courses</a>
                <a href="view_reports.php" class="active">Reports</a>
                <a href="view_logs.php">Logs</a>
                <a href="notifications.php">Notifications</a>
                <a href="profile.php">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-title">
                    <h1>Issue Reports</h1>
                    <p>Review, resolve, and delete reported issues</p>
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
                                <th>User</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Response</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($reports) > 0) { ?>
                                <?php foreach ($reports as $report) { ?>
                                    <tr>
                                        <td><?php echo $report['issue_id']; ?></td>
                                        <td><?php echo htmlspecialchars($report['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($report['issue_type']); ?></td>
                                        <td><?php echo htmlspecialchars($report['description']); ?></td>
                                        <td><?php echo htmlspecialchars($report['status']); ?></td>
                                        <td><?php echo htmlspecialchars($report['admin_response'] ?? 'None'); ?></td>
                                        <td class="action-cell">
                                            <?php if ($report['status'] != "Resolved") { ?>
                                                <form method="POST" style="display:flex; gap:8px; flex-wrap:wrap;">
                                                    <input type="hidden" name="resolve_issue_id" value="<?php echo $report['issue_id']; ?>">
                                                    <input type="text" name="response_text" placeholder="Admin response" required style="padding:8px 10px; border:1px solid #d1d5db; border-radius:8px;">
                                                    <button type="submit" class="edit-btn">Resolve</button>
                                                </form>
                                            <?php } ?>
                                            <a href="view_reports.php?delete_issue_id=<?php echo $report['issue_id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this report?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="7">No reports found.</td>
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