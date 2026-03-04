from database import read_users


def login_user():
    users = read_users()  # load all users

    print("\n=== Login ===")

    while True:
        login_input = input("Enter username or email: ").strip()
        password_input = input("Enter password: ").strip()

        # check credentials
        user_found = False
        for user in users:
            if (login_input == user["username"] or login_input == user["email"]) and password_input == user["password"]:
                print(f"Login successful! Welcome {user['first_name']} {user['last_name']} ({user['user_type']})")
                return user
            elif login_input == user["username"] or login_input == user["email"]:
                user_found = True

        # If we reach here, login failed
        if user_found:
            print("Incorrect password. Please try again.\n")
        else:
            print("Username/email not found. Please try again.\n")