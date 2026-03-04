from auth.profile_management import view_and_edit_profile
from admin.admin_enrollment import view_all_enrollments


def instructor_menu(current_user):
    while True:
        print(f"\n=== Instructor Menu ({current_user['first_name']} {current_user['last_name']}) ===")
        print("1. View/Edit Profile")
        print("2. Track Student Enrollment")
        print("3. Manage Courses (coming soon)")
        print("4. Track Quizzes & Assignments (coming soon)")
        print("5. Track Gamification (coming soon)")
        print("6. Student Performance Analytics (coming soon)")
        print("7. Logout")

        choice = input("Select an option: ").strip()

        if choice == "1":
            view_and_edit_profile(current_user)
        elif choice == "2":
            view_all_enrollments()
        elif choice == "3":
            print("[Placeholder] Manage Courses")
        elif choice == "4":
            print("[Placeholder] Track Quizzes & Assignments")
        elif choice == "5":
            print("[Placeholder] Track Gamification")
        elif choice == "6":
            print("[Placeholder] Student Performance Analytics")
        elif choice == "7":
            print("Logging out...")
            break
        else:
            print("Invalid option. Try again.")