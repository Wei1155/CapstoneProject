from database import read_users, generate_user_id
from auth.validation import validate_email, validate_password, validate_first_name, validate_last_name

def register_user():
    """
    Register a new user and save to users.txt
    Returns the new user dictionary for auto-login
    """
    print("\n=== Register New User ===")

    # User type
    while True:
        user_type = input("Enter user type (admin/student/instructor): ").strip().lower()
        if user_type in ["admin", "student", "instructor"]:
            break
        else:
            print("Invalid user type! Must be admin, student, or instructor.")

    # First name
    while True:
        first_name = input("Enter first name: ").strip()
        if validate_first_name(first_name):
            break

    # Last name
    while True:
        last_name = input("Enter last name: ").strip()
        if validate_last_name(last_name):
            break

    # Email
    while True:
        email = input("Enter email: ").strip()
        if validate_email(email):
            break

    # Username
    while True:
        username = input("Enter username: ").strip()
        if len(username) >= 3 and username.isalnum():
            break
        else:
            print("Username must be at least 3 characters and alphanumeric.")

    # Password
    while True:
        password = input("Enter password: ").strip()
        if validate_password(password):
            break

    # Read existing users
    users = read_users()

    # Generate unique userID
    user_id = generate_user_id(user_type, users)

    # Create user dictionary
    new_user = {
        "userID": user_id,
        "username": username,
        "first_name": first_name,
        "last_name": last_name,
        "email": email,
        "password": password,
        "user_type": user_type
    }

    # Save to users.txt WITH spaces around "|", no blank lines
    filename = "admin_profiles/users.txt"
    try:
        # Ensure no extra blank lines
        with open(filename, 'r+') as file:
            content = file.read().rstrip('\n')
            file.seek(0, 2)  # go to end
            if content != '':
                file.write('\n')
            # write new user
            file.write(f"{new_user['userID']} | {new_user['username']} | {new_user['first_name']} | {new_user['last_name']} | {new_user['email']} | {new_user['password']} | {new_user['user_type']}")
        print(f"Registration successful! Welcome {first_name} {last_name} ({user_type})")
    except Exception as e:
        print(f"Error saving user: {e}")
        return None

    # Return new user for automatic login
    return new_user