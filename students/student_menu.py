def student_menu(profile):
    while True:
        print(f"\n=== Student Menu ({profile['first_name']} {profile['last_name']}) ===")
        print("1. View/Edit Profile")
        print("2. Enroll in Courses")
        print("3. Track Progress")
        print("4. Resource Library (coming soon)")
        print("5. Quests & Challenges (coming soon)")
        print("6. Rewards & Badges (coming soon)")
        print("7. Progress Reports (coming soon)")
        print("8. Rewards Store & Redemption (coming soon)")
        print("9. Activity Log (coming soon)")
        print("10. Logout")

        choice = input("Select an option: ").strip()

        if choice == "1":
            print("[Placeholder] View/Edit Profile")
        elif choice == "2":
            print("[Placeholder] Enroll in Courses")
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
            print("[Placeholder] Activity Log")
        elif choice == "10":
            print("Logging out...")
            break
        else:
            print("Invalid option. Try again.")