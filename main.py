from auth.register import register
from auth.login import login

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
            register()
        elif choice == 2:
            role = login()
            if role == "student":
                print("🎮 Welcome Student!")
            elif role == "admin":
                print("👑 Welcome Admin!")
        elif choice == 3:
            print("Exiting...")
            break
        else:
            print("❌ Please choose 1-3 only")


if __name__ == "__main__":
    main()