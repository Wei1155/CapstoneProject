import os
from auth.validation.user_validation import validate_username

FILE_NAME = "users.txt"


def login():
    print("\n=== Login ===")
    username = input("Enter username: ").strip()
    password = input("Enter password: ").strip()

    if not os.path.exists(FILE_NAME):
        print("❌ No users registered yet.")
        return None

    with open(FILE_NAME, "r") as file:
        for line in file:
            data = line.strip().split(",")
            stored_username = data[0]
            stored_password = data[1]
            role = data[2]

            if username == stored_username and password == stored_password:
                print(f"✅ Login successful! Role: {role}\n")
                return role

    print("❌ Invalid username or password.")
    return None