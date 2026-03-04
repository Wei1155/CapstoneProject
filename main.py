from auth.login import login_user
from auth.register import register_user
from students.student_menu import student_menu
from instructors.instructor_menu import instructor_menu
from admin.admin_menu import admin_menu


def main():
    while True:
        print("\n=== Gamified E-Learning System ===")
        print("1. Login")
        print("2. Register")
        print("3. Exit")

        choice = input("Select an option: ").strip()

        if choice == "1":
            user = login_user()  # loops until login is successful
            if user:
                # Redirect based on user type
                if user['user_type'].lower() == "student":
                    student_menu(user)
                elif user['user_type'].lower() == "instructor":
                    instructor_menu(user)
                elif user['user_type'].lower() == "admin":
                    admin_menu(user)

        elif choice == "2":
            # Register and automatically log in the new user
            new_user = register_user()  # returns the exact user just created
            if new_user:
                print(
                    f"\nAutomatically logging in {new_user['first_name']} {new_user['last_name']} ({new_user['user_type']})...")

                if new_user['user_type'].lower() == "student":
                    student_menu(new_user)
                elif new_user['user_type'].lower() == "instructor":
                    instructor_menu(new_user)
                elif new_user['user_type'].lower() == "admin":
                    admin_menu(new_user)

        elif choice == "3":
            print("Exiting system. Goodbye!")
            break
        else:
            print("Invalid choice. Try again.")


if __name__ == "__main__":
    main()