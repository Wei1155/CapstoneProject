from database import read_users, write_users
from auth.validation import validate_email, validate_password, validate_first_name, validate_last_name


def view_and_edit_profile(current_user):
    users = read_users()

    # Find current user from file (to avoid outdated data)
    for user in users:
        if user["userID"] == current_user["userID"]:
            current_user = user
            break

    # ===== VIEW PROFILE =====
    print("\n========== YOUR PROFILE ==========")
    print(f"User ID   : {current_user['userID']}")
    print(f"Username  : {current_user['username']}")
    print(f"First Name: {current_user['first_name']}")
    print(f"Last Name : {current_user['last_name']}")
    print(f"Email     : {current_user['email']}")
    print(f"User Type : {current_user['user_type']}")
    print("==================================")

    # ===== ASK IF WANT TO EDIT =====
    choice = input("\nDo you want to edit your profile? (yes/no): ").strip().lower()

    if choice != "yes":
        print("Returning to menu...")
        return current_user

    # ===== EDIT SECTION =====
    while True:
        print("\n===== EDIT PROFILE =====")
        print("1. Edit Username")
        print("2. Edit First Name")
        print("3. Edit Last Name")
        print("4. Edit Email")
        print("5. Edit Password")
        print("6. Done Editing")

        option = input("Choose option: ").strip()

        if option == "1":
            new_username = input("Enter new username: ").strip()
            if len(new_username) >= 3 and new_username.isalnum():
                current_user["username"] = new_username
                print("Username updated!")
            else:
                print("Invalid username.")

        elif option == "2":
            while True:
                new_first = input("Enter new first name: ").strip()
                if validate_first_name(new_first):
                    current_user["first_name"] = new_first
                    print("First name updated!")
                    break

        elif option == "3":
            while True:
                new_last = input("Enter new last name: ").strip()
                if validate_last_name(new_last):
                    current_user["last_name"] = new_last
                    print("Last name updated!")
                    break

        elif option == "4":
            while True:
                new_email = input("Enter new email: ").strip()
                if validate_email(new_email):
                    current_user["email"] = new_email
                    print("Email updated!")
                    break

        elif option == "5":
            while True:
                new_password = input("Enter new password: ").strip()
                if validate_password(new_password):
                    current_user["password"] = new_password
                    print("Password updated!")
                    break

        elif option == "6":
            print("Finished editing.")
            break

        else:
            print("Invalid choice!")

    # ===== SAVE CHANGES =====
    for i, user in enumerate(users):
        if user["userID"] == current_user["userID"]:
            users[i] = current_user
            break

    write_users(users)
    print("Profile saved successfully!")

    return current_user