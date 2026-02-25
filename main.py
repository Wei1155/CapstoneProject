from auth.register import register
from auth.login import login
from profile_management import profile_menu

def main():
    while True:
        print("\n=== Gamified E-Learning System ===")
        print("1. Register")
        print("2. Login")
        print("3. Exit")

        choice = input("Choose option (1-3): ").strip()
        if not choice.isdigit():
            print("❌ Please enter numbers only")
            continue

        choice = int(choice)

        if choice == 1:
            username, role = register()  # auto-login
            if username and role:
                print(f"🎉 Automatically logged in as {username} ({role})!")
                profile_menu(username)
        elif choice == 2:
            username, role = login()
            if role:
                profile_menu(username)
        elif choice == 3:
            print("Exiting system...")
            break
        else:
            print("❌ Please choose 1-3 only")


if __name__ == "__main__":
    main()