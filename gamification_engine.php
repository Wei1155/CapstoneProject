<?php
// ==========================================
// MOCK DATABASE & DATA (For testing purposes)
// ==========================================

$available_badges = [
    ["name" => "First Steps", "points_required" => 50],
    ["name" => "Rising Scholar", "points_required" => 150],
    ["name" => "Quiz Master", "points_required" => 300]
];

// ==========================================
// MODULE 3.4: ACTIVITY LOG
// ==========================================

function create_activity_log($student_id, $action_description) {
    // Gets the current date and time
    $timestamp = date("Y-m-d H:i:s");
    
    // In the real system, your leader will connect this to the SQL database using:
    // INSERT INTO Activity_Log (StudentID, ActionDescription) VALUES (...)
    
    echo "[LOG] $timestamp | Student ID: $student_id | Action: $action_description\n";
}

// ==========================================
// MODULE 3.3: REWARDS & BADGES
// ==========================================

function check_for_new_badges(&$student, $available_badges) {
    foreach ($available_badges as $badge) {
        // If they have enough points AND don't already have the badge
        if ($student['total_points'] >= $badge['points_required'] && !in_array($badge['name'], $student['badges'])) {
            $student['badges'][] = $badge['name']; // Give them the badge
            create_activity_log($student['student_id'], "Earned Badge: " . $badge['name']);
        }
    }
}

function award_points(&$student, $points_earned, $available_badges) {
    $student['total_points'] += $points_earned;
    
    // Calculate level (every 100 points is 1 level)
    $new_level = intdiv($student['total_points'], 100) + 1;
    
    if ($new_level > $student['current_level']) {
        $student['current_level'] = $new_level;
        create_activity_log($student['student_id'], "Leveled up to Level $new_level!");
    }
    
    // Check if these new points unlocked any badges
    check_for_new_badges($student, $available_badges);
    
    // In the real app, you would run an SQL UPDATE query here to save the new points to the database
}

// ==========================================
// MODULE 3.2: QUESTS AND CHALLENGES
// ==========================================

function complete_task(&$student, $task, $available_badges) {
    // Check if the student has NOT completed this task yet
    if (!in_array($task['task_id'], $student['completed_tasks'])) {
        
        // Mark it as completed so they can't farm points
        $student['completed_tasks'][] = $task['task_id'];
        
        // Log the completion
        create_activity_log($student['student_id'], "Completed Task: " . $task['name']);
        
        // Give them their points!
        award_points($student, $task['points_reward'], $available_badges);
        
        return "Success! Reward claimed.\n";
    } else {
        return "You have already claimed the reward for this task.\n";
    }
}

?>