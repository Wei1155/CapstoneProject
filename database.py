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

def write_users(users):
    filename = "admin_profiles/users.txt"
    with open(filename, 'w') as file:
        for i, user in enumerate(users):
            line = f"{user['userID']} | {user['username']} | {user['first_name']} | {user['last_name']} | {user['email']} | {user['password']} | {user['user_type']}"
            if i < len(users) - 1:
                file.write(line + "\n")
            else:
                file.write(line)


def generate_user_id(user_type, users):
    prefix_map = {"admin": "A", "student": "S", "instructor": "I"}
    prefix = prefix_map[user_type.lower()]
    existing_ids = [u["userID"].strip() for u in users if u["user_type"].lower() == user_type.lower()]
    if not existing_ids:
        number = 1
    else:
        number = max(int(uid[1:]) for uid in existing_ids) + 1
    return f"{prefix}{str(number).zfill(3)}"


def read_enrollments():
    enrollments = []
    filename = "admin_profiles/enrollments.txt"  # correct folder

    try:
        with open(filename, "r") as file:
            for line in file:
                line = line.strip()
                if not line:  # skip empty lines
                    continue

                cols = [col.strip() for col in line.split("|")]
                if len(cols) != 7:
                    print(f"Skipping invalid line: {line}")
                    continue

                enrollment = {
                    "enrollment_id": cols[0],
                    "student_id": cols[1],
                    "student_name": cols[2],
                    "course_id": cols[3],
                    "course_name": cols[4],
                    "progress": cols[5],
                    "status": cols[6]
                }
                enrollments.append(enrollment)

    except FileNotFoundError:
        print(f"{filename} not found!")

    return enrollments

def write_enrollments(enrollments):
    filename = "admin_profiles/enrollments.txt"
    with open(filename, "w") as file:
        for e in enrollments:
            line = " | ".join([
                e["enrollment_id"],
                e["student_id"],
                e["student_name"],
                e["course_name"],
                e["progress"],
                e["status"]
            ])
            file.write(line + "\n")

# database.py

def read_courses():

    courses = []
    filename = "admin_profiles/courses.txt"  # adjust path if courses.txt is here

    try:
        with open(filename, "r") as file:
            for line in file:
                line = line.strip()
                if not line:  # skip empty lines
                    continue

                cols = [col.strip() for col in line.split("|")]
                if len(cols) != 3:
                    print(f"Skipping invalid line: {line}")
                    continue

                course = {
                    "course_id": cols[0],
                    "course_name": cols[1],
                    "status": cols[2]  # Open / Closed
                }
                courses.append(course)

    except FileNotFoundError:
        print(f"{filename} not found!")

    return courses