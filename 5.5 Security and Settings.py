import sqlite3

class SecuritySystem:
    def __init__(self, db_name="security_settings.db"):
        self.conn = sqlite3.connect(db_name)
        self.cursor = self.conn.cursor()
        self._init_db()
        self.current_user = None

    def _init_db(self):
        self.cursor.execute('''
            CREATE TABLE IF NOT EXISTS system_settings (
                setting_key TEXT PRIMARY KEY,
                setting_value TEXT
            )
        ''')
        default_settings = [
            ('system_name', 'Learning Management System'),
            ('enable_leaderboard', 'True'),
            ('max_quiz_attempts', '3')
        ]
        self.cursor.executemany("INSERT OR IGNORE INTO system_settings VALUES (?, ?)", default_settings)
        self.conn.commit()

    
    
    @staticmethod  
    def access_control(allowed_roles):
        def decorator(func):
            def wrapper(self, *args, **kwargs):
                
                if not self.current_user:
                    print("❌ Access Denied: Please login first.")
                    return
                
                if self.current_user['role'] in allowed_roles:
                    return func(self, *args, **kwargs)
                else:
                    print(f"🚫 Permission Denied: {self.current_user['role']} cannot access this.")
            return wrapper
        return decorator


    def login(self, username, role):
        self.current_user = {"name": username, "role": role}
        print(f"\n[Login] {username} logged in as {role}")

    @access_control(allowed_roles=["Admin"])
    def admin_page(self):
        print("🛠 [admin_page.py] Welcome to Admin Dashboard.")

    @access_control(allowed_roles=["Student"])
    def student_page(self):
        print("📖 [student_page.py] Welcome to Student Portal.")

    @access_control(allowed_roles=["Lecturer", "Admin"])
    def add_course(self):
        print("📝 Course content added successfully.")

   

    @access_control(allowed_roles=["Admin"])
    def update_setting(self, key, value):
        self.cursor.execute("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?", (str(value), key))
        self.conn.commit()
        print(f"⚙️ Config Updated: {key} = {value}")

    def get_setting(self, key):
        self.cursor.execute("SELECT setting_value FROM system_settings WHERE setting_key = ?", (key,))
        result = self.cursor.fetchone()
        return result[0] if result else None

    def view_all_settings(self):
        print("\n--- Current System Configuration ---")
        self.cursor.execute("SELECT * FROM system_settings")
        for row in self.cursor.fetchall():
            print(f"{row[0]}: {row[1]}")


if __name__ == "__main__":
    sys = SecuritySystem()

    
    sys.login("Alice", "Student")
    sys.student_page()   
    sys.admin_page()     

    
    sys.login("Bob", "Lecturer")
    sys.add_course()     
    sys.update_setting("system_name", "Hacked LMS") 

    
    sys.login("Charlie", "Admin")
    sys.admin_page()     
    sys.update_setting("max_quiz_attempts", 10)
    sys.view_all_settings()