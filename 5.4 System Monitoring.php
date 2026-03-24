<?php
class SystemMonitor {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function logActivity($userName, $userRole, $action) {
        $role = (empty(trim($userRole))) ? "Unknown" : trim($userRole);
        $formattedRole = ucfirst(strtolower($role));
        
        $sql = "INSERT INTO activity_logs (user_name, user_role, action, created_at) VALUES (?, ?, ?, NOW())";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$userName, $formattedRole, $action]);
        } catch (PDOException $e) {
            error_log("Log Error: " . $e->getMessage());
            return false;
        }
    }

    public function reportIssue($userName, $issueType, $description) {
        $sql = "INSERT INTO issue_reports (user_name, issue_type, description, created_at) VALUES (?, ?, ?, NOW())";
        try {
            $stmt = $this->pdo->prepare($sql);
            if ($stmt->execute([$userName, $issueType, $description])) {
                
                $this->logActivity($userName, "Student", "Reported issue: $issueType");
                return true;
            }
        } catch (PDOException $e) {
            error_log("Report Error: " . $e->getMessage());
        }
        return false;
    }

    public function getAllLogs() {
        return $this->pdo->query("SELECT * FROM activity_logs ORDER BY created_at DESC")->fetchAll();
    }

    public function getAllReports() {
        return $this->pdo->query("SELECT * FROM issue_reports ORDER BY created_at DESC")->fetchAll();
    }

    public function resolveIssue($issueId, $adminName, $responseText) {
        $fullResponse = "[$adminName]: $responseText";
        $sql = "UPDATE issue_reports SET status = 'Resolved', admin_response = ? WHERE issue_id = ?";
        try {
            $stmt = $this->pdo->prepare($sql);
            if ($stmt->execute([$fullResponse, $issueId])) {
                $this->logActivity($adminName, "Admin", "Resolved Issue ID: $issueId");
                return true;
            }
        } catch (PDOException $e) { error_log("Resolve Error: " . $e->getMessage()); }
        return false;
    }

    public function deleteIssue($issueId, $adminName) {
        $sql = "DELETE FROM issue_reports WHERE issue_id = ?";
        try {
            $stmt = $this->pdo->prepare($sql);
            if ($stmt->execute([$issueId])) {
                $this->logActivity($adminName, "Admin", "Deleted Issue ID: $issueId");
                return true;
            }
        } catch (PDOException $e) { error_log("Delete Error: " . $e->getMessage()); }
        return false;
    }
}
?>