from auth.profile_management import view_and_edit_profile
from admin.admin_enrollment import view_all_enrollments



def admin_menu(current_user):
    while True:
        print(f"\n=== Admin Menu ({current_user['first_name']} {current_user['last_name']}) ===")
        print("1. User Administration")
        print("2. View/Edit Profile")
        print("3. View All Enrollments")
        print("4. Content Moderation (coming soon)")
        print("5. Gamification Control (coming soon)")
        print("6. System Monitoring (coming soon)")
        print("7. Security & Settings (coming soon)")
        print("8. Reporting & Analytics (coming soon)")
        print("9. Logout")

        choice = input("Select an option: ").strip()

        if choice == "1":
            print("[Placeholder] User Administration")  # your own code for 5.1
        elif choice == "2":
            view_and_edit_profile(current_user)
        elif choice == "3":
            view_all_enrollments()
        elif choice == "4":
            print("[Placeholder] Content Moderation (coming soon)")
        elif choice == "5":
            print("[Placeholder] Gamification Control (coming soon)")
        elif choice == "6":
            print("[Placeholder] System Monitoring (coming soon)")
        elif choice == "7":
            print("[Placeholder] Security & Settings (coming soon)")
        elif choice == "8":
            print("[Placeholder] Reporting & Analytics (coming soon)")
        elif choice == "9":
            print("Logging out...")
            break
        else:
            print("Invalid option. Try again.")