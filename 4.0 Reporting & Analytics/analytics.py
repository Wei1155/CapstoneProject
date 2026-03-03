from file_reader import read_file

def load_topics(courseId):

    lines = read_file("txtdata/topics.txt")
    topics = []

    for line in lines:
        if line == "":
            continue

        parts = line.split("|")

        if parts[0] == courseId:
            topics.append(parts)

    return topics


def course_analytics(courseId):

    topics = load_topics(courseId)

    total = len(topics)
    completed = 0
    ongoing = 0

    for t in topics:
        if t[2] == "completed":
            completed += 1
        else:
            ongoing += 1

    progress = (completed * 100) // total

    grade = "F"
    if progress >= 80:
        grade = "A"
    elif progress >= 60:
        grade = "B"
    elif progress >= 40:
        grade = "C"

    return {
        "total": total,
        "completed": completed,
        "ongoing": ongoing,
        "progress": progress,
        "grade": grade,
        "topics": topics
    }