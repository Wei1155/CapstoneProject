import datetime


class Student:
    def __init__(self, student_id, name):
        self.student_id = student_id
        self.name = name
        self.total_points = 0
        self.current_level = 1
        self.badges = []
        self.completed_tasks = []

    def save_to_db(self):
        pass


class Task:
    def __init__(self, task_id, name, points_reward):
        self.task_id = task_id
        self.name = name
        self.points_reward = points_reward


AVAILABLE_BADGES = [
    {"name": "First Steps", "points_required": 50},
    {"name": "Rising Scholar", "points_required": 150},
    {"name": "Quiz Master", "points_required": 300}
]


def create_activity_log(student_id, action_description):
    """Creates a log of what the student did."""
    timestamp = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")


    print(f"[LOG] {timestamp} | Student ID: {student_id} | Action: {action_description}")



def check_for_new_badges(student):
    """Checks if the student's points qualify them for a new badge."""
    for badge in AVAILABLE_BADGES:
        if student.total_points >= badge["points_required"] and badge["name"] not in student.badges:
            student.badges.append(badge["name"])
            create_activity_log(student.student_id, f"Earned Badge: {badge['name']}")


def award_points(student, points_earned):
    """Adds points to the student, calculates level, and checks badges."""
    student.total_points += points_earned

    new_level = (student.total_points // 100) + 1

    if new_level > student.current_level:
        student.current_level = new_level
        create_activity_log(student.student_id, f"Leveled up to Level {new_level}!")

    check_for_new_badges(student)

    student.save_to_db()



def complete_task(student, task):
    """Triggers when a student attempts to complete a learning task."""

    if task.task_id not in student.completed_tasks:

        student.completed_tasks.append(task.task_id)

        create_activity_log(student.student_id, f"Completed Task: {task.name}")

        award_points(student, task.points_reward)

        return "Success! Reward claimed."
    else:
        return "You have already claimed the reward for this task."


if __name__ == "__main__":
    student_ali = Student(student_id=101, name="Ali")
    task_quiz1 = Task(task_id=1, name="Chapter 1 Quiz", points_reward=60)
    task_video1 = Task(task_id=2, name="Watch Introduction Video", points_reward=100)

    print(f"--- {student_ali.name} starts with {student_ali.total_points} points ---")

    complete_task(student_ali, task_quiz1)

    complete_task(student_ali, task_quiz1)

    complete_task(student_ali, task_video1)

    print(f"\n--- Final Status for {student_ali.name} ---")
    print(f"Total Points: {student_ali.total_points}")
    print(f"Current Level: {student_ali.current_level}")
    print(f"Badges Earned: {student_ali.badges}")