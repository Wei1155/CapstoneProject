import java.sql.*;
import java.time.LocalDateTime;

public class SystemMonitor {
    private final String url = "jdbc:mysql://localhost:3306/lms_db"; 
    private final String user = "root";
    private final String password = "";

    public void logActivity(String userName, String userRole, String action) {
        String role = (userRole == null || userRole.trim().isEmpty()) ? "Unknown" : userRole.trim();
        String formattedRole = role.substring(0, 1).toUpperCase() + role.substring(1).toLowerCase();
        
        String sql = "INSERT INTO activity_logs (user_name, user_role, action, created_at) VALUES (?, ?, ?, ?)";
        try (Connection conn = DriverManager.getConnection(url, user, password);
             PreparedStatement pstmt = conn.prepareStatement(sql)) {
            pstmt.setString(1, userName);
            pstmt.setString(2, formattedRole);
            pstmt.setString(3, action);
            pstmt.setTimestamp(4, Timestamp.valueOf(LocalDateTime.now()));
            pstmt.executeUpdate();
        } catch (SQLException e) { e.printStackTrace(); }
    }

    public void reportIssue(String userName, String issueType, String description) {
        String sql = "INSERT INTO issue_reports (user_name, issue_type, description, created_at) VALUES (?, ?, ?, ?)";
        try (Connection conn = DriverManager.getConnection(url, user, password);
             PreparedStatement pstmt = conn.prepareStatement(sql)) {
            pstmt.setString(1, userName);
            pstmt.setString(2, issueType);
            pstmt.setString(3, description);
            pstmt.setTimestamp(4, Timestamp.valueOf(LocalDateTime.now()));
            pstmt.executeUpdate();
            logActivity(userName, "Student", "Reported a " + issueType);
        } catch (SQLException e) { e.printStackTrace(); }
    }

    public void viewAllLogs() {
        System.out.println("\n" + "=".repeat(30) + " SYSTEM ACTIVITY LOGS " + "=".repeat(30));
        String sql = "SELECT * FROM activity_logs ORDER BY created_at DESC";
        try (Connection conn = DriverManager.getConnection(url, user, password);
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {
            while (rs.next()) {
                System.out.printf("[%s] %s (%s) -> %s%n", 
                    rs.getTimestamp("created_at"), rs.getString("user_name"), 
                    rs.getString("user_role"), rs.getString("action"));
            }
        } catch (SQLException e) { e.printStackTrace(); }
        System.out.println("=".repeat(85));
    }

    public void viewAllReports() {
        System.out.println("\n" + "=".repeat(35) + " ISSUE REPORTS " + "=".repeat(34));
        System.out.printf("%-4s | %-10s | %-15s | %-10s | %s%n", "ID", "User", "Type", "Status", "Response");
        System.out.println("-".repeat(85));
        String sql = "SELECT * FROM issue_reports";
        try (Connection conn = DriverManager.getConnection(url, user, password);
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {
            while (rs.next()) {
                String resp = rs.getString("admin_response");
                System.out.printf("%-4d | %-10s | %-15s | %-10s | %s%n", 
                    rs.getInt("issue_id"), rs.getString("user_name"), rs.getString("issue_type"),
                    rs.getString("status"), (resp == null ? "None" : resp));
            }
        } catch (SQLException e) { e.printStackTrace(); }
    }

    public void resolveIssue(int issueId, String adminName, String responseText) {
        String sql = "UPDATE issue_reports SET status = 'Resolved', admin_response = ? WHERE issue_id = ?";
        try (Connection conn = DriverManager.getConnection(url, user, password);
             PreparedStatement pstmt = conn.prepareStatement(sql)) {
            pstmt.setString(1, "[" + adminName + "]: " + responseText);
            pstmt.setInt(2, issueId);
            if (pstmt.executeUpdate() > 0) logActivity(adminName, "Admin", "Resolved Issue ID: " + issueId);
        } catch (SQLException e) { e.printStackTrace(); }
    }

    public void deleteIssue(int issueId, String adminName) {
        String sql = "DELETE FROM issue_reports WHERE issue_id = ?";
        try (Connection conn = DriverManager.getConnection(url, user, password);
             PreparedStatement pstmt = conn.prepareStatement(sql)) {
            pstmt.setInt(1, issueId);
            if (pstmt.executeUpdate() > 0) logActivity(adminName, "Admin", "Deleted Issue ID: " + issueId);
        } catch (SQLException e) { e.printStackTrace(); }
    }
}