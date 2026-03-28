<?php

class CourseManager {
    private $conn;
    private $gamification;

    public function __construct($conn, $gamification_system) {
        $this->conn = $conn;
        $this->gamification = $gamification_system; // Link to Process 3.0
    }

    /**
     * 2.1 Lesson & Module Editor
     */
    public function mark_lesson_complete($student_id, $lesson_id, $course_id) {
        // 1. Update the lesson_progress table
        $stmt = $this->conn->prepare("
            INSERT INTO lesson_progress (user_id, lesson_id, status, completed_at)
            VALUES (?, ?, 'Completed', NOW())
            ON DUPLICATE KEY UPDATE status = 'Completed'
        ");
        $stmt->bind_param("ii", $student_id, $lesson_id);
        $stmt->execute();
        $stmt->close();

        // 2. Log the activity in the engine
        $this->gamification->create_activity_log($student_id, "Completed Lesson ID: " . $lesson_id);

        // 3. Trigger the Quest Check
        return $this->gamification->complete_task($student_id, 1); 
    }

    /**
     * 2.1 Lesson & Module Editor - Quiz Component
     */
    public function submit_quiz_attempt($student_id, $quiz_id, $score) {
        $status = ($score >= 50) ? 'Pass' : 'Fail';


        $stmt = $this->conn->prepare("
            INSERT INTO quiz_attempts (user_id, quiz_id, score, status, attempted_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("iiis", $student_id, $quiz_id, $score, $status);
        $stmt->execute();
        $stmt->close();

        if ($status === 'Pass') {
            return $this->gamification->complete_task($student_id, 2);
        }
        
        return "Quiz recorded. Try again to pass and earn rewards!";
    }

    /**
     * 2.3 Course Category Organization
     */
    public function get_courses_by_category($category_name) {
        $courses = [];
        $stmt = $this->conn->prepare("SELECT * FROM courses WHERE category = ?");
        $stmt->bind_param("s", $category_name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        
        $stmt->close();
        return $courses;
    }
}
?>