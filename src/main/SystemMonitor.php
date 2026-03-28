<?php
class SystemMonitor {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function logActivity($userId, $activityText) {
        $stmt = $this->conn->prepare("
            INSERT INTO activity_logs (user_id, activity_text, created_at)
            VALUES (?, ?, NOW())
        ");

        if (!$stmt) {
            die("Log Error: " . $this->conn->error);
        }

        $stmt->bind_param("is", $userId, $activityText);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function reportIssue($userId, $userName, $issueType, $description) {
        $stmt = $this->conn->prepare("
            INSERT INTO issue_reports (user_id, user_name, issue_type, description, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");

        if (!$stmt) {
            die("Report Error: " . $this->conn->error);
        }

        $stmt->bind_param("isss", $userId, $userName, $issueType, $description);

        if ($stmt->execute()) {
            $stmt->close();

            $this->logActivity($userId, "Reported issue: " . $issueType);

            /* notify only ONE admin */
            $adminStmt = $this->conn->prepare("
                SELECT user_id
                FROM users
                WHERE role = 'Admin'
                ORDER BY user_id ASC
                LIMIT 1
            ");

            if ($adminStmt) {
                $adminStmt->execute();
                $adminResult = $adminStmt->get_result();

                if ($adminRow = $adminResult->fetch_assoc()) {
                    $adminId = $adminRow['user_id'];
                    $message = $userName . " reported a new issue (" . $issueType . ").";

                    $notifStmt = $this->conn->prepare("
                        INSERT INTO notifications (user_id, message, is_read, created_at)
                        VALUES (?, ?, 0, NOW())
                    ");

                    if ($notifStmt) {
                        $notifStmt->bind_param("is", $adminId, $message);
                        $notifStmt->execute();
                        $notifStmt->close();
                    }
                }

                $adminStmt->close();
            }

            return true;
        }

        $stmt->close();
        return false;
    }

    public function getAllLogs() {
        $result = $this->conn->query("
            SELECT 
                activity_logs.log_id,
                activity_logs.user_id,
                activity_logs.activity_text,
                activity_logs.created_at,
                users.username,
                users.first_name,
                users.last_name,
                users.role
            FROM activity_logs
            LEFT JOIN users ON activity_logs.user_id = users.user_id
            ORDER BY activity_logs.created_at DESC
        ");

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getUserLogs($userId) {
        $stmt = $this->conn->prepare("
            SELECT 
                activity_logs.log_id,
                activity_logs.user_id,
                activity_logs.activity_text,
                activity_logs.created_at,
                users.username,
                users.first_name,
                users.last_name,
                users.role
            FROM activity_logs
            LEFT JOIN users ON activity_logs.user_id = users.user_id
            WHERE activity_logs.user_id = ?
            ORDER BY activity_logs.created_at DESC
        ");

        if (!$stmt) {
            die("User Log Error: " . $this->conn->error);
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $logs = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $logs;
    }

    public function getAllReports() {
        $result = $this->conn->query("
            SELECT *
            FROM issue_reports
            ORDER BY created_at DESC
        ");

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function resolveIssue($issueId, $adminId, $adminName, $responseText) {
        $issueStmt = $this->conn->prepare("
            SELECT user_id, issue_type
            FROM issue_reports
            WHERE issue_id = ?
        ");

        if (!$issueStmt) {
            die("Resolve Fetch Error: " . $this->conn->error);
        }

        $issueStmt->bind_param("i", $issueId);
        $issueStmt->execute();
        $issueResult = $issueStmt->get_result();
        $issue = $issueResult->fetch_assoc();
        $issueStmt->close();

        if (!$issue) {
            return false;
        }

        $fullResponse = "[" . $adminName . "]: " . $responseText;

        $stmt = $this->conn->prepare("
            UPDATE issue_reports
            SET status = 'Resolved', admin_response = ?
            WHERE issue_id = ?
        ");

        if (!$stmt) {
            die("Resolve Error: " . $this->conn->error);
        }

        $stmt->bind_param("si", $fullResponse, $issueId);

        if ($stmt->execute()) {
            $stmt->close();

            $this->logActivity($adminId, "Resolved Issue ID: " . $issueId);

            $message = "Your issue (" . $issue['issue_type'] . ") has been resolved by admin. Response: " . $responseText;

            $notifStmt = $this->conn->prepare("
                INSERT INTO notifications (user_id, message, is_read, created_at)
                VALUES (?, ?, 0, NOW())
            ");

            if (!$notifStmt) {
                die("Notification Error: " . $this->conn->error);
            }

            $notifStmt->bind_param("is", $issue['user_id'], $message);
            $notifStmt->execute();
            $notifStmt->close();

            return true;
        }

        $stmt->close();
        return false;
    }

    public function deleteIssue($issueId, $adminId) {
        $stmt = $this->conn->prepare("
            DELETE FROM issue_reports
            WHERE issue_id = ?
        ");

        if (!$stmt) {
            die("Delete Error: " . $this->conn->error);
        }

        $stmt->bind_param("i", $issueId);

        if ($stmt->execute()) {
            $stmt->close();
            $this->logActivity($adminId, "Deleted Issue ID: " . $issueId);
            return true;
        }

        $stmt->close();
        return false;
    }
}
?>