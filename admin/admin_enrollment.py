from database import read_enrollments

def view_all_enrollments():

    enrollments = read_enrollments()

    if not enrollments:
        print("\nNo enrollments found!")
        return

    print("\n=== ALL STUDENT ENROLLMENTS ===")
    print("EnrollmentID | StudentID | Student Name | CourseID | Course Name | Progress | Status")
    print("-"*80)
    for e in enrollments:
        print(f"{e['enrollment_id']} | {e['student_id']} | {e['student_name']} | "
              f"{e['course_id']} | {e['course_name']} | {e['progress']} | {e['status']}")

    input("\nPress Enter to return to Admin Menu...")