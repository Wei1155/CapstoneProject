<?php
class SecuritySystem {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        if (session_status() == PHP_SESSION_NONE) session_start();
    }

    public function checkAccess($allowedRoles) {
        if (!isset($_SESSION['user_type'])) return false;
        $userRole = strtolower($_SESSION['user_type']);
        foreach ($allowedRoles as $role) {
            if (strtolower($role) === $userRole) return true;
        }
        return false;
    }

    public function updateSetting($key, $value) {
        if (!$this->checkAccess(['admin'])) return ["success" => false, "msg" => "No Permission"];

        $sql = "INSERT INTO system_settings (config_key, config_value) VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$key, $value]);
            return ["success" => true, "msg" => "Update Successful"];
        } catch (PDOException $e) {
            error_log("Settings Update Error: " . $e->getMessage());
            return ["success" => false, "msg" => "Database Error"];
        }
    }

    public function getAllSettings() {
        return $this->pdo->query("SELECT * FROM system_settings")->fetchAll();
    }

    public function getSetting($key) {
        $sql = "SELECT config_value FROM system_settings WHERE config_key = ?";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$key]);
            $res = $stmt->fetch();
            return $res ? $res['config_value'] : null;
        } catch (PDOException $e) { return null; }
    }
}
?>