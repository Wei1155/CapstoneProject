import os
from auth.validation.user_validation import validate_password

FILE_NAME = "users.txt"


def view_profile(username):
    if not os.path.exists(FILE_NAME):
        print("❌ No users registered yet.")
        return

    with open(FILE_NAME, "r") as file:
        for line in file:
            data = line.strip().split(",")
            if data[0] == username:
                uname, pw, role, level, xp, badges = data
                print("\n=== Profile ===")
                print(f"Username: {uname}")
                print(f"Role: {role}")
                if role == "student":
                    print(f"Level: {level}")
                    print(f"XP: {xp}")
                    print(f"Badges: {badges}")
                print("================\n")
                return
    print("❌ User not found.")


def edit_password(username):
    if not os.path.exists(FILE_NAME):
        print("❌ No users registered yet.")
        return

    with open(FILE_NAME, "r") as file:
        lines = file.readlines()

    for i, line in enumerate(lines):
        data = line.strip().split(",")
        if data[0] == username:
            while True:
                new_pw = input("Enter new password: ").strip()
                if not validate_password(new_pw):
                    print("❌ Password must be 6+ chars with uppercase, lowercase, and number")
                    continue
                break
            data[1] = new_pw
            lines[i] = ",".join(data) + "\n"
            break
    else:
        print("❌ User not found.")
        return

    with open(FILE_NAME, "w") as file:
        file.writelines(lines)

    print("✅ Password updated successfully!\n")


def profile_menu(username):
    while True:
        print("\n=== Profile Management ===")
        print("1. View Profile")
        print("2. Change Password")
        print("3. Back to Main Menu")

        choice = input("Choose option (1-3): ").strip()
        if not choice.isdigit():
            print("❌ Please enter numbers only")
            continue

        choice = int(choice)

        if choice == 1:
            view_profile(username)
        elif choice == 2:
            edit_password(username)
        elif choice == 3:
            break
        else:
            print("❌ Please choose 1-3 only")