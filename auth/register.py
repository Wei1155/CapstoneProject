import os
from auth.validation.admin_validation import validate_admin_key
from auth.validation.user_validation import validate_username, validate_password

FILE_NAME = "users.txt"


def user_exists(username):
    if not os.path.exists(FILE_NAME):
        return False
    with open(FILE_NAME, "r") as file:
        for line in file:
            if line.strip().split(",")[0] == username:
                return True
    return False


def register_student():
    print("\n=== Student Registration ===")
    while True:
        username = input("Enter username: ").strip()
        if not validate_username(username):
            print("❌ Invalid username (4-20 letters/numbers only)")
            continue
        if user_exists(username):
            print("❌ Username already exists")
            continue
        break

    while True:
        password = input("Enter password: ").strip()
        if not validate_password(password):
            print("❌ Password must be 6+ chars, uppercase, lowercase, and number")
            continue
        break

    with open(FILE_NAME, "a") as file:
        file.write(f"{username},{password},student,1,0,None\n")
    print("✅ Student registered successfully!\n")

    return username, "student"  # return for auto-login


def register_admin():
    print("\n=== Admin Registration (Protected) ===")
    key = input("Enter admin secret key: ").strip()
    if not validate_admin_key(key):
        print("❌ Invalid admin key. Registration denied.")
        return None, None

    while True:
        username = input("Enter admin username: ").strip()
        if not validate_username(username):
            print("❌ Invalid username")
            continue
        if user_exists(username):
            print("❌ Username already exists")
            continue
        break

    while True:
        password = input("Enter admin password: ").strip()
        if not validate_password(password):
            print("❌ Password must be 6+ chars, uppercase, lowercase, and number")
            continue
        break

    with open(FILE_NAME, "a") as file:
        file.write(f"{username},{password},admin,0,0,None\n")
    print("✅ Admin registered successfully!\n")

    return username, "admin"  # return for auto-login


def register():
    while True:
        print("\nRegister as:")
        print("1. Student")
        print("2. Admin")
        print("3. Back to main menu")

        choice = input("Choose option (1-3): ").strip()
        if not choice.isdigit():
            print("❌ Please enter numbers only")
            continue

        choice = int(choice)
        if choice == 1:
            return register_student()
        elif choice == 2:
            return register_admin()
        elif choice == 3:
            return None, None
        else:
            print("❌ Please choose 1-3 only")