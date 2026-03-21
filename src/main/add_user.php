<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {
    header("Location: login.php");
    exit();
}

$firstName = $_SESSION['first_name'];
$username = $_SESSION['username'];
$profilePic = !empty($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'default_profile.png';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newUsername = trim($_POST['username']);
    $newFirstName = trim($_POST['first_name']);
    $newLastName = trim($_POST['last_name']);
    $newEmail = trim($_POST['email']);
    $newPassword = trim($_POST['password']);
    $newRole = trim($_POST['role']);
    $profilePicture = "default_profile.png";

    if ($newUsername == "" || $newFirstName == "" || $newLastName == "" || $newEmail == "" || $newPassword == "" || $newRole == "") {
        $error = "Please fill in all fields.";
    } else {
        $checkStmt = $conn->prepare("
            SELECT user_id
            FROM users
            WHERE username = ? OR email = ?
        ");
        $checkStmt->bind_param("ss", $newUsername, $newEmail);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $error = "Username or email already exists.";
        } else {
            $insertStmt = $conn->prepare("
                INSERT INTO users (username, first_name, last_name, email, password, profile_picture, role)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $insertStmt->bind_param(
                "sssssss",
                $newUsername,
                $newFirstName,
                $newLastName,
                $newEmail,
                $newPassword,
                $profilePicture,
                $newRole
            );

            if ($insertStmt->execute()) {
                $_SESSION['admin_success'] = "User added successfully!";
                header("Location: admin_users.php");
                exit();
            } else {
                $error = "Failed to add user.";
            }

            $insertStmt->close();
        }

        $checkStmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link rel="stylesheet" href="../css/admin_users.css">
</head>
<body>
    <div class="dashboard-page">

        <aside class="sidebar">
            <div class="sidebar-logo">GamiLearn</div>

            <nav class="sidebar-nav">
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="admin_users.php" class="active">Users</a>
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
                    <h1>Add User</h1>
                    <p>Create a new user and assign a role</p>
                </div>

                <div class="topbar-actions">
                    <div class="profile-chip">
                        <img src="../media/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                        <span><?php echo htmlspecialchars($username); ?></span>
                    </div>
                </div>
            </header>

            <section class="admin-section">
                <?php if ($error != "") { ?>
                    <div class="error-msg"><?php echo $error; ?></div>
                <?php } ?>

                <div class="form-card dashboard-form-card">
                    <form method="POST">
                        <label>Username</label>
                        <input type="text" name="username" required>

                        <label>First Name</label>
                        <input type="text" name="first_name" required>

                        <label>Last Name</label>
                        <input type="text" name="last_name" required>

                        <label>Email</label>
                        <input type="email" name="email" required>

                        <label>Password</label>
                        <input type="password" name="password" required>

                        <label>Role</label>
                        <select name="role" required>
                            <option value="">Select Role</option>
                            <option value="Admin">Admin</option>
                            <option value="Instructor">Instructor</option>
                            <option value="Student">Student</option>
                        </select>

                        <div class="form-actions">
                            <button type="submit" class="save-btn">Save</button>
                            <a href="admin_dashboard.php" class="cancel-btn">Cancel</a>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>
</body>
</html>