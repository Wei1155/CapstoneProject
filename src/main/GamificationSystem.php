<?php
class GamificationSystem {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function create_activity_log($student_id, $action_description) {
        $stmt = $this->conn->prepare("
            INSERT INTO activity_logs (user_id, activity_text, created_at)
            VALUES (?, ?, NOW())
        ");

        if (!$stmt) {
            die("Activity Log Error: " . $this->conn->error);
        }

        $stmt->bind_param("is", $student_id, $action_description);
        $stmt->execute();
        $stmt->close();
    }

    public function get_student_data($student_id) {
        $stmt = $this->conn->prepare("
            SELECT progress_id, user_id, xp, level, rank_position, badges_count
            FROM student_progress
            WHERE user_id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            die("Student Progress Error: " . $this->conn->error);
        }

        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $stmt->close();

        if (!$student) {
            $insertStmt = $this->conn->prepare("
                INSERT INTO student_progress (user_id, xp, level, rank_position, badges_count)
                VALUES (?, 0, 1, 0, 0)
            ");

            if (!$insertStmt) {
                die("Student Progress Insert Error: " . $this->conn->error);
            }

            $insertStmt->bind_param("i", $student_id);
            $insertStmt->execute();
            $insertStmt->close();

            return [
                "progress_id" => 0,
                "user_id" => $student_id,
                "xp" => 0,
                "level" => 1,
                "rank_position" => 0,
                "badges_count" => 0
            ];
        }

        return $student;
    }

    public function get_student_badges($student_id) {
        $badges = [];

        $stmt = $this->conn->prepare("
            SELECT b.badge_name
            FROM student_badges sb
            INNER JOIN badges b ON sb.badge_id = b.badge_id
            WHERE sb.user_id = ?
        ");

        if (!$stmt) {
            die("Student Badge Query Error: " . $this->conn->error);
        }

        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $badges[] = $row['badge_name'];
        }

        $stmt->close();
        return $badges;
    }

    public function update_badge_count($student_id) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS total_badges
            FROM student_badges
            WHERE user_id = ?
        ");

        if (!$stmt) {
            die("Badge Count Error: " . $this->conn->error);
        }

        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $badgeCount = 0;

        if ($row = $result->fetch_assoc()) {
            $badgeCount = (int)$row['total_badges'];
        }

        $stmt->close();

        $updateStmt = $this->conn->prepare("
            UPDATE student_progress
            SET badges_count = ?
            WHERE user_id = ?
        ");

        if (!$updateStmt) {
            die("Badge Count Update Error: " . $this->conn->error);
        }

        $updateStmt->bind_param("ii", $badgeCount, $student_id);
        $updateStmt->execute();
        $updateStmt->close();
    }

    public function award_points($student_id, $points_earned) {
        $student = $this->get_student_data($student_id);

        $newXp = $student['xp'] + (int)$points_earned;
        $newLevel = intdiv($newXp, 100) + 1;

        $stmt = $this->conn->prepare("
            UPDATE student_progress
            SET xp = ?, level = ?
            WHERE user_id = ?
        ");

        if (!$stmt) {
            die("Award Points Error: " . $this->conn->error);
        }

        $stmt->bind_param("iii", $newXp, $newLevel, $student_id);
        $stmt->execute();
        $stmt->close();

        if ($newLevel > $student['level']) {
            $this->create_activity_log($student_id, "Leveled up to Level " . $newLevel . "!");
        }
    }

    public function give_quest_badge($student_id, $badge_name) {
        if (trim($badge_name) === "") {
            return;
        }

        $earnedBadges = $this->get_student_badges($student_id);

        if (in_array($badge_name, $earnedBadges)) {
            return;
        }

        $badgeStmt = $this->conn->prepare("
            SELECT badge_id, badge_name
            FROM badges
            WHERE badge_name = ?
            LIMIT 1
        ");

        if (!$badgeStmt) {
            die("Quest Badge Query Error: " . $this->conn->error);
        }

        $badgeStmt->bind_param("s", $badge_name);
        $badgeStmt->execute();
        $badgeResult = $badgeStmt->get_result();
        $badge = $badgeResult->fetch_assoc();
        $badgeStmt->close();

        if ($badge) {
            $insertStmt = $this->conn->prepare("
                INSERT INTO student_badges (user_id, badge_id, earned_at)
                VALUES (?, ?, NOW())
            ");

            if (!$insertStmt) {
                die("Quest Badge Insert Error: " . $this->conn->error);
            }

            $insertStmt->bind_param("ii", $student_id, $badge['badge_id']);
            $insertStmt->execute();
            $insertStmt->close();

            $this->create_activity_log($student_id, "Earned Badge: " . $badge['badge_name']);
            $this->update_badge_count($student_id);
        }
    }

    public function check_quest_condition($student_id, $quest_title) {
        if ($quest_title === "Complete First Lesson") {
            $stmt = $this->conn->prepare("
                SELECT progress_id
                FROM lesson_progress
                WHERE user_id = ? AND status = 'Completed'
                LIMIT 1
            ");

            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $ok = $result->num_rows > 0;
            $stmt->close();

            return $ok;
        }

        if ($quest_title === "Pass First Quiz") {
            $stmt = $this->conn->prepare("
                SELECT attempt_id
                FROM quiz_attempts
                WHERE user_id = ? AND status = 'Pass'
                LIMIT 1
            ");

            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $ok = $result->num_rows > 0;
            $stmt->close();

            return $ok;
        }

        if ($quest_title === "Finish One Course") {
            $stmt = $this->conn->prepare("
                SELECT enrollment_id
                FROM enrollments
                WHERE user_id = ? AND progress = 100
                LIMIT 1
            ");

            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $ok = $result->num_rows > 0;
            $stmt->close();

            return $ok;
        }

        return false;
    }

    public function complete_task($student_id, $quest_id) {
        $questStmt = $this->conn->prepare("
            SELECT quest_id, title, xp_reward, badge_reward, status
            FROM quests
            WHERE quest_id = ? AND status = 'Active'
            LIMIT 1
        ");

        if (!$questStmt) {
            die("Quest Query Error: " . $this->conn->error);
        }

        $questStmt->bind_param("i", $quest_id);
        $questStmt->execute();
        $questResult = $questStmt->get_result();
        $quest = $questResult->fetch_assoc();
        $questStmt->close();

        if (!$quest) {
            return "Quest not found or inactive.";
        }

        if (!$this->check_quest_condition($student_id, $quest['title'])) {
            return "You have not completed the requirement yet.";
        }

        $checkStmt = $this->conn->prepare("
            SELECT student_quest_id
            FROM student_quests
            WHERE user_id = ? AND quest_id = ? AND progress_status = 'Completed'
            LIMIT 1
        ");

        if (!$checkStmt) {
            die("Student Quest Check Error: " . $this->conn->error);
        }

        $checkStmt->bind_param("ii", $student_id, $quest_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $alreadyCompleted = $checkResult->num_rows > 0;
        $checkStmt->close();

        if ($alreadyCompleted) {
            return "You have already claimed the reward for this task.";
        }

        $insertStmt = $this->conn->prepare("
            INSERT INTO student_quests (user_id, quest_id, progress_status, completed_at)
            VALUES (?, ?, 'Completed', NOW())
        ");

        if (!$insertStmt) {
            die("Student Quest Insert Error: " . $this->conn->error);
        }

        $insertStmt->bind_param("ii", $student_id, $quest_id);
        $insertStmt->execute();
        $insertStmt->close();

        $this->create_activity_log($student_id, "Completed Task: " . $quest['title']);
        $this->award_points($student_id, $quest['xp_reward']);

        if (!empty($quest['badge_reward'])) {
            $this->give_quest_badge($student_id, $quest['badge_reward']);
        }

        return "Success! Reward claimed.";
    }
}
?>