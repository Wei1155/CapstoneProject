<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Student") {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['lesson_id']) || !isset($_GET['course_id'])) {
    header("Location: my_courses.php");
    exit();
}

$userId = $_SESSION['user_id'];
$lessonId = (int) $_GET['lesson_id'];
$courseId = (int) $_GET['course_id'];

$firstName = $_SESSION['first_name'];
$lastName = $_SESSION['last_name'];
$fullName = $firstName . " " . $lastName;
$profilePic = !empty($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'default_profile.png';

/* Get current course */
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

/* Get lesson info */
$lessonStmt = $conn->prepare("
    SELECT lesson_id, lesson_title, lesson_order
    FROM lessons
    WHERE lesson_id = ? AND course_id = ?
");
$lessonStmt->bind_param("ii", $lessonId, $courseId);
$lessonStmt->execute();
$lessonResult = $lessonStmt->get_result();

if ($lessonResult->num_rows === 0) {
    header("Location: resume_course.php?course_id=" . $courseId);
    exit();
}

$lesson = $lessonResult->fetch_assoc();
$lessonStmt->close();

/* Get total lessons */
$totalStmt = $conn->prepare("
    SELECT COUNT(*) AS total_lessons
    FROM lessons
    WHERE course_id = ?
");
$totalStmt->bind_param("i", $courseId);
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totalLessons = (int) $totalResult->fetch_assoc()['total_lessons'];
$totalStmt->close();

/* Handle completion */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $currentProgress = (int) $course['progress'];

    if ($totalLessons > 0) {
        $increment = 100 / $totalLessons;
        $newProgress = round($currentProgress + $increment);

        if ($newProgress > 100) {
            $newProgress = 100;
        }

        $updateStmt = $conn->prepare("
            UPDATE enrollments
            SET progress = ?
            WHERE user_id = ? AND course_id = ?
        ");
        $updateStmt->bind_param("iii", $newProgress, $userId, $courseId);
        $updateStmt->execute();
        $updateStmt->close();
    }

    header("Location: resume_course.php?course_id=" . $courseId);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lesson Page</title>
    <link rel="stylesheet" href="../css/resume_course.css">
    <style>
        .lesson-page-card {
            background: white;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }

        .lesson-inner-card {
            border: 1px solid #e5e7eb;
            border-radius: 28px;
            overflow: hidden;
            background: #fff;
        }

        .lesson-inner-header {
            padding: 24px 28px;
            border-bottom: 1px solid #e5e7eb;
        }

        .lesson-inner-header h2 {
            font-size: 32px;
        }

        .lesson-content {
            padding: 28px;
        }

        .lesson-content h3 {
            font-size: 24px;
            margin-bottom: 14px;
        }

        .lesson-content p {
            font-size: 18px;
            color: #4b5563;
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .lesson-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .complete-btn {
            padding: 12px 20px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(90deg, #6366f1, #8b5cf6);
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        .back-link-btn {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 12px;
            text-decoration: none;
            background: #e5e7eb;
            color: #1f2937;
            font-weight: bold;
        }
    </style>
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
                    <h1>Lesson Page</h1>
                    <p>Start and complete your lesson</p>
                </div>

                <div class="topbar-actions">
                    <button class="icon-btn">🔔</button>
                    <div class="profile-chip">
                        <img src="../media/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                        <span><?php echo htmlspecialchars($fullName); ?></span>
                    </div>
                </div>
            </header>

            <section class="lesson-page-card">
                <div class="resume-top-actions">
                    <a href="resume_course.php?course_id=<?php echo $courseId; ?>" class="back-btn">Back</a>
                </div>

                <div class="lesson-inner-card">
                    <div class="lesson-inner-header">
                        <h2><?php echo htmlspecialchars($course['course_title']); ?></h2>
                    </div>

                    <div class="lesson-content">
                        <h3><?php echo htmlspecialchars($lesson['lesson_title']); ?></h3>
                        <p>
                            This is a temporary lesson content page for your project.
                            Later, you can replace this with real lesson notes, videos, slides, or quizzes.
                        </p>

                        <div class="lesson-actions">
                            <form method="POST">
                                <button type="submit" class="complete-btn">Complete Lesson</button>
                            </form>

                            <a href="resume_course.php?course_id=<?php echo $courseId; ?>" class="back-link-btn">
                                Return to Resume Page
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>