import re

def validate_username(username):
    """
    Username rules:
    - 4 to 20 characters
    - Letters and numbers only
    - No spaces
    """
    if not re.match(r"^[a-zA-Z0-9]{4,20}$", username):
        return False
    return True


def validate_password(password):
    """
    Password rules:
    - Minimum 6 characters
    - At least 1 uppercase letter
    - At least 1 lowercase letter
    - At least 1 number
    """
    if len(password) < 6:
        return False

    if not re.search(r"[A-Z]", password):
        return False

    if not re.search(r"[a-z]", password):
        return False

    if not re.search(r"[0-9]", password):
        return False

    return True