<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Student") {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$firstName = $_SESSION['first_name'];
$lastName = $_SESSION['last_name'];
$fullName = $firstName . " " . $lastName;
$profilePic = !empty($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'default_profile.png';

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$category = isset($_GET['category']) ? trim($_GET['category']) : "All";

/* Pagination */
$limit = 3;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $limit;

/* Count total courses */
$countSql = "SELECT COUNT(*) AS total FROM courses WHERE 1=1";
$countParams = [];
$countTypes = "";

if ($search !== "") {
    $countSql .= " AND course_title LIKE ?";
    $countParams[] = "%" . $search . "%";
    $countTypes .= "s";
}

if ($category !== "" && $category !== "All") {
    $countSql .= " AND category = ?";
    $countParams[] = $category;
    $countTypes .= "s";
}

$countStmt = $conn->prepare($countSql);
if (!empty($countParams)) {
    $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalCourses = (int) $countResult->fetch_assoc()['total'];
$countStmt->close();

$totalPages = max(1, ceil($totalCourses / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

/* Load courses */
$sql = "
    SELECT 
        courses.course_id,
        courses.course_title,
        courses.category,
        courses.level,
        courses.rating,
        courses.course_image,
        CASE 
            WHEN enrollments.user_id IS NOT NULL THEN 1
            ELSE 0
        END AS is_enrolled
    FROM courses
    LEFT JOIN enrollments 
        ON courses.course_id = enrollments.course_id
        AND enrollments.user_id = ?
    WHERE 1=1
";

$params = [$userId];
$types = "i";

if ($search !== "") {
    $sql .= " AND courses.course_title LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}

if ($category !== "" && $category !== "All") {
    $sql .= " AND courses.category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY courses.course_id ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}
$stmt->close();

/* Categories */
$categoryResult = $conn->query("SELECT DISTINCT category FROM courses ORDER BY category ASC");
$categories = [];
while ($row = $categoryResult->fetch_assoc()) {
    $categories[] = $row['category'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Courses</title>
    <link rel="stylesheet" href="../css/courses.css">
</head>
<body>
    <div class="dashboard-page">

        <aside class="sidebar">
            <div class="sidebar-logo">GamiLearn</div>

            <nav class="sidebar-nav">
                <a href="student_dashboard.php" class="active">Dashboard</a>
                <a href="all_courses.php">Courses</a>
                <a href="quests.php">Quests</a>
                <a href="rewards.php">Rewards</a>
                <a href="view_logs.php">Activity Log</a>
                <a href="library.php">Library</a>
                <a href="notifications.php">Notifications</a>
                <a href="report_issues.php">Report Issue</a>
                <a href="profile.php">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-title">
                    <h1>Courses</h1>
                    <p>Browse and enroll in available courses.</p>
                </div>

                <div class="topbar-actions">
                    <button class="icon-btn">🔔</button>
                    <div class="profile-chip">
                        <img src="../media/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                        <span><?php echo htmlspecialchars($fullName); ?></span>
                    </div>
                </div>
            </header>

            <section class="courses-section">
                <form method="GET" class="courses-toolbar">
                    <input
                        type="text"
                        name="search"
                        placeholder="Search Courses"
                        value="<?php echo htmlspecialchars($search); ?>"
                        class="search-input"
                    >

                    <select name="category" class="category-select">
                        <option value="All">All Categories</option>
                        <?php foreach ($categories as $cat) { ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($category == $cat) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php } ?>
                    </select>

                    <button type="submit" class="filter-btn">Search</button>
                </form>

                <div class="courses-header-row">
                    <div class="course-tabs">
                        <a href="all_courses.php" class="tab-btn active">All Courses</a>
                        <a href="my_courses.php" class="tab-btn">My Courses</a>
                    </div>

                    <div class="top-pagination">
                        <?php if ($page > 1) { ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>" class="arrow-btn">
                                ◀
                            </a>
                        <?php } else { ?>
                            <span class="arrow-btn disabled">◀</span>
                        <?php } ?>

                        <?php if ($page < $totalPages) { ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>" class="arrow-btn">
                                ▶
                            </a>
                        <?php } else { ?>
                            <span class="arrow-btn disabled">▶</span>
                        <?php } ?>
                    </div>
                </div>

                <?php
                if (isset($_SESSION['course_success'])) {
                    echo "<div class='success-msg'>" . $_SESSION['course_success'] . "</div>";
                    unset($_SESSION['course_success']);
                }

                if (isset($_SESSION['course_error'])) {
                    echo "<div class='error-msg'>" . $_SESSION['course_error'] . "</div>";
                    unset($_SESSION['course_error']);
                }
                ?>

                <div class="course-list-card">
                    <?php if (count($courses) > 0) { ?>
                        <?php foreach ($courses as $course) { ?>
                            <div class="course-item">
                                <div class="course-image-wrap">
                                    <img src="../media/<?php echo htmlspecialchars($course['course_image']); ?>" alt="Course Image" class="course-image">
                                </div>

                                <div class="course-details">
                                    <h3><?php echo htmlspecialchars($course['course_title']); ?></h3>
                                    <p class="course-level"><?php echo htmlspecialchars($course['level']); ?></p>
                                    <p class="course-rating">⭐ <?php echo htmlspecialchars($course['rating']); ?></p>

                                    <?php if ((int)$course['is_enrolled'] === 1) { ?>
                                        <button class="enrolled-btn" disabled>Enrolled</button>
                                    <?php } else { ?>
                                        <form action="enroll_course.php" method="POST">
                                            <input type="hidden" name="course_id" value="<?php echo (int)$course['course_id']; ?>">
                                            <button type="submit" class="enroll-btn">Enroll</button>
                                        </form>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <p class="empty-text">No courses found.</p>
                    <?php } ?>
                </div>

                <div class="page-status">
                    Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>