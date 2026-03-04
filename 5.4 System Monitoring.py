import os
from datetime import datetime

class SystemMonitor:
    def __init__(self):
        self.log_file = "admin_profiles/activity_log.txt"
        self.report_file = "admin_profiles/issue_report.txt"
        self._ensure_files_exist()

    def _ensure_files_exist(self):
        if not os.path.exists("admin_profiles"):
            os.makedirs("admin_profiles")
        for f in [self.log_file, self.report_file]:
            if not os.path.exists(f):
                with open(f, 'w') as file:
                    pass

    def log_activity(self, user_name, user_role, action):
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        log_entry = f"{user_name} | {user_role} | {action} | {timestamp}\n"
        
        with open(self.log_file, 'a') as file:
            file.write(log_entry)
        print(f"[LOG] {timestamp} - {user_name} ({user_role}): {action}")

    def report_issue(self, user_name, issue_type, description):
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        lines = self._read_all_lines(self.report_file)
        new_id = len(lines) + 1
        report_entry = f"{new_id} | {user_name} | {issue_type} | {description} | Pending | None | {timestamp}\n"
        
        with open(self.report_file, 'a') as file:
            file.write(report_entry)
        
        self.log_activity(user_name, "Student", f"Reported a {issue_type}")
        print(f"✅ Issue submitted by {user_name}.")

    def resolve_issue(self, issue_id, admin_name, response_text):
        lines = self._read_all_lines(self.report_file)
        found = False
        updated_lines = []

        for line in lines:
            cols = [c.strip() for c in line.split("|")]
            if cols[0] == str(issue_id):
                cols[4] = "Resolved"
                cols[5] = f"[{admin_name}]: {response_text}"
                line = " | ".join(cols) + "\n"
                found = True
            updated_lines.append(line)

        if found:
            with open(self.report_file, 'w') as file:
                file.writelines(updated_lines)
            self.log_activity(admin_name, "Admin", f"Resolved Issue ID: {issue_id}")
            print(f"🛠 Issue #{issue_id} has been resolved by {admin_name}.")
        else:
            print(f"❌ Error: Issue ID {issue_id} not found.")

    def delete_issue(self, issue_id, admin_name):
        lines = self._read_all_lines(self.report_file)
        updated_lines = [l for l in lines if not l.startswith(f"{issue_id} |")]

        if len(updated_lines) < len(lines):
            with open(self.report_file, 'w') as file:
                file.writelines(updated_lines)
            self.log_activity(admin_name, "Admin", f"Deleted Issue ID: {issue_id}")
            print(f"🗑 Issue #{issue_id} removed.")
        else:
            print(f"❌ Error: Could not find Issue ID {issue_id}.")

    def view_all_logs(self):
        print("\n" + "="*30 + " SYSTEM ACTIVITY LOGS " + "="*30)
        lines = self._read_all_lines(self.log_file)
        for line in lines:
            cols = [c.strip() for c in line.split("|")]
            if len(cols) == 4:
                print(f"[{cols[3]}] {cols[0]} ({cols[1]}) -> {cols[2]}")
        print("="*83 + "\n")

    def view_all_reports(self):
        print("\n" + "="*35 + " ISSUE REPORTS " + "="*34)
        print(f"{'ID':<4} | {'User':<10} | {'Type':<15} | {'Status':<10} | {'Response'}")
        print("-" * 83)
        lines = self._read_all_lines(self.report_file)
        for line in lines:
            cols = [c.strip() for c in line.split("|")]
            if len(cols) >= 6:
                print(f"{cols[0]:<4} | {cols[1]:<10} | {cols[2]:<15} | {cols[4]:<10} | {cols[5]}")
        print("="*83 + "\n")

    def _read_all_lines(self, filename):
        try:
            with open(filename, 'r') as file:
                return [l for l in file.readlines() if l.strip()]
        except FileNotFoundError:
            return []


if __name__ == "__main__":
    monitor = SystemMonitor()
    monitor.log_activity("Alice", "Student", "Login success")
    monitor.report_issue("Alice", "Quiz Error", "Question 3 logic is wrong.")
    monitor.view_all_reports()
    monitor.resolve_issue(1, "Charlie", "Fixed in database.")
    monitor.view_all_logs()
    monitor.view_all_reports()