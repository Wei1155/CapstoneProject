<?php
class SecuritySystem {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function checkAccess($allowedRoles) {
        if (!isset($_SESSION['role'])) {
            return false;
        }

        $userRole = strtolower(trim($_SESSION['role']));

        foreach ($allowedRoles as $role) {
            if (strtolower(trim($role)) === $userRole) {
                return true;
            }
        }

        return false;
    }

    public function updateSetting($key, $value) {
        if (!$this->checkAccess(['Admin'])) {
            return [
                "success" => false,
                "msg" => "No Permission"
            ];
        }

        $stmt = $this->conn->prepare("
            INSERT INTO system_settings (config_key, config_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)
        ");

        if (!$stmt) {
            return [
                "success" => false,
                "msg" => "Database Error: " . $this->conn->error
            ];
        }

        $stmt->bind_param("ss", $key, $value);

        if ($stmt->execute()) {
            $stmt->close();
            return [
                "success" => true,
                "msg" => "Update Successful"
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return [
                "success" => false,
                "msg" => "Database Error: " . $error
            ];
        }
    }

    public function getAllSettings() {
        $result = $this->conn->query("
            SELECT *
            FROM system_settings
            ORDER BY config_key ASC
        ");

        if (!$result) {
            return [];
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getSetting($key) {
        $stmt = $this->conn->prepare("
            SELECT config_value
            FROM system_settings
            WHERE config_key = ?
        ");

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row ? $row['config_value'] : null;
    }
}
?>