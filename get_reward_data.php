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
// Badge info
$sql = "SELECT badge_id, badge_name, badge_description, badge_icon FROM badges";
$badgesinfo_results = $conn->query($sql);
// $badges = $badgesinfo_results->num_rows;
$all_badges = [];
while ($row = $badgesinfo_results->fetch_assoc()) {
    $all_badges[] = $row;
}

// Student badge info
$sql = "SELECT badge_id, earned_at FROM student_badges WHERE user_id = $user_id";
$studentbadgesinfo_results = $conn->query($sql);
// $studentbadges = $studentbadgesinfo_results->num_rows;
$earned_badges = [];

while ($row = $studentbadgesinfo_results->fetch_assoc()) {
    $earned_badges[] = $row['badge_id'];
}

// FOR BADGE CRITERIA
// Progress Info
$sql = "SELECT level, rank_position FROM student_progress 
        WHERE user_id = $user_id";
$progressinfo_result = $conn->query($sql);

$progress = $progressinfo_result->fetch_assoc() ?? [];

$rank_position = $progress['rank_position'] ?? 9999;
$level = $progress['level'] ?? 0;

// Course Count
$sql = "SELECT COUNT(*) AS total_courses 
        FROM enrollments 
        WHERE user_id = $user_id";
$courseresult = $conn->query($sql);
$count = $courseresult->fetch_assoc();
$total_courses = $count['total_courses'] ?? 0;

// Quiz Info (score, count)
$sql = "SELECT COUNT(*) AS quiz_count, AVG(score) AS avg_score, MAX(score) as highest
        FROM quiz_results 
        WHERE user_id = $user_id";
$quizresult = $conn->query($sql);
$quiz = $quizresult->fetch_assoc();
$quiz_count = $quiz['quiz_count'] ?? 0;
$avg_score = $quiz['avg_score'] ?? 0;
$highest_score = $quiz['highest'] ?? 0;


$eligible_badges = [];
foreach ($all_badges as $badge) {

    $badge_id = $badge['badge_id'];
    $isEligible = false;

    switch ($badge_id) {
        case 1: // Level badge
            if ($level >= 5) $isEligible = true;
            break;
        case 2: // Courses badge
            if ($total_courses >= 3) $isEligible = true;
            break;
        case 3: // Avg score badge
            if ($avg_score >= 80) $isEligible = true;
            break;
        case 4: // Perfect score badge
            if ($highest_score == 100) $isEligible = true;
            break;
        case 5: // Rank badge
            if ($rank_position <= 10) $isEligible = true;
            break;
    }
    if ($isEligible) {
        $eligible_badges[] = $badge_id;
    }
}
