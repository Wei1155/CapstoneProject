<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connection.php';
include 'SecuritySystem.php';

$security = new SecuritySystem($conn);

if (!$security->checkAccess(['Admin'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$firstName = $_SESSION['first_name'];
$username = $_SESSION['username'];
$profilePic = !empty($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'default_profile.png';

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $settingsToUpdate = [
        "system_name" => trim($_POST['system_name']),
        "maintenance_mode" => trim($_POST['maintenance_mode']),
        "allow_student_registration" => trim($_POST['allow_student_registration']),

        "allow_course_creation" => trim($_POST['allow_course_creation']),
        "max_courses_per_instructor" => trim($_POST['max_courses_per_instructor']),
        "allow_course_editing" => trim($_POST['allow_course_editing']),

        "enable_leaderboard" => trim($_POST['enable_leaderboard']),
        "enable_badges" => trim($_POST['enable_badges']),
        "enable_rewards" => trim($_POST['enable_rewards']),
        "xp_per_completed_lesson" => trim($_POST['xp_per_completed_lesson']),

        "max_quiz_attempts" => trim($_POST['max_quiz_attempts']),
        "pass_mark" => trim($_POST['pass_mark']),
        "allow_quiz_retry" => trim($_POST['allow_quiz_retry']),
        "show_quiz_result_immediately" => trim($_POST['show_quiz_result_immediately']),

        "password_min_length" => trim($_POST['password_min_length']),
        "max_login_attempts" => trim($_POST['max_login_attempts']),
        "account_lock_minutes" => trim($_POST['account_lock_minutes'])
    ];

    $allSuccess = true;

    foreach ($settingsToUpdate as $key => $value) {
        $result = $security->updateSetting($key, $value);
        if (!$result['success']) {
            $allSuccess = false;
        }
    }

    if ($allSuccess) {
        $message = "System settings updated successfully!";
        $messageType = "success";
    } else {
        $message = "Failed to update one or more settings.";
        $messageType = "error";
    }
}

/* Load all current settings */
$systemName = $security->getSetting("system_name") ?? "GamiLearn";
$maintenanceMode = $security->getSetting("maintenance_mode") ?? "False";
$allowStudentRegistration = $security->getSetting("allow_student_registration") ?? "True";

$allowCourseCreation = $security->getSetting("allow_course_creation") ?? "True";
$maxCoursesPerInstructor = $security->getSetting("max_courses_per_instructor") ?? "5";
$allowCourseEditing = $security->getSetting("allow_course_editing") ?? "True";

$enableLeaderboard = $security->getSetting("enable_leaderboard") ?? "True";
$enableBadges = $security->getSetting("enable_badges") ?? "True";
$enableRewards = $security->getSetting("enable_rewards") ?? "True";
$xpPerCompletedLesson = $security->getSetting("xp_per_completed_lesson") ?? "10";

$maxQuizAttempts = $security->getSetting("max_quiz_attempts") ?? "3";
$passMark = $security->getSetting("pass_mark") ?? "50";
$allowQuizRetry = $security->getSetting("allow_quiz_retry") ?? "True";
$showQuizResultImmediately = $security->getSetting("show_quiz_result_immediately") ?? "True";

$passwordMinLength = $security->getSetting("password_min_length") ?? "8";
$maxLoginAttempts = $security->getSetting("max_login_attempts") ?? "5";
$accountLockMinutes = $security->getSetting("account_lock_minutes") ?? "15";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings</title>
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
                <a href="view_reports.php">Reports</a>
                <a href="view_logs.php">Logs</a>
                <a href="system_settings.php" class="active">System Settings</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-title">
                    <h1>System Settings</h1>
                    <p>Manage system behavior and security rules</p>
                </div>

                <div class="topbar-actions">
                    <div class="profile-chip">
                        <img src="../media/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                        <span><?php echo htmlspecialchars($username); ?></span>
                    </div>
                </div>
            </header>

            <section class="admin-section">
                <?php if ($message != "") { ?>
                    <div class="<?php echo $messageType == 'success' ? 'success-msg' : 'error-msg'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php } ?>

                <div class="form-card dashboard-form-card">
                    <form method="POST">

                        <h2>System Behavior</h2>
                        <label>System Name</label>
                        <input type="text" name="system_name" value="<?php echo htmlspecialchars($systemName); ?>" required>

                        <label>Maintenance Mode</label>
                        <select name="maintenance_mode" required>
                            <option value="True" <?php echo ($maintenanceMode == "True") ? "selected" : ""; ?>>True</option>
                            <option value="False" <?php echo ($maintenanceMode == "False") ? "selected" : ""; ?>>False</option>
                        </select>

                        <label>Allow Student Registration</label>
                        <select name="allow_student_registration" required>
                            <option value="True" <?php echo ($allowStudentRegistration == "True") ? "selected" : ""; ?>>True</option>
                            <option value="False" <?php echo ($allowStudentRegistration == "False") ? "selected" : ""; ?>>False</option>
                        </select>

                        <h2>Course Control</h2>
                        <label>Allow Course Creation</label>
                        <select name="allow_course_creation" required>
                            <option value="True" <?php echo ($allowCourseCreation == "True") ? "selected" : ""; ?>>True</option>
                            <option value="False" <?php echo ($allowCourseCreation == "False") ? "selected" : ""; ?>>False</option>
                        </select>

                        <label>Max Courses Per Instructor</label>
                        <input type="number" name="max_courses_per_instructor" value="<?php echo htmlspecialchars($maxCoursesPerInstructor); ?>" min="1" required>

                        <label>Allow Course Editing</label>
                        <select name="allow_course_editing" required>
                            <option value="True" <?php echo ($allowCourseEditing == "True") ? "selected" : ""; ?>>True</option>
                            <option value="False" <?php echo ($allowCourseEditing == "False") ? "selected" : ""; ?>>False</option>
                        </select>

                        <h2>Gamification Control</h2>
                        <label>Enable Leaderboard</label>
                        <select name="enable_leaderboard" required>
                            <option value="True" <?php echo ($enableLeaderboard == "True") ? "selected" : ""; ?>>True</option>
                            <option value="False" <?php echo ($enableLeaderboard == "False") ? "selected" : ""; ?>>False</option>
                        </select>

                        <label>Enable Badges</label>
                        <select name="enable_badges" required>
                            <option value="True" <?php echo ($enableBadges == "True") ? "selected" : ""; ?>>True</option>
                            <option value="False" <?php echo ($enableBadges == "False") ? "selected" : ""; ?>>False</option>
                        </select>

                        <label>Enable Rewards</label>
                        <select name="enable_rewards" required>
                            <option value="True" <?php echo ($enableRewards == "True") ? "selected" : ""; ?>>True</option>
                            <option value="False" <?php echo ($enableRewards == "False") ? "selected" : ""; ?>>False</option>
                        </select>

                        <label>XP Per Completed Lesson</label>
                        <input type="number" name="xp_per_completed_lesson" value="<?php echo htmlspecialchars($xpPerCompletedLesson); ?>" min="0" required>

                        <h2>Quiz & Learning Rules</h2>
                        <label>Max Quiz Attempts</label>
                        <input type="number" name="max_quiz_attempts" value="<?php echo htmlspecialchars($maxQuizAttempts); ?>" min="1" required>

                        <label>Pass Mark</label>
                        <input type="number" name="pass_mark" value="<?php echo htmlspecialchars($passMark); ?>" min="0" max="100" required>

                        <label>Allow Quiz Retry</label>
                        <select name="allow_quiz_retry" required>
                            <option value="True" <?php echo ($allowQuizRetry == "True") ? "selected" : ""; ?>>True</option>
                            <option value="False" <?php echo ($allowQuizRetry == "False") ? "selected" : ""; ?>>False</option>
                        </select>

                        <label>Show Quiz Result Immediately</label>
                        <select name="show_quiz_result_immediately" required>
                            <option value="True" <?php echo ($showQuizResultImmediately == "True") ? "selected" : ""; ?>>True</option>
                            <option value="False" <?php echo ($showQuizResultImmediately == "False") ? "selected" : ""; ?>>False</option>
                        </select>

                        <h2>Security Settings</h2>
                        <label>Password Minimum Length</label>
                        <input type="number" name="password_min_length" value="<?php echo htmlspecialchars($passwordMinLength); ?>" min="4" required>

                        <label>Max Login Attempts</label>
                        <input type="number" name="max_login_attempts" value="<?php echo htmlspecialchars($maxLoginAttempts); ?>" min="1" required>

                        <label>Account Lock Minutes</label>
                        <input type="number" name="account_lock_minutes" value="<?php echo htmlspecialchars($accountLockMinutes); ?>" min="1" required>

                        <div class="form-actions">
                            <button type="submit" class="save-btn">Save Settings</button>
                            <a href="admin_dashboard.php" class="cancel-btn">Cancel</a>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>
</body>
</html>