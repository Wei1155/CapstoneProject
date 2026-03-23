import java.sql.*;
import java.util.*;

public class SecuritySystem {
    private final String url = "jdbc:mysql://localhost:3306/lms_db";
    private final String user = "root";
    private final String password = "";
    private Map<String, String> currentUser = null;

    public void setSession(Map<String, String> userDict) {
        this.currentUser = userDict;
    }

    private boolean checkAccess(String[] allowedRoles) {
        if (currentUser == null) {
            System.out.println(" Access Denied: Please login first.");
            return false;
        }
        String userRole = currentUser.getOrDefault("user_type", "unknown").toLowerCase();
        for (String role : allowedRoles) {
            if (role.toLowerCase().equals(userRole)) return true;
        }
        System.out.println(" Permission Denied: " + userRole + " cannot access this function.");
        return false;
    }

    public void updateSetting(String key, String value) {
        if (!checkAccess(new String[]{"admin"})) return;
        String sql = "INSERT INTO system_settings (config_key, config_value) VALUES (?, ?) " +
                     "ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)";
        try (Connection conn = DriverManager.getConnection(url, user, password);
             PreparedStatement pstmt = conn.prepareStatement(sql)) {
            pstmt.setString(1, key);
            pstmt.setString(2, value);
            pstmt.executeUpdate();
            System.out.println("⚙️ Config Updated: " + key + " = " + value);
        } catch (SQLException e) { e.printStackTrace(); }
    }

    public void viewAllSettings() {
        System.out.println("\n" + "-".repeat(15) + " SYSTEM CONFIGURATION " + "-".repeat(15));
        String sql = "SELECT * FROM system_settings";
        try (Connection conn = DriverManager.getConnection(url, user, password);
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {
            while (rs.next()) {
                System.out.println(rs.getString("config_key") + ": " + rs.getString("config_value"));
            }
        } catch (SQLException e) { e.printStackTrace(); }
        System.out.println("-".repeat(55));
    }

    public String getSetting(String key) {
        String sql = "SELECT config_value FROM system_settings WHERE config_key = ?";
        try (Connection conn = DriverManager.getConnection(url, user, password);
             PreparedStatement pstmt = conn.prepareStatement(sql)) {
            pstmt.setString(1, key);
            ResultSet rs = pstmt.executeQuery();
            if (rs.next()) return rs.getString("config_value");
        } catch (SQLException e) { e.printStackTrace(); }
        return null;
    }
}