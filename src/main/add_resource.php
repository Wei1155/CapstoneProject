<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != "Instructor" && $_SESSION['role'] != "Admin")) {
    header("Location: login.php");
    exit();
}

$userRole = $_SESSION['role'];
$firstName = $_SESSION['first_name'] ?? "User";
$username = $_SESSION['username'] ?? "user";
$profilePic = !empty($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'default_profile.png';

$courses = [];
$success = "";
$error = "";

/* Load courses */
$courseResult = $conn->query("
    SELECT course_id, course_title
    FROM courses
    ORDER BY course_title ASC
");

if ($courseResult) {
    while ($row = $courseResult->fetch_assoc()) {
        $courses[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courseId = (int) $_POST['course_id'];
    $title = trim($_POST['title']);
    $resourceType = trim($_POST['resource_type']);
    $filePath = trim($_POST['file_path']);
    $externalLink = trim($_POST['external_link']);
    $description = trim($_POST['description']);

    if ($courseId <= 0 || $title == "" || $resourceType == "") {
        $error = "Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO resource_library
            (course_id, title, resource_type, file_path, external_link, description, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        if ($stmt) {
            $stmt->bind_param("isssss", $courseId, $title, $resourceType, $filePath, $externalLink, $description);

            if ($stmt->execute()) {
                $success = "Resource added successfully!";
            } else {
                $error = "Failed to add resource.";
            }

            $stmt->close();
        } else {
            $error = "Database error.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Resource</title>
    <link rel="stylesheet" href="../css/admin_users.css">
</head>
<body>
    <div class="dashboard-page">
        <aside class="sidebar">
            <div class="sidebar-logo">GamiLearn</div>

            <nav class="sidebar-nav">
                <?php if ($userRole == "Instructor") { ?>
                    <a href="instructor_dashboard.php">Dashboard</a>
                <?php } else { ?>
                    <a href="admin_dashboard.php">Dashboard</a>
                <?php } ?>
                <a href="library.php">Library</a>
                <a href="add_resource.php" class="active">Add Resource</a>
                <a href="profile.php">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-title">
                    <h1>Add Resource</h1>
                    <p>Create a new library resource for students</p>
                </div>

                <div class="topbar-actions">
                    <div class="profile-chip">
                        <img src="../media/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                        <span><?php echo htmlspecialchars($username); ?></span>
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
                        <label>Course</label>
                        <select name="course_id" required>
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $course) { ?>
                                <option value="<?php echo $course['course_id']; ?>">
                                    <?php echo htmlspecialchars($course['course_title']); ?>
                                </option>
                            <?php } ?>
                        </select>

                        <label>Resource Title</label>
                        <input type="text" name="title" required>

                        <label>Resource Type</label>
                        <select name="resource_type" required>
                            <option value="">Select Type</option>
                            <option value="PDF">PDF</option>
                            <option value="Video">Video</option>
                            <option value="Slide">Slide</option>
                            <option value="Link">Link</option>
                            <option value="Document">Document</option>
                        </select>

                        <label>File Path (optional)</label>
                        <input type="text" name="file_path" placeholder="../media/file.pdf">

                        <label>External Link (optional)</label>
                        <input type="text" name="external_link" placeholder="https://example.com">

                        <label>Description</label>
                        <textarea name="description" rows="5" class="issue-textarea"></textarea>

                        <div class="form-actions">
                            <button type="submit" class="save-btn">Add Resource</button>
                            <a href="library.php" class="cancel-btn">Cancel</a>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>
</body>
</html>