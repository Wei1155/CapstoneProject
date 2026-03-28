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

if (!isset($_GET['lesson_id']) || !isset($_GET['course_id'])) {
    die("Lesson ID or Course ID missing.");
}

$lessonId = (int) $_GET['lesson_id'];
$courseId = (int) $_GET['course_id'];

$lessonTitle = "Lesson";
$noteTitle = "";
$noteContent = "";
$quizId = 0;
$quizTitle = "";
$passMark = 50;
$questions = [];
$quizMessage = "";
$quizPassed = false;
$answerResults = [];
$quizSubmitted = false;

/* Get lesson title */
$lessonStmt = $conn->prepare("
    SELECT lesson_title
    FROM lessons
    WHERE lesson_id = ?
");
$lessonStmt->bind_param("i", $lessonId);
$lessonStmt->execute();
$lessonResult = $lessonStmt->get_result();

if ($lessonRow = $lessonResult->fetch_assoc()) {
    $lessonTitle = $lessonRow['lesson_title'];
}
$lessonStmt->close();

/* Get lesson note */
$noteStmt = $conn->prepare("
    SELECT note_title, note_content
    FROM lesson_notes
    WHERE lesson_id = ?
    LIMIT 1
");
$noteStmt->bind_param("i", $lessonId);
$noteStmt->execute();
$noteResult = $noteStmt->get_result();

if ($noteRow = $noteResult->fetch_assoc()) {
    $noteTitle = $noteRow['note_title'];
    $noteContent = $noteRow['note_content'];
}
$noteStmt->close();

/* Get quiz for this lesson */
$quizStmt = $conn->prepare("
    SELECT quiz_id, quiz_title, pass_mark
    FROM quizzes
    WHERE lesson_id = ?
    LIMIT 1
");
$quizStmt->bind_param("i", $lessonId);
$quizStmt->execute();
$quizResult = $quizStmt->get_result();

if ($quizRow = $quizResult->fetch_assoc()) {
    $quizId = $quizRow['quiz_id'];
    $quizTitle = $quizRow['quiz_title'];
    $passMark = (int)$quizRow['pass_mark'];
}
$quizStmt->close();

/* Get quiz questions */
if ($quizId > 0) {
    $questionStmt = $conn->prepare("
        SELECT question_id, question_text, option_a, option_b, option_c, option_d, correct_option
        FROM quiz_questions
        WHERE quiz_id = ?
    ");
    $questionStmt->bind_param("i", $quizId);
    $questionStmt->execute();
    $questionResult = $questionStmt->get_result();

    while ($row = $questionResult->fetch_assoc()) {
        $questions[] = $row;
    }
    $questionStmt->close();
}

/* Check if already passed quiz before */
if ($quizId > 0) {
    $attemptCheckStmt = $conn->prepare("
        SELECT score, status
        FROM quiz_attempts
        WHERE user_id = ? AND quiz_id = ?
        ORDER BY attempted_at DESC
        LIMIT 1
    ");
    $attemptCheckStmt->bind_param("ii", $userId, $quizId);
    $attemptCheckStmt->execute();
    $attemptCheckResult = $attemptCheckStmt->get_result();

    if ($attemptRow = $attemptCheckResult->fetch_assoc()) {
        if ($attemptRow['status'] === 'Pass') {
            $quizPassed = true;
            $quizMessage = "You already passed this quiz. Score: " . $attemptRow['score'] . "%";
        }
    }
    $attemptCheckStmt->close();
}

/* Submit quiz */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_quiz'])) {
    if ($quizId > 0 && count($questions) > 0) {
        $correctCount = 0;
        $totalQuestions = count($questions);
        $quizSubmitted = true;

        foreach ($questions as $question) {
            $questionId = $question['question_id'];
            $selected = $_POST['answer_' . $questionId] ?? '';
            $correct = $question['correct_option'];
            $isCorrect = ($selected === $correct);

            if ($isCorrect) {
                $correctCount++;
            }

            $answerResults[] = [
                'question_text' => $question['question_text'],
                'selected' => $selected,
                'correct' => $correct,
                'isCorrect' => $isCorrect,
                'option_a' => $question['option_a'],
                'option_b' => $question['option_b'],
                'option_c' => $question['option_c'],
                'option_d' => $question['option_d']
            ];
        }

        $score = round(($correctCount / $totalQuestions) * 100);
        $status = ($score >= $passMark) ? 'Pass' : 'Fail';

        $insertAttemptStmt = $conn->prepare("
            INSERT INTO quiz_attempts (user_id, quiz_id, score, status, attempted_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $insertAttemptStmt->bind_param("iiis", $userId, $quizId, $score, $status);
        $insertAttemptStmt->execute();
        $insertAttemptStmt->close();

        if ($status === 'Pass') {
            $quizPassed = true;
            $quizMessage = "Congratulations! You passed the quiz with " . $score . "%.";
        } else {
            $quizPassed = false;
            $quizMessage = "You scored " . $score . "%. You need at least " . $passMark . "% to pass.";
        }
    }
}

/* Complete lesson */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['complete_lesson'])) {
    if ($quizPassed || $quizId == 0) {
        $checkProgressStmt = $conn->prepare("
            SELECT progress_id
            FROM lesson_progress
            WHERE user_id = ? AND lesson_id = ?
        ");
        $checkProgressStmt->bind_param("ii", $userId, $lessonId);
        $checkProgressStmt->execute();
        $checkProgressResult = $checkProgressStmt->get_result();

        if ($checkProgressResult->num_rows > 0) {
            $updateProgressStmt = $conn->prepare("
                UPDATE lesson_progress
                SET status = 'Completed', completed_at = NOW()
                WHERE user_id = ? AND lesson_id = ?
            ");
            $updateProgressStmt->bind_param("ii", $userId, $lessonId);
            $updateProgressStmt->execute();
            $updateProgressStmt->close();
        } else {
            $insertProgressStmt = $conn->prepare("
                INSERT INTO lesson_progress (user_id, lesson_id, status, completed_at)
                VALUES (?, ?, 'Completed', NOW())
            ");
            $insertProgressStmt->bind_param("ii", $userId, $lessonId);
            $insertProgressStmt->execute();
            $insertProgressStmt->close();
        }
        $checkProgressStmt->close();

        $totalLessonsStmt = $conn->prepare("
            SELECT COUNT(*) AS total_lessons
            FROM lessons
            WHERE course_id = ?
        ");
        $totalLessonsStmt->bind_param("i", $courseId);
        $totalLessonsStmt->execute();
        $totalLessonsResult = $totalLessonsStmt->get_result();
        $totalLessons = 0;

        if ($row = $totalLessonsResult->fetch_assoc()) {
            $totalLessons = (int)$row['total_lessons'];
        }
        $totalLessonsStmt->close();

        $completedLessonsStmt = $conn->prepare("
            SELECT COUNT(*) AS completed_lessons
            FROM lesson_progress lp
            INNER JOIN lessons l ON lp.lesson_id = l.lesson_id
            WHERE lp.user_id = ? AND l.course_id = ? AND lp.status = 'Completed'
        ");
        $completedLessonsStmt->bind_param("ii", $userId, $courseId);
        $completedLessonsStmt->execute();
        $completedLessonsResult = $completedLessonsStmt->get_result();
        $completedLessons = 0;

        if ($row = $completedLessonsResult->fetch_assoc()) {
            $completedLessons = (int)$row['completed_lessons'];
        }
        $completedLessonsStmt->close();

        $progressPercentage = 0;
        if ($totalLessons > 0) {
            $progressPercentage = round(($completedLessons / $totalLessons) * 100);
        }

        $enrollmentCheckStmt = $conn->prepare("
            SELECT enrollment_id
            FROM enrollments
            WHERE user_id = ? AND course_id = ?
        ");
        $enrollmentCheckStmt->bind_param("ii", $userId, $courseId);
        $enrollmentCheckStmt->execute();
        $enrollmentCheckResult = $enrollmentCheckStmt->get_result();

        if ($enrollmentCheckResult->num_rows > 0) {
            $updateEnrollStmt = $conn->prepare("
                UPDATE enrollments
                SET progress = ?
                WHERE user_id = ? AND course_id = ?
            ");
            $updateEnrollStmt->bind_param("iii", $progressPercentage, $userId, $courseId);
            $updateEnrollStmt->execute();
            $updateEnrollStmt->close();
        }
        $enrollmentCheckStmt->close();

        header("Location: resume_course.php?course_id=" . $courseId);
        exit();
    } else {
        $quizMessage = "You must pass the quiz before completing this lesson.";
    }
}

function getOptionText($result, $optionKey) {
    switch ($optionKey) {
        case 'A': return $result['option_a'];
        case 'B': return $result['option_b'];
        case 'C': return $result['option_c'];
        case 'D': return $result['option_d'];
        default: return 'No answer selected';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lesson Page</title>
    <link rel="stylesheet" href="../css/resume_course.css">
    <link rel="stylesheet" href="../css/lesson.css">
</head>
<body>
    <div class="resume-page">
        <div class="resume-container">
            <div class="lesson-topbar">
                <a href="resume_course.php?course_id=<?php echo $courseId; ?>" class="primary-btn">Back</a>
            </div>

            <div class="lesson-box">
                <h1 class="lesson-title"><?php echo htmlspecialchars($lessonTitle); ?></h1>

                <div class="lesson-section">
                    <h2><?php echo htmlspecialchars($noteTitle ?: 'Lesson Notes'); ?></h2>
                    <p><?php echo htmlspecialchars($noteContent ?: 'No lesson notes available yet.'); ?></p>
                </div>

                <?php if ($quizId > 0 && count($questions) > 0) { ?>
                    <div class="lesson-section quiz-box">
                        <h2><?php echo htmlspecialchars($quizTitle); ?></h2>

                        <?php if ($quizMessage != "") { ?>
                            <div class="message-box <?php echo $quizPassed ? 'success-box' : 'error-box'; ?>">
                                <?php echo htmlspecialchars($quizMessage); ?>
                            </div>
                        <?php } ?>

                        <?php if (!$quizSubmitted && !$quizPassed) { ?>
                            <form method="POST">
                                <?php foreach ($questions as $index => $question) { ?>
                                    <div class="quiz-question">
                                        <h4><?php echo ($index + 1) . ". " . htmlspecialchars($question['question_text']); ?></h4>

                                        <label class="quiz-option">
                                            <input type="radio" name="answer_<?php echo $question['question_id']; ?>" value="A" required>
                                            A. <?php echo htmlspecialchars($question['option_a']); ?>
                                        </label>

                                        <label class="quiz-option">
                                            <input type="radio" name="answer_<?php echo $question['question_id']; ?>" value="B">
                                            B. <?php echo htmlspecialchars($question['option_b']); ?>
                                        </label>

                                        <label class="quiz-option">
                                            <input type="radio" name="answer_<?php echo $question['question_id']; ?>" value="C">
                                            C. <?php echo htmlspecialchars($question['option_c']); ?>
                                        </label>

                                        <label class="quiz-option">
                                            <input type="radio" name="answer_<?php echo $question['question_id']; ?>" value="D">
                                            D. <?php echo htmlspecialchars($question['option_d']); ?>
                                        </label>
                                    </div>
                                <?php } ?>

                                <button type="submit" name="submit_quiz" class="primary-btn">Submit Quiz</button>
                            </form>
                        <?php } ?>

                        <?php if ($quizSubmitted && count($answerResults) > 0) { ?>
                            <div class="lesson-section">
                                <h2>Quiz Review</h2>

                                <?php foreach ($answerResults as $index => $result) { ?>
                                    <div class="quiz-review-box">
                                        <h4><?php echo ($index + 1) . ". " . htmlspecialchars($result['question_text']); ?></h4>

                                        <p>
                                            <strong>Your Answer:</strong>
                                            <?php echo htmlspecialchars($result['selected']); ?>.
                                            <?php echo htmlspecialchars(getOptionText($result, $result['selected'])); ?>
                                            <?php echo $result['isCorrect'] ? '✅' : '❌'; ?>
                                        </p>

                                        <?php if (!$result['isCorrect']) { ?>
                                            <p>
                                                <strong>Correct Answer:</strong>
                                                <?php echo htmlspecialchars($result['correct']); ?>.
                                                <?php echo htmlspecialchars(getOptionText($result, $result['correct'])); ?> ✅
                                            </p>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>

                <div class="lesson-actions">
                    <form method="POST" class="inline-form">
                        <button type="submit" name="complete_lesson" class="primary-btn">Complete Lesson</button>
                    </form>

                    <a href="resume_course.php?course_id=<?php echo $courseId; ?>" class="secondary-btn">Return to Resume Page</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>