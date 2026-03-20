<?php
session_start();
include 'db_connection.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    $profilePicture = "default_profile.png";
    $role = "Student";

    if ($username == "" || $firstName == "" || $lastName == "" || $email == "" || $password == "" || $confirmPassword == "") {
        $error = "Please fill in all fields.";
    } elseif ($password != $confirmPassword) {
        $error = "Passwords do not match.";
    } else {

        $checkStmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $checkStmt->bind_param("ss", $email, $username);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email or username already exists.";
        } else {

            $insertStmt = $conn->prepare("INSERT INTO users (username, first_name, last_name, email, password, profile_picture, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insertStmt->bind_param("sssssss", $username, $firstName, $lastName, $email, $password, $profilePicture, $role);

            if ($insertStmt->execute()) {

                session_unset();

                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['first_name'] = $firstName;
                $_SESSION['last_name'] = $lastName;
                $_SESSION['email'] = $email;
                $_SESSION['profile_picture'] = $profilePicture;
                $_SESSION['role'] = $role;

                header("Location: student_dashboard.php");
                exit();

            } else {
                $error = "Registration failed.";
            }

            $insertStmt->close();
        }

        $checkStmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Gamified E-Learning</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>

<div class="login-box">

    <h2>Create Account</h2>
    <p>Join the Gamified E-Learning System</p>

    <?php if ($error != "") { ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php } ?>

    <form method="POST">

        <label>Username</label>
        <input type="text" name="username" required>

        <label>First Name</label>
        <input type="text" name="first_name" required>

        <label>Last Name</label>
        <input type="text" name="last_name" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required>

        <button type="submit">Register</button>

    </form>

    <p class="register-text">
        Already have an account? <a href="login.php">Login</a>
    </p>

</div>

</body>
</html>