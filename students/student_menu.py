from auth.profile_management import view_and_edit_profile
from students.student_enrollment import manage_enrollments



def student_menu(current_user):
    while True:
        print(f"\n=== Student Menu ({current_user['first_name']} {current_user['last_name']}) ===")
        print("1. View/Edit Profile")
        print("2. Enroll in Courses & Track Progress")
        print("3. Resource Library (coming soon)")
        print("4. Quests & Challenges (coming soon)")
        print("5. Rewards & Badges (coming soon)")
        print("6. Progress Reports (coming soon)")
        print("7. Rewards Store & Redemption (coming soon)")
        print("8. Activity Log (coming soon)")
        print("9. Logout")

        choice = input("Select an option: ").strip()

        if choice == "1":
            view_and_edit_profile(current_user)
        elif choice == "2":
            manage_enrollments(current_user)
        elif choice == "3":
            print("[Placeholder] Track Progress")
        elif choice == "4":
            print("[Placeholder] Resource Library")
        elif choice == "5":
            print("[Placeholder] Quests & Challenges (includes quizzes)")
        elif choice == "6":
            print("[Placeholder] Rewards & Badges")
        elif choice == "7":
            print("[Placeholder] Progress Reports")
        elif choice == "8":
            print("[Placeholder] Rewards Store & Redemption")
        elif choice == "9":
            print("Logging out...")
            break
        else:
            print("Invalid option. Try again.")