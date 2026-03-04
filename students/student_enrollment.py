from database import read_enrollments, write_enrollments, read_courses

def manage_enrollments(student):

    enrollments = read_enrollments()
    student_id = student["userID"]
    student_name = f"{student['first_name']} {student['last_name']}"

    print("\n=== YOUR ENROLLMENTS & PROGRESS ===")
    found = False
    for e in enrollments:
        if e["student_id"] == student_id:
            print(f"{e['enrollment_id']} | {e['course_id']} | {e['course_name']} | {e['progress']} | {e['status']}")
            found = True

    if not found:
        print("No enrollments found.")

    # Ask if student wants to enroll in a new course
    choice = input("\nDo you want to enroll in a new course? (y/n): ").lower()
    if choice != "y":
        return

    # Show available courses
    courses = read_courses()  # returns list of dicts with course_id, course_name, status
    print("\n=== AVAILABLE COURSES ===")
    open_courses = [c for c in courses if c["status"].lower() == "open"]
    if not open_courses:
        print("No open courses available.")
        return

    for c in open_courses:
        print(f"{c['course_id']} | {c['course_name']}")

    # Prompt student to select course
    course_id = input("Enter Course ID to enroll: ").strip()
    selected_course = None
    for c in open_courses:
        if c["course_id"] == course_id:
            selected_course = c
            break

    if not selected_course:
        print("Invalid course ID or course not open.")
        return

    # Check if already enrolled
    for e in enrollments:
        if e["student_id"] == student_id and e["course_id"] == course_id:
            print("You are already enrolled in this course.")
            return

    # Generate new enrollment ID
    enrollment_id = "E" + str(len(enrollments) + 1).zfill(3)

    # Create enrollment record
    new_enrollment = {
        "enrollment_id": enrollment_id,
        "student_id": student_id,
        "student_name": student_name,
        "course_id": selected_course["course_id"],
        "course_name": selected_course["course_name"],
        "progress": "0%",
        "status": "In Progress"
    }

    enrollments.append(new_enrollment)
    write_enrollments(enrollments)
    print(f"Successfully enrolled in {selected_course['course_name']}!")