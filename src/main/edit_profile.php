<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$error = "";

/* Load current user info */
$stmt = $conn->prepare("SELECT username, first_name, last_name, email, profile_picture, role, password FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$profilePic = !empty($user['profile_picture']) ? $user['profile_picture'] : 'default_profile.png';
$fullName = $user['first_name'] . " " . $user['last_name'];

$dashboardLink = "student_dashboard.php";
if ($user['role'] == "Instructor") {
    $dashboardLink = "instructor_dashboard.php";
} elseif ($user['role'] == "Admin") {
    $dashboardLink = "admin_dashboard.php";
}

/* Handle update */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if ($username == "" || $firstName == "" || $lastName == "") {
        $error = "Username, first name, and last name cannot be empty.";
    } else {
        $usernameChanged = ($username !== $user['username']);
        $firstNameChanged = ($firstName !== $user['first_name']);
        $lastNameChanged = ($lastName !== $user['last_name']);
        $passwordChanged = ($newPassword !== "" || $confirmPassword !== "");

        if (!$usernameChanged && !$firstNameChanged && !$lastNameChanged && !$passwordChanged) {
            $_SESSION['success'] = "No changes were made.";
            header("Location: edit_profile.php");
            exit();
        }

        /* Check duplicate username only if username changed */
        if ($usernameChanged) {
            $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
            $checkStmt->bind_param("si", $username, $userId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                $error = "That username is already taken.";
            }

            $checkStmt->close();
        }

        if ($error == "") {
            if ($passwordChanged) {
                if ($newPassword == "" || $confirmPassword == "") {
                    $error = "Please fill in both password fields.";
                } elseif ($newPassword != $confirmPassword) {
                    $error = "New password and confirm password do not match.";
                } else {
                    $updateStmt = $conn->prepare("UPDATE users SET username = ?, first_name = ?, last_name = ?, password = ? WHERE user_id = ?");
                    $updateStmt->bind_param("ssssi", $username, $firstName, $lastName, $newPassword, $userId);

                    if ($updateStmt->execute()) {
                        $_SESSION['username'] = $username;
                        $_SESSION['first_name'] = $firstName;
                        $_SESSION['last_name'] = $lastName;
                        $_SESSION['success'] = "Profile updated successfully!";
                        header("Location: edit_profile.php");
                        exit();
                    } else {
                        $error = "Failed to update profile.";
                    }

                    $updateStmt->close();
                }
            } else {
                $updateStmt = $conn->prepare("UPDATE users SET username = ?, first_name = ?, last_name = ? WHERE user_id = ?");
                $updateStmt->bind_param("sssi", $username, $firstName, $lastName, $userId);

                if ($updateStmt->execute()) {
                    $_SESSION['username'] = $username;
                    $_SESSION['first_name'] = $firstName;
                    $_SESSION['last_name'] = $lastName;
                    $_SESSION['success'] = "Profile updated successfully!";
                    header("Location: edit_profile.php");
                    exit();
                } else {
                    $error = "Failed to update profile.";
                }

                $updateStmt->close();
            }
        }
    }
}

/* Reload fresh info */
$stmt = $conn->prepare("SELECT username, first_name, last_name, email, profile_picture, role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$profilePic = !empty($user['profile_picture']) ? $user['profile_picture'] : 'default_profile.png';
$fullName = $user['first_name'] . " " . $user['last_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../css/profile.css">
</head>
<body>

<div class="dashboard-page">

    <aside class="sidebar">
        <div class="sidebar-logo">GamiLearn</div>

        <nav class="sidebar-nav">
            <a href="<?php echo $dashboardLink; ?>">Dashboard</a>
            <a href="profile.php" class="active">Settings</a>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>

    <main class="main-content">

        <header class="topbar">
            <div class="topbar-title">
                <h1>Profile Management</h1>
                <p>Edit your personal information</p>
            </div>

            <div class="topbar-actions">
                <div class="profile-chip">
                    <img src="../media/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                    <span><?php echo htmlspecialchars($fullName); ?></span>
                </div>
            </div>
        </header>

        <section class="profile-section">
            <div class="profile-header">
                <h2>Personal Info</h2>

                <div class="profile-picture-wrapper">
                    <img
                        src="../media/<?php echo htmlspecialchars($profilePic); ?>"
                        alt="Profile Picture"
                        class="big-profile-picture"
                    >
                    <p class="picture-hint">Change picture from Profile page</p>
                </div>
            </div>

            <?php
            if (isset($_SESSION['success'])) {
                echo "<div class='success-msg'>" . $_SESSION['success'] . "</div>";
                unset($_SESSION['success']);
            }
            ?>

            <?php if ($error != "") { ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php } ?>

            <form method="POST">
                <div class="info-card">

                    <div class="info-row">
                        <div class="info-icon">👤</div>
                        <div class="info-content edit-info-content">
                            <span class="info-label">Username</span>
                            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="info-input" required>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon">📝</div>
                        <div class="info-content edit-info-content">
                            <span class="info-label">First Name</span>
                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" class="info-input" required>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon">📝</div>
                        <div class="info-content edit-info-content">
                            <span class="info-label">Last Name</span>
                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" class="info-input" required>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon">📧</div>
                        <div class="info-content edit-info-content">
                            <span class="info-label">Email</span>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="info-input" disabled>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon">🔒</div>
                        <div class="info-content edit-info-content">
                            <span class="info-label">New Password</span>
                            <input type="password" name="new_password" placeholder="Leave blank if no change" class="info-input">
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon">🔐</div>
                        <div class="info-content edit-info-content">
                            <span class="info-label">Confirm New Password</span>
                            <input type="password" name="confirm_password" placeholder="Repeat new password" class="info-input">
                        </div>
                    </div>

                    <div class="profile-actions">
                        <button type="submit" class="action-btn form-btn">Save Changes</button>
                        <a href="profile.php" class="action-btn secondary-btn">Back</a>
                    </div>

                </div>
            </form>
        </section>
    </main>
</div>

<script>
setTimeout(() => {
    const msg = document.querySelector('.success-msg');
    if (msg) {
        msg.style.display = 'none';
    }
}, 3000);
</script>

</body>
</html>