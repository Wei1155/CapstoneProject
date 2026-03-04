from database import read_enrollments, write_enrollments


def update_enrollment_status():
    enrollments = read_enrollments()

    enrollment_id = input("Enter Enrollment ID to update: ")

    for e in enrollments:
        if e["enrollment_id"] == enrollment_id:
            print(f"Current Status: {e['status']}")
            new_status = input("Enter new status (Approved/Completed/Rejected): ")

            e["status"] = new_status
            write_enrollments(enrollments)

            print("Status updated successfully!")
            return

    print("Enrollment ID not found.")