<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connection.php';
include 'SystemMonitor.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['username'];
$userRole = $_SESSION['role'];
$firstName = $_SESSION['first_name'] ?? "User";
$lastName = $_SESSION['last_name'] ?? "";
$fullName = trim($firstName . " " . $lastName);
$profilePic = !empty($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'default_profile.png';

$monitor = new SystemMonitor($conn);

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $issueType = trim($_POST['issue_type']);
    $description = trim($_POST['description']);

    if ($issueType == "" || $description == "") {
        $error = "Please fill in all fields.";
    } else {
        if ($monitor->reportIssue($userId, $userName, $issueType, $description)) {
            $success = "Issue reported successfully!";
        } else {
            $error = "Failed to report issue.";
        }
    }
}

$dashboardLink = "student_dashboard.php";
if ($userRole == "Instructor") {
    $dashboardLink = "instructor_dashboard.php";
} elseif ($userRole == "Admin") {
    $dashboardLink = "admin_dashboard.php";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Issue</title>
    <link rel="stylesheet" href="../css/admin_users.css">
</head>
<body>
    <div class="dashboard-page">
        <aside class="sidebar">
            <div class="sidebar-logo">GamiLearn</div>

            <nav class="sidebar-nav">
                <a href="<?php echo $dashboardLink; ?>">Dashboard</a>
                <a href="report_issue.php" class="active">Report Issue</a>
                <a href="notifications.php">Notifications</a>
                <a href="profile.php">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-title">
                    <h1>Report Issue</h1>
                    <p>Submit an issue or system problem</p>
                </div>

                <div class="topbar-actions">
                    <div class="profile-chip">
                        <img src="../media/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                        <span><?php echo htmlspecialchars($fullName); ?></span>
                    </div>
                </div>
            </header>

            <section class="admin-section">
                <?php if ($success != "") { ?>
                    <div class="success-msg"><?php echo htmlspecialchars($success); ?></div>
                <?php } ?>

                <?php if ($error != "") { ?>
                    <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
                <?php } ?>

                <div class="form-card dashboard-form-card">
                    <form method="POST">
                        <label>Issue Type</label>
                        <select name="issue_type" required>
                            <option value="">Select Issue Type</option>
                            <option value="Bug">Bug</option>
                            <option value="Login Problem">Login Problem</option>
                            <option value="Course Access">Course Access</option>
                            <option value="System Error">System Error</option>
                            <option value="Other">Other</option>
                        </select>

                        <label>Description</label>
                        <textarea name="description" rows="6" class="issue-textarea" required></textarea>

                        <div class="form-actions">
                            <button type="submit" class="save-btn">Submit Report</button>
                            <a href="<?php echo $dashboardLink; ?>" class="cancel-btn">Cancel</a>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>
</body>
</html>