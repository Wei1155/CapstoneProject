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

    if ($password != $confirmPassword) {
        $error = "Passwords do not match";
    } else {

        $checkStmt = $conn->prepare("SELECT * FROM students WHERE email=? OR username=?");
        $checkStmt->bind_param("ss", $email, $username);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {

            $error = "Email or username already exists";

        } else {

            $insertStmt = $conn->prepare("INSERT INTO students (username, first_name, last_name, email, password) VALUES (?, ?, ?, ?, ?)");
            $insertStmt->bind_param("sssss", $username, $firstName, $lastName, $email, $password);

            if ($insertStmt->execute()) {

                // AUTO LOGIN
                $_SESSION['username'] = $username;
                $_SESSION['first_name'] = $firstName;
                $_SESSION['last_name'] = $lastName;

                header("Location: dashboard.php");
                exit();

            } else {
                $error = "Registration failed";
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

    <?php
    if ($error != "") {
        echo "<p style='color:red;'>$error</p>";
    }
    ?>

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
    </p>

</div>

</body>
</html>