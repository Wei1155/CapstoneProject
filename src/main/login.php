<?php
session_start();
include 'db_connection.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $emailOrUsername = trim($_POST['emailOrUsername']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $emailOrUsername, $emailOrUsername);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $row = $result->fetch_assoc();

        if ($password == $row['password']) {

            // Save user info into session
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['first_name'] = $row['first_name'];
            $_SESSION['last_name'] = $row['last_name'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['profile_picture'] = $row['profile_picture'];
            $_SESSION['role'] = $row['role'];

            // Redirect based on role
            if ($row['role'] == "Student") {
                header("Location: student_dashboard.php");
                exit();
            }

            elseif ($row['role'] == "Instructor") {
                header("Location: instructor_dashboard.php");
                exit();
            }

            elseif ($row['role'] == "Admin") {
                header("Location: admin_dashboard.php");
                exit();
            }

            else {
                $error = "Invalid role.";
            }

        } else {
            $error = "Incorrect password";
        }

    } else {
        $error = "User not found";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login - Gamified E-Learning</title>
    <link rel="stylesheet" href="../css/login.css">
</head>

<body>

<div class="login-box">

    <h2>Gamified E-Learning</h2>
    <p>Start your learning journey</p>

    <?php
    if ($error != "") {
        echo "<p style='color:red;'>$error</p>";
    }
    ?>

    <form method="POST">

        <label>Email or Username</label>
        <input type="text" name="emailOrUsername" placeholder="example@email.com" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Sign In</button>

    </form>

    <p class="register-text">
        Don't have an account? <a href="register.php">Sign Up</a>
    </p>

</div>

</body>
</html>