<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['user_id'])) {
    header("Location: admin_users.php");
    exit();
}

$userId = (int) $_GET['user_id'];
$error = "";

$stmt = $conn->prepare("
    SELECT user_id, username, first_name, last_name, email, role
    FROM users
    WHERE user_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['admin_error'] = "User not found.";
    header("Location: admin_users.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);

    if ($username == "" || $firstName == "" || $lastName == "" || $email == "" || $role == "") {
        $error = "Please fill in all fields.";
    } else {
        $checkStmt = $conn->prepare("
            SELECT user_id
            FROM users
            WHERE (username = ? OR email = ?) AND user_id != ?
        ");
        $checkStmt->bind_param("ssi", $username, $email, $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $error = "Username or email already exists.";
        } else {
            $updateStmt = $conn->prepare("
                UPDATE users
                SET username = ?, first_name = ?, last_name = ?, email = ?, role = ?
                WHERE user_id = ?
            ");
            $updateStmt->bind_param("sssssi", $username, $firstName, $lastName, $email, $role, $userId);

            if ($updateStmt->execute()) {
                $_SESSION['admin_success'] = "User updated successfully!";
                header("Location: admin_users.php");
                exit();
            } else {
                $error = "Failed to update user.";
            }

            $updateStmt->close();
        }

        $checkStmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="../css/admin_users.css">
</head>
<body>
    <div class="form-page">
        <div class="form-card">
            <h1>Edit User</h1>

            <?php if ($error != "") { ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php } ?>

            <form method="POST">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

                <label>First Name</label>
                <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>

                <label>Last Name</label>
                <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>

                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                <label>Role</label>
                <select name="role" required>
                    <option value="Admin" <?php echo ($user['role'] == "Admin") ? "selected" : ""; ?>>Admin</option>
                    <option value="Instructor" <?php echo ($user['role'] == "Instructor") ? "selected" : ""; ?>>Instructor</option>
                    <option value="Student" <?php echo ($user['role'] == "Student") ? "selected" : ""; ?>>Student</option>
                </select>

                <div class="form-actions">
                    <button type="submit" class="save-btn">Update</button>
                    <a href="admin_dashboard.php" class="cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>