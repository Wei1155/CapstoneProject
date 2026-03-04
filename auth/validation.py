from database import read_users

def validate_email(email):
    if '@' not in email or email.count('@') != 1:
        print("Invalid email format! Include '@'")
        return False
    username, domain = email.split('@')
    if '.' not in domain:
        print("Invalid email format! Domain must contain '.'")
        return False
    if '|' in email:
        print("Email cannot contain '|'")
        return False
    # check for duplicate email
    existing_users = read_users()
    for user in existing_users:
        if email.lower() == user['email'].lower():
            print("Email already exists in database, please try another email.")
            return False
    return True


def validate_password(password):
    if len(password) < 3:
        print("Password must be longer than 3 characters")
        return False
    if not any(char.isupper() for char in password):
        print("Password must contain at least 1 capital letter")
        return False
    if not any(char.isdigit() for char in password):
        print("Password must contain at least 1 number")
        return False
    if '|' in password:
        print("Password cannot contain '|'")
        return False
    return True


def validate_first_name(first_name):
    if not first_name.isalpha():
        print("First name must contain only letters")
        return False
    if not first_name[0].isupper():
        print("First name must start with a capital letter")
        return False
    return True


def validate_last_name(last_name):
    if not last_name.isalpha():
        print("Last name must contain only letters")
        return False
    if not last_name[0].isupper():
        print("Last name must start with a capital letter")
        return False
    return True