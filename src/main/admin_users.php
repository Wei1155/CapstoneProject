<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {
    header("Location: login.php");
    exit();
}

$firstName = $_SESSION['first_name'];
$username = $_SESSION['username'];
$profilePic = !empty($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'default_profile.png';

$search = isset($_GET['search']) ? trim($_GET['search']) : "";

if ($search != "") {
    $stmt = $conn->prepare("
        SELECT user_id, username, first_name, last_name, email, role
        FROM users
        WHERE username LIKE ? 
           OR first_name LIKE ? 
           OR last_name LIKE ? 
           OR email LIKE ?
        ORDER BY user_id ASC
    ");
    $searchValue = "%" . $search . "%";
    $stmt->bind_param("ssss", $searchValue, $searchValue, $searchValue, $searchValue);
} else {
    $stmt = $conn->prepare("
        SELECT user_id, username, first_name, last_name, email, role
        FROM users
        ORDER BY user_id ASC
    ");
}

$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Administration</title>
    <link rel="stylesheet" href="../css/admin_users.css">
</head>
<body>
    <div class="dashboard-page">

        <aside class="sidebar">
            <div class="sidebar-logo">GamiLearn</div>

            <nav class="sidebar-nav">
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="admin_users.php" class="active">Users</a>
                <a href="create_course.php">Courses</a>
                <a href="generate_report.php">Reports</a>
                <a href="view_logs.php">Logs</a>
                <a href="profile.php">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-title">
                    <h1>User Administration</h1>
                    <p>Manage users and assign roles</p>
                </div>

                <div class="topbar-actions">
                    <div class="profile-chip">
                        <img src="../media/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                        <span><?php echo htmlspecialchars($username); ?></span>
                    </div>
                </div>
            </header>

            <section class="admin-section">
                <div class="toolbar">
                    <form method="GET" class="search-form">
                        <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                        <button type="submit" class="search-btn">Search</button>
                    </form>

                    <a href="add_user.php" class="add-btn">+ Add User</a>
                </div>

                <?php
                if (isset($_SESSION['admin_success'])) {
                    echo "<div class='success-msg'>" . $_SESSION['admin_success'] . "</div>";
                    unset($_SESSION['admin_success']);
                }

                if (isset($_SESSION['admin_error'])) {
                    echo "<div class='error-msg'>" . $_SESSION['admin_error'] . "</div>";
                    unset($_SESSION['admin_error']);
                }
                ?>

                <div class="table-card">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0) { ?>
                                <?php foreach ($users as $user) { ?>
                                    <tr>
                                        <td><?php echo $user['user_id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                                        <td class="action-cell">
                                            <a href="edit_user.php?user_id=<?php echo $user['user_id']; ?>" class="edit-btn">Edit</a>
                                            <a href="delete_user.php?user_id=<?php echo $user['user_id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="7">No users found.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>