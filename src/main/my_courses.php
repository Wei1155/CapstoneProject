<?php
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

/* Pagination */
$limit = 3;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $limit;

/* Count total enrolled courses */
$countSql = "
    SELECT COUNT(*) AS total
    FROM enrollments
    INNER JOIN courses ON enrollments.course_id = courses.course_id
    WHERE enrollments.user_id = ?
";
$countParams = [$userId];
$countTypes = "i";

if ($search !== "") {
    $countSql .= " AND courses.course_title LIKE ?";
    $countParams[] = "%" . $search . "%";
    $countTypes .= "s";
}

$countStmt = $conn->prepare($countSql);
$countStmt->bind_param($countTypes, ...$countParams);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalCourses = (int) $countResult->fetch_assoc()['total'];
$countStmt->close();

$totalPages = max(1, ceil($totalCourses / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

/* Load enrolled courses */
$sql = "
    SELECT 
        courses.course_id,
        courses.course_title,
        enrollments.progress
    FROM enrollments
    INNER JOIN courses ON enrollments.course_id = courses.course_id
    WHERE enrollments.user_id = ?
";

$params = [$userId];
$types = "i";

if ($search !== "") {
    $sql .= " AND courses.course_title LIKE ?";
    $params[] = "%" . $search . "%";
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses</title>
    <link rel="stylesheet" href="../css/courses.css">
</head>
<body>
    <div class="dashboard-page">

        <aside class="sidebar">
            <div class="sidebar-logo">GamiLearn</div>

            <nav class="sidebar-nav">
                <a href="student_dashboard.php">Dashboard</a>
                <a href="all_courses.php" class="active">Courses</a>
                <a href="#">Quests</a>
                <a href="#">Rewards</a>
                <a href="#">Library</a>
                <a href="#">Logs</a>
                <a href="profile.php">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-title">
                    <h1>Courses</h1>
                    <p>Track your enrolled courses and continue learning.</p>
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
                    <button type="submit" class="filter-btn">Search</button>
                </form>

                <div class="courses-header-row">
                    <div class="course-tabs">
                        <a href="all_courses.php" class="tab-btn">All Courses</a>
                        <a href="my_courses.php" class="tab-btn active">My Courses</a>
                    </div>

                    <div class="top-pagination">
                        <?php if ($page > 1) { ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" class="arrow-btn">◀</a>
                        <?php } else { ?>
                            <span class="arrow-btn disabled">◀</span>
                        <?php } ?>

                        <?php if ($page < $totalPages) { ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" class="arrow-btn">▶</a>
                        <?php } else { ?>
                            <span class="arrow-btn disabled">▶</span>
                        <?php } ?>
                    </div>
                </div>

                <div class="my-courses-card">
                    <?php if (count($courses) > 0) { ?>
                        <?php foreach ($courses as $course) { ?>
                            <div class="my-course-row">
                                <h3 class="my-course-title"><?php echo htmlspecialchars($course['course_title']); ?></h3>

                                <div class="my-course-bottom">
                                    <div class="my-progress-bar">
                                        <div class="my-progress-fill" style="width: <?php echo (int)$course['progress']; ?>%;"></div>
                                    </div>

                                    <div class="my-course-actions">
                                        <span class="my-progress-text"><?php echo (int)$course['progress']; ?>%</span>
                                        <a href="resume_course.php?course_id=<?php echo (int)$course['course_id']; ?>" class="resume-btn">Resume ▶</a>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <p class="empty-text">You have not enrolled in any courses yet.</p>
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