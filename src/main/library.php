<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userRole = $_SESSION['role'];
$firstName = $_SESSION['first_name'] ?? "User";
$lastName = $_SESSION['last_name'] ?? "";
$fullName = trim($firstName . " " . $lastName);
$username = $_SESSION['username'] ?? "user";
$profilePic = !empty($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'default_profile.png';

$dashboardLink = "student_dashboard.php";
if ($userRole == "Instructor") {
    $dashboardLink = "instructor_dashboard.php";
} elseif ($userRole == "Admin") {
    $dashboardLink = "admin_dashboard.php";
}

$search = trim($_GET['search'] ?? "");
$courseFilter = trim($_GET['course_id'] ?? "");

$courses = [];
$resources = [];

/* Load courses for dropdown */
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

/* Build resource query */
$sql = "
    SELECT rl.resource_id, rl.course_id, rl.title, rl.resource_type,
           rl.file_path, rl.external_link, rl.description, rl.created_at,
           c.course_title
    FROM resource_library rl
    INNER JOIN courses c ON rl.course_id = c.course_id
    WHERE 1=1
";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (rl.title LIKE ? OR rl.description LIKE ? OR c.course_title LIKE ?)";
    $searchLike = "%" . $search . "%";
    $params[] = $searchLike;
    $params[] = $searchLike;
    $params[] = $searchLike;
    $types .= "sss";
}

if ($courseFilter !== "") {
    $sql .= " AND rl.course_id = ?";
    $params[] = $courseFilter;
    $types .= "i";
}

$sql .= " ORDER BY rl.created_at DESC";

$stmt = $conn->prepare($sql);

if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $resources[] = $row;
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resource Library</title>
    <link rel="stylesheet" href="../css/admin_users.css">
</head>
<body>
    <div class="dashboard-page">
        <aside class="sidebar">
            <div class="sidebar-logo">GamiLearn</div>

            <nav class="sidebar-nav">
                <a href="<?php echo $dashboardLink; ?>">Dashboard</a>
                <a href="library.php" class="active">Library</a>
                <?php if ($userRole == "Instructor" || $userRole == "Admin") { ?>
                    <a href="add_resource.php">Add Resource</a>
                <?php } ?>
                <a href="profile.php">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-title">
                    <h1>Resource Library</h1>
                    <p>Browse learning materials and references</p>
                </div>

                <div class="topbar-actions">
                    <div class="profile-chip">
                        <img src="../media/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                        <span><?php echo htmlspecialchars($fullName); ?></span>
                    </div>
                </div>
            </header>

            <section class="admin-section">
                <div class="toolbar">
                    <form method="GET" class="search-form">
                        <input type="text" name="search" class="search-input" placeholder="Search resources..." value="<?php echo htmlspecialchars($search); ?>">

                        <select name="course_id" class="search-input">
                            <option value="">All Courses</option>
                            <?php foreach ($courses as $course) { ?>
                                <option value="<?php echo $course['course_id']; ?>" <?php echo ($courseFilter == $course['course_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['course_title']); ?>
                                </option>
                            <?php } ?>
                        </select>

                        <button type="submit" class="search-btn">Search</button>
                    </form>

                    <?php if ($userRole == "Instructor" || $userRole == "Admin") { ?>
                        <a href="add_resource.php" class="add-btn">Add Resource</a>
                    <?php } ?>
                </div>

                <div class="table-card">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Course</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Access</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($resources) > 0) { ?>
                                <?php foreach ($resources as $resource) { ?>
                                    <tr>
                                        <td><?php echo $resource['resource_id']; ?></td>
                                        <td><?php echo htmlspecialchars($resource['course_title']); ?></td>
                                        <td><?php echo htmlspecialchars($resource['title']); ?></td>
                                        <td><?php echo htmlspecialchars($resource['resource_type']); ?></td>
                                        <td><?php echo htmlspecialchars($resource['description'] ?? ''); ?></td>
                                        <td>
                                            <?php if (!empty($resource['external_link'])) { ?>
                                                <a href="<?php echo htmlspecialchars($resource['external_link']); ?>" target="_blank" class="edit-btn">Open Link</a>
                                            <?php } elseif (!empty($resource['file_path'])) { ?>
                                                <a href="<?php echo htmlspecialchars($resource['file_path']); ?>" target="_blank" class="edit-btn">Open File</a>
                                            <?php } else { ?>
                                                <span>No resource link</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="6">No resources found.</td>
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