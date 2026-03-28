<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Student") {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['course_id'])) {
    header("Location: my_courses.php");
    exit();
}

$userId = $_SESSION['user_id'];
$courseId = (int) $_GET['course_id'];

$firstName = $_SESSION['first_name'];
$lastName = $_SESSION['last_name'];
$fullName = $firstName . " " . $lastName;
$profilePic = !empty($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'default_profile.png';

/* Get course info */
$courseStmt = $conn->prepare("
    SELECT c.course_title, e.progress
    FROM enrollments e
    INNER JOIN courses c ON e.course_id = c.course_id
    WHERE e.user_id = ? AND e.course_id = ?
");
$courseStmt->bind_param("ii", $userId, $courseId);
$courseStmt->execute();
$courseResult = $courseStmt->get_result();

if ($courseResult->num_rows === 0) {
    header("Location: my_courses.php");
    exit();
}

$course = $courseResult->fetch_assoc();
$courseStmt->close();

$progress = (int) $course['progress'];

/* Get lessons */
$lessonStmt = $conn->prepare("
    SELECT lesson_id, lesson_title, lesson_order
    FROM lessons
    WHERE course_id = ?
    ORDER BY lesson_order ASC, lesson_id ASC
");
$lessonStmt->bind_param("i", $courseId);
$lessonStmt->execute();
$lessonResult = $lessonStmt->get_result();

$lessons = [];
while ($row = $lessonResult->fetch_assoc()) {
    $lessons[] = $row;
}
$lessonStmt->close();

$totalLessons = count($lessons);

/* Build lesson status from progress */
if ($totalLessons > 0) {
    $completedCount = floor(($progress / 100) * $totalLessons);

    if ($completedCount >= $totalLessons && $progress < 100) {
        $completedCount = $totalLessons - 1;
    }

    foreach ($lessons as $index => $lesson) {
        if ($progress >= 100) {
            $lessons[$index]['status'] = 'completed';
        } else {
            if ($index < $completedCount) {
                $lessons[$index]['status'] = 'completed';
            } elseif ($index == $completedCount) {
                $lessons[$index]['status'] = 'current';
            } else {
                $lessons[$index]['status'] = 'locked';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Course</title>
    <link rel="stylesheet" href="../css/resume_course.css">
</head>
<body>
    <div class="dashboard-page">

        <aside class="sidebar">
            <div class="sidebar-logo">GamiLearn</div>

            <nav class="sidebar-nav">
                <a href="student_dashboard.php">Dashboard</a>
                <a href="all_courses.php" class="active">Courses</a>
                <a href="profile.php">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-title">
                    <h1>Courses Detail</h1>
                    <p>Resume your learning progress</p>
                </div>

                <div class="topbar-actions">
                    <button class="icon-btn">🔔</button>
                    <div class="profile-chip">
                        <img src="../media/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                        <span><?php echo htmlspecialchars($fullName); ?></span>
                    </div>
                </div>
            </header>

            <section class="resume-section">
                <div class="resume-top-actions">
                    <a href="my_courses.php" class="back-btn">Back</a>
                </div>

                <div class="resume-card">
                    <div class="resume-card-header">
                        <h2><?php echo htmlspecialchars($course['course_title']); ?></h2>
                    </div>

                    <div class="resume-progress-area">
                        <h3>Progress : <?php echo $progress; ?>%</h3>

                        <div class="resume-progress-bar">
                            <div class="resume-progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                        </div>
                    </div>

                    <div class="lesson-section">
                        <h3>Lessons</h3>

                        <div class="lesson-list">
                            <?php if ($totalLessons > 0) { ?>
                                <?php foreach ($lessons as $lesson) { ?>
                                    <div class="lesson-row">
                                        <div class="lesson-left">
                                            <span class="lesson-status">
                                                <?php
                                                if ($lesson['status'] == 'completed') {
                                                    echo "✅";
                                                } elseif ($lesson['status'] == 'current') {
                                                    echo "▶";
                                                } else {
                                                    echo "🔒";
                                                }
                                                ?>
                                            </span>

                                            <span class="lesson-title">
                                                <?php echo htmlspecialchars($lesson['lesson_title']); ?>
                                            </span>
                                        </div>

                                        <div class="lesson-action">
                                            <?php if ($lesson['status'] == 'completed') { ?>
                                                <span class="lesson-btn completed-btn">Completed</span>

                                            <?php } elseif ($lesson['status'] == 'current') { ?>
                                                <a href="lesson_page.php?lesson_id=<?php echo (int)$lesson['lesson_id']; ?>&course_id=<?php echo $courseId; ?>" class="lesson-btn">
                                                    Start
                                                </a>

                                            <?php } else { ?>
                                                <span class="lesson-btn disabled-btn">Locked</span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } else { ?>
                                <p class="empty-text">No lessons found for this course.</p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>