def instructor_menu(profile):
    while True:
        print(f"\n=== Instructor Menu ({profile['first_name']} {profile['last_name']}) ===")
        print("1. View/Edit Profile")
        print("2. Track Student Enrollment")
        print("3. Manage Courses (coming soon)")
        print("4. Track Quizzes & Assignments (coming soon)")
        print("5. Track Gamification (coming soon)")
        print("6. Student Performance Analytics (coming soon)")
        print("7. Logout")

        choice = input("Select an option: ").strip()

        if choice == "1":
            print("[Placeholder] View/Edit Profile")
        elif choice == "2":
            print("[Placeholder] Track Student Enrollment")
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