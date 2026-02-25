ADMIN_SECRET_KEY = "ELEARN2026"   # Must match register.py

def validate_admin_key(key):
    """
    Check if admin secret key is correct.
    """
    return key == ADMIN_SECRET_KEY