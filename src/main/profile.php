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

$stmt = $conn->prepare("
    SELECT username, first_name, last_name, email, profile_picture, role
    FROM users
    WHERE user_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: login.php");
    exit();
}

$profilePic = !empty($user['profile_picture']) ? $user['profile_picture'] : 'default_profile.png';
$fullName = $user['first_name'] . " " . $user['last_name'];

$dashboardLink = "student_dashboard.php";
if ($user['role'] == "Instructor") {
    $dashboardLink = "instructor_dashboard.php";
} elseif ($user['role'] == "Admin") {
    $dashboardLink = "admin_dashboard.php";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Management</title>
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
                <p>Manage your personal information</p>
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
                        id="openProfileModal"
                    >
                    <p class="picture-hint">Click picture to change</p>
                </div>
            </div>

            <?php
            if (isset($_SESSION['upload_success'])) {
                echo "<div class='success-msg'>" . $_SESSION['upload_success'] . "</div>";
                unset($_SESSION['upload_success']);
            }

            if (isset($_SESSION['upload_error'])) {
                echo "<div class='error-msg'>" . $_SESSION['upload_error'] . "</div>";
                unset($_SESSION['upload_error']);
            }
            ?>

            <div class="info-card">
                <div class="info-row">
                    <div class="info-icon">👤</div>
                    <div class="info-content">
                        <span class="info-label">Username</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon">📝</div>
                    <div class="info-content">
                        <span class="info-label">First Name</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['first_name']); ?></span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon">📝</div>
                    <div class="info-content">
                        <span class="info-label">Last Name</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['last_name']); ?></span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon">📧</div>
                    <div class="info-content">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon">🔒</div>
                    <div class="info-content">
                        <span class="info-label">Password</span>
                        <span class="info-value">************</span>
                    </div>
                </div>

                <div class="profile-actions">
                    <a href="edit_profile.php" class="action-btn">Edit Profile</a>
                </div>
            </div>
        </section>
    </main>
</div>

<div class="modal-overlay" id="profileModal">
    <div class="modal-box">
        <button class="close-btn" id="closeProfileModal">&times;</button>
        <h3>Change Profile Picture</h3>
        <p>Select a new image to upload.</p>

        <form action="upload_profile_picture.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="profile_picture" accept="image/*" required>
            <button type="submit" class="upload-btn">Upload</button>
        </form>
    </div>
</div>

<script>
const openModal = document.getElementById("openProfileModal");
const closeModal = document.getElementById("closeProfileModal");
const modal = document.getElementById("profileModal");

openModal.addEventListener("click", () => {
    modal.classList.add("show");
});

closeModal.addEventListener("click", () => {
    modal.classList.remove("show");
});

modal.addEventListener("click", (e) => {
    if (e.target === modal) {
        modal.classList.remove("show");
    }
});
</script>

</body>
</html>