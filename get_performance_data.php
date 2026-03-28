<?php
include "db_connection.php";
include "analytics.php";
session_start();

$user_id = $_SESSION['user_id'];

// User name & Pfp
$sql = "SELECT username, profile_picture FROM users 
        WHERE user_id = $user_id";
$userinfo_result = $conn->query($sql);
$user = $userinfo_result->fetch_assoc();
$username = $user['username'];
$profile_pic = $user['profile_picture'];

// Instructor ID
$sql = "SELECT instructor_id 
        FROM instructors 
        WHERE user_id = $user_id";
$result = $conn->query($sql);
$instructor = $result->fetch_assoc();
$instructor_id = $instructor['instructor_id'];
if (!$instructor_id) {
    die("Instructor not found.");
}

// Course info
$sql = "SELECT course_title, category, created_at, level, rating, course_image 
        FROM courses WHERE instructor_id = $instructor_id";
$courseinfo_results = $conn->query($sql);
$all_courses = $courseinfo_results->num_rows; 

// Total courses
$sql = "SELECT COUNT(*) as total
        FROM courses WHERE instructor_id = $instructor_id";
$coursecount_results = $conn->query($sql);
$total_courses = $coursecount_results->fetch_assoc()['total'] ?? 0;

// Enrolled students
$sql = "SELECT COUNT(DISTINCT e.user_id) as enrolled
        FROM enrollments e
        JOIN courses c ON c.course_id = e.course_id
        WHERE c.instructor_id = $instructor_id";
$enrolled_result = $conn->query($sql);
$enroll = $enrolled_result->fetch_assoc();
$enrolled = $enroll['enrolled'] ?? 0;

// Completed students (all quizzes completed in a course)
$sql = "SELECT COUNT(DISTINCT e.user_id) AS completed
        FROM enrollments e
        JOIN courses c ON e.course_id = c.course_id
        WHERE c.instructor_id = $instructor_id
        AND NOT EXISTS (
        SELECT 1
    FROM quizzes q
    LEFT JOIN quiz_results qr 
      ON qr.quiz_id = q.quiz_id AND qr.user_id = e.user_id
    WHERE q.course_id = e.course_id AND qr.result_id IS NULL)";
$completed_result = $conn->query($sql);
$complete = $completed_result->fetch_assoc();
$completed = $complete['completed'] ?? 0;

$engagement = engagementLevel($enrolled, $completed);

// Overall average score between all courses
$sql = "SELECT AVG(qr.score) as avg_score
        FROM quiz_results qr
        JOIN quizzes q ON qr.quiz_id = q.quiz_id
        JOIN courses c ON q.course_id = c.course_id
        WHERE c.instructor_id = $instructor_id
          AND qr.score IS NOT NULL";
$result = $conn->query($sql);
$data = $result->fetch_assoc();
$overall_avg_score = $data['avg_score'] ?? 0;

// Total student count
$sql = "SELECT COUNT(e.user_id) as student_count
        FROM enrollments e
        JOIN courses c ON c.course_id = e.course_id
        WHERE c.instructor_id = $instructor_id";
$count_result = $conn->query($sql);
$count = $count_result->fetch_assoc();
$student_count = $count['student_count'] ?? 0;
$result = $conn->query($sql);

?>

