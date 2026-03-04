from file_reader import *

def load_users():
    return read_file("txtdata/users.txt")


def load_rewards():
    return read_file("txtdata/rewards.txt")

def generate_store():

    rewards = load_rewards()

    f = open("txtdata/store.txt", "w")

    for r in rewards:
        if r != "":
            f.write(r + "\n")

    f.close()

def get_points(studentId):

    users = load_users()

    for line in users:
        if line == "":
            continue

        parts = line.split("|")

        if parts[0] == studentId:
            return int(parts[5])

    return 0


def redeem(userId, rewardId):

    users = load_users()
    rewards = load_rewards()

    new_users = []
    new_rewards = []

    cost = 0
    stock = 0

    for r in rewards:
        if r == "":
            continue

        parts = r.split("|")

        if parts[0] == rewardId:
            cost = int(parts[2])
            stock = int(parts[3])

    points = get_points(userId)

    if points < cost or stock <= 0:
        return "FAILED"

    for u in users:
        if u == "":
            continue

        parts = u.split("|")

        if parts[0] == userId:
            parts[5] = str(points - cost)

        new_users.append("|".join(parts))

    for r in rewards:
        if r == "":
            continue

        parts = r.split("|")

        if parts[0] == rewardId:
            parts[3] = str(int(parts[3]) - 1)

        new_rewards.append("|".join(parts))

    write_file("data/students.txt", new_users)
    write_file("data/rewards.txt", new_rewards)

    return "SUCCESS"