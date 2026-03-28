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

// Student progress (Level, XP, Rank Place, Total Bagdes)
$sql = "SELECT level, xp, rank_position, badges_count FROM student_progress 
        WHERE user_id = $user_id";
$progressinfo_result = $conn->query($sql);

$progress = $progressinfo_result->fetch_assoc();

$rank_position = $progress['rank_position'];
$badges_count = $progress['badges_count'];
$level = $progress['level'];
$xp = $progress['xp'];
$result = calcXpPercentage($xp, $level);

$new_xp = $result['xp'];
$new_level = $result['level'];
$xp_percent = $result['xp_percent'];

$stmt = $conn->prepare("UPDATE student_progress SET xp = ?, level = ? WHERE user_id = ?");
$stmt->bind_param("iii", $new_xp, $new_level, $user_id);
$stmt->execute();

$stmt->close();


// Latest Badges (Name, Icon)
$sql = "SELECT badge_name, badge_icon FROM badges b 
        JOIN student_badges sb ON sb.badge_id = b.badge_id 
        WHERE sb.user_id = $user_id AND sb.badge_id = b.badge_id
        ORDER BY sb.earned_at DESC
        LIMIT 6";
$badgeinfo_result = $conn->query($sql);


// Total Quiz completed + average score
$sql = "SELECT COUNT(*) as total, AVG(score) as avg_score 
        FROM quiz_results 
        WHERE user_id = $user_id";
        
$quizinfo_result = $conn->query($sql);
$quiz = $quizinfo_result->fetch_assoc();

$totalq = $quiz['total'];
$avg_score = $quiz['avg_score'];

// Most enrolled course category & best average score on them
$sql = "SELECT c.category, 
               COUNT(DISTINCT e.course_id) AS total_courses,
               AVG(qr.score) AS avg_score
        FROM enrollments e
        JOIN courses c ON e.course_id = c.course_id
        LEFT JOIN quizzes q ON c.course_id = q.course_id
        LEFT JOIN quiz_results qr 
            ON q.quiz_id = qr.quiz_id 
            AND qr.user_id = e.user_id
        WHERE e.user_id = $user_id
        GROUP BY c.category
        ORDER BY total_courses DESC
        LIMIT 1";

$extrainfo_result = $conn->query($sql);

if ($extrainfo_result && $extrainfo_result->num_rows > 0) {
    $extradata = $extrainfo_result->fetch_assoc();

    $top_category = $extradata['category'];
    $total_courses = $extradata['total_courses'];
    $best_avg_score = $extradata['avg_score'];
} else {
    $top_category = "None";
    $total_courses = 0;
    $best_avg_score = 0;
}
?>