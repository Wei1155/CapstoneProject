import os
from auth.validation.admin_validation import validate_admin_key
from auth.validation.user_validation import validate_username, validate_password

FILE_NAME = "users.txt"


def user_exists(username):
    """Check if username already exists in users.txt"""
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
            print("❌ Password must be 6+ chars, with uppercase, lowercase, and number")
            continue
        break

    # Save student (plain password)
    with open(FILE_NAME, "a") as file:
        file.write(f"{username},{password},student,1,0,None\n")
    print("✅ Student registered successfully!\n")


def register_admin():
    print("\n=== Admin Registration (Protected) ===")
    key = input("Enter admin secret key: ").strip()
    if not validate_admin_key(key):
        print("❌ Invalid admin key. Registration denied.")
        return

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

    # Save admin
    with open(FILE_NAME, "a") as file:
        file.write(f"{username},{password},admin,0,0,None\n")
    print("✅ Admin registered successfully!\n")


def register():
    """Main registration menu"""
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
            register_student()
        elif choice == 2:
            register_admin()
        elif choice == 3:
            return
        else:
            print("❌ Please choose between 1-3 only")