import os

class SecuritySystem:
    def __init__(self):
        self.settings_file = "admin_profiles/settings.txt"
        self.current_user = None
        self._init_settings()

    def _init_settings(self):
        if not os.path.exists("admin_profiles"):
            os.makedirs("admin_profiles")
        
        if not os.path.exists(self.settings_file):
            default_settings = {
                "system_name": "Learning Management System",
                "enable_leaderboard": "True",
                "max_quiz_attempts": "3"
            }
            self._save_all_settings(default_settings)

    
    @staticmethod
    def access_control(allowed_roles):
        def decorator(func):
            def wrapper(self, *args, **kwargs):
                if not self.current_user:
                    print("❌ Access Denied: Please login first.")
                    return
                user_role = self.current_user.get('user_type', '').lower()
                allowed_roles_lower = [r.lower() for r in allowed_roles]
                
                if user_role in allowed_roles_lower:
                    return func(self, *args, **kwargs)
                else:
                    print(f"🚫 Permission Denied: {user_role} cannot access this.")
            return wrapper
        return decorator

    def set_session(self, user_dict):
        self.current_user = user_dict
        if user_dict:
            print(f"\n[Session] Active user: {user_dict['username']} ({user_dict['user_type']})")

    @access_control(allowed_roles=["admin"])
    def update_setting(self, key, value):
        settings = self._load_all_settings()
        settings[key] = str(value)
        self._save_all_settings(settings)
        print(f"⚙️ Config Updated in {self.settings_file}: {key} = {value}")

    def get_setting(self, key):
        settings = self._load_all_settings()
        return settings.get(key)

    def view_all_settings(self):
        settings = self._load_all_settings()
        print("\n--- Current System Configuration (from TXT) ---")
        for k, v in settings.items():
            print(f"{k}: {v}")

    def _load_all_settings(self):
        settings = {}
        try:
            with open(self.settings_file, 'r') as f:
                for line in f:
                    if "|" in line:
                        k, v = line.strip().split("|")
                        settings[k.strip()] = v.strip()
        except FileNotFoundError:
            pass
        return settings

    def _save_all_settings(self, settings):
        with open(self.settings_file, 'w') as f:
            for k, v in settings.items():
                f.write(f"{k} | {v}\n")

if __name__ == "__main__":
    sys = SecuritySystem()
    student_user = {"username": "Alice", "user_type": "student"}
    sys.set_session(student_user)
    sys.update_setting("system_name", "Hacked LMS") 
    admin_user = {"username": "Charlie", "user_type": "admin"}
    sys.set_session(admin_user)
    sys.update_setting("max_quiz_attempts", 10) 
    sys.view_all_settings()