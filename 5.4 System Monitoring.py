import sqlite3
from datetime import datetime

class SystemMonitor:
    def __init__(self, db_name="system_monitor.db"):
        
        self.conn = sqlite3.connect(db_name)
        self.cursor = self.conn.cursor()
        self._create_tables()

    def _create_tables(self):
        
        
        self.cursor.execute('''
            CREATE TABLE IF NOT EXISTS activity_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_name TEXT,
                user_role TEXT,
                action TEXT,
                timestamp DATETIME
            )
        ''')
        
        
        self.cursor.execute('''
            CREATE TABLE IF NOT EXISTS issue_report (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_name TEXT,
                issue_type TEXT,
                description TEXT,
                status TEXT DEFAULT 'Pending',
                admin_response TEXT DEFAULT 'None',
                timestamp DATETIME
            )
        ''')
        self.conn.commit()

    def log_activity(self, user_name, user_role, action):
        
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        self.cursor.execute(
            "INSERT INTO activity_log (user_name, user_role, action, timestamp) VALUES (?, ?, ?, ?)",
            (user_name, user_role, action, timestamp)
        )
        self.conn.commit()
       
        print(f"[LOG] {timestamp} - {user_name} ({user_role}): {action}")

   
    def report_issue(self, user_name, issue_type, description):
        
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        
        
        self.cursor.execute(
            """INSERT INTO issue_report 
               (user_name, issue_type, description, status, timestamp) 
               VALUES (?, ?, ?, ?, ?)""",
            (user_name, issue_type, description, 'Pending', timestamp)
        )
        self.conn.commit()
        
        
        self.log_activity(user_name, "Student", f"Reported a {issue_type}")
        print(f"✅ Issue submitted by {user_name}.")

   
    
    def resolve_issue(self, issue_id, admin_name, response_text):
        
        self.cursor.execute(
            "UPDATE issue_report SET status = 'Resolved', admin_response = ? WHERE id = ?",
            (f"[{admin_name}]: {response_text}", issue_id)
        )
        
        if self.cursor.rowcount > 0:
            self.conn.commit()
            
            self.log_activity(admin_name, "Admin", f"Resolved Issue ID: {issue_id}")
            print(f"🛠 Issue #{issue_id} has been resolved by {admin_name}.")
        else:
            print(f"❌ Error: Issue ID {issue_id} not found.")

    def delete_issue(self, issue_id, admin_name):
        
        self.cursor.execute("DELETE FROM issue_report WHERE id = ?", (issue_id,))
        if self.cursor.rowcount > 0:
            self.conn.commit()
            self.log_activity(admin_name, "Admin", f"Deleted Issue ID: {issue_id}")
            print(f"🗑 Issue #{issue_id} removed.")
        else:
            print(f"❌ Error: Could not find Issue ID {issue_id}.")

    
    def view_all_logs(self):
        
        print("\n" + "="*30 + " SYSTEM ACTIVITY LOGS " + "="*30)
        self.cursor.execute("SELECT * FROM activity_log ORDER BY timestamp DESC")
        rows = self.cursor.fetchall()
        for row in rows:
            print(f"[{row[4]}] {row[1]} ({row[2]}) -> {row[3]}")
        print("="*83 + "\n")

    def view_all_reports(self):
        
        print("\n" + "="*35 + " ISSUE REPORTS " + "="*34)
        print(f"{'ID':<4} | {'User':<10} | {'Type':<15} | {'Status':<10} | {'Response'}")
        print("-" * 83)
        self.cursor.execute("SELECT id, user_name, issue_type, status, admin_response FROM issue_report")
        for row in self.cursor.fetchall():
            print(f"{row[0]:<4} | {row[1]:<10} | {row[2]:<15} | {row[3]:<10} | {row[4]}")
        print("="*83 + "\n")



if __name__ == "__main__":
    monitor = SystemMonitor()

   
    monitor.log_activity("Alice", "Student", "Login success")
    monitor.log_activity("Bob", "Teacher", "Uploaded 'Python 101' Quiz")
    monitor.log_activity("Charlie", "Admin", "Changed system language to Chinese")

   
    monitor.report_issue("Alice", "Quiz Error", "Question 3 logic is wrong.")
    monitor.report_issue("Alice", "System Bug", "Cannot upload avatar image.")

    
    monitor.view_all_reports()

    
    monitor.resolve_issue(1, "Charlie", "Fixed the logic in Question 3. Thanks!")

    
    monitor.view_all_reports()

    
    monitor.view_all_logs()