def read_users():
    users = []
    filename = "admin_profiles/users.txt"
    try:
        with open(filename, 'r') as file:
            for line in file:
                line = line.strip()
                if line == "":
                    continue  # skip empty lines
                cols = line.split("|")
                if len(cols) == 7:
                    # strip each part to remove accidental spaces
                    user = {
                        "userID": cols[0].strip(),
                        "username": cols[1].strip(),
                        "first_name": cols[2].strip(),
                        "last_name": cols[3].strip(),
                        "email": cols[4].strip(),
                        "password": cols[5].strip(),
                        "user_type": cols[6].strip()
                    }
                    users.append(user)
    except FileNotFoundError:
        print(f"{filename} not found!")
    return users


def generate_user_id(user_type, users):
    prefix_map = {"admin": "A", "student": "S", "instructor": "I"}
    prefix = prefix_map[user_type.lower()]
    existing_ids = [u["userID"].strip() for u in users if u["user_type"].lower() == user_type.lower()]
    if not existing_ids:
        number = 1
    else:
        number = max(int(uid[1:]) for uid in existing_ids) + 1
    return f"{prefix}{str(number).zfill(3)}"