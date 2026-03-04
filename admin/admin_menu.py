def admin_menu(profile):
    while True:
        print(f"\n=== Admin Menu ({profile['first_name']} {profile['last_name']}) ===")
        print("1. User Administration")
        print("2. View/Edit Profile")
        print("3. Content Moderation (coming soon)")   # 5.2
        print("4. Gamification Control (coming soon)")  # 5.3
        print("5. System Monitoring (coming soon)")     # 5.4
        print("6. Security & Settings (coming soon)")   # 5.5
        print("7. Reporting & Analytics (coming soon)") # 4.0
        print("8. Logout")

        choice = input("Select an option: ").strip()

        if choice == "1":
            print("[Placeholder] User Administration")  # your own code for 5.1
        elif choice == "2":
            print("[Placeholder] View/Edit Profile")
        elif choice == "3":
            print("[Placeholder] Content Moderation")
        elif choice == "4":
            print("[Placeholder] Gamification Control")
        elif choice == "5":
            print("[Placeholder] System Monitoring")
        elif choice == "6":
            print("[Placeholder] Security & Settings")
        elif choice == "7":
            print("[Placeholder] Reporting & Analytics")
        elif choice == "8":
            print("Logging out...")
            break
        else:
            print("Invalid option. Try again.")