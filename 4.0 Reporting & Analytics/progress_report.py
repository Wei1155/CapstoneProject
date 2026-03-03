from file_reader import read_file

def load_users():
    lines = read_file("txtdata/students.txt")
    students = []

    for line in lines:
        if line == "":
            continue
        parts = line.split("|")
        students.append(parts)

    return students


def load_enrollments():
    lines = read_file("txtdata/enrollments.txt")
    data = []

    for line in lines:
        if line == "":
            continue
        data.append(line.split("|"))

    return data


def load_courses():
    lines = read_file("txtdata/courses.txt")
    data = []

    for line in lines:
        if line == "":
            continue
        data.append(line.split("|"))

    return data

def generate_progress(user_id):

    users = load_users()
    enrollments = load_enrollments()
    courses = load_courses()

    result = []

    for u in users:
        if u[0] == user_id:
            lastLogin = u[2]
            score = u[3]
            totalTime = u[4]

    for e in enrollments:
        if e[0] == user_id:

            courseId = e[1]
            status = e[2]

            for c in courses:
                if c[0] == courseId:
                    courseName = c[1]
                    subject = c[2]

                    row = [
                        courseName,
                        subject,
                        lastLogin,
                        score,
                        totalTime,
                        status
                    ]

                    result.append(row)

    return result


data = generate_progress("U01")

for row in data:
    print("|".join(row))