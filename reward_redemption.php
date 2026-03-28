<?php
include "db_connection.php";
$user_id = $_SESSION['user_id'];

// REDEEM BTN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['badge_id'])) {

    $badge_id = intval($_POST['badge_id']);

    $check = "SELECT * FROM student_badges 
              WHERE user_id = $user_id AND badge_id = $badge_id";
    $result = $conn->query($check);

    if ($result->num_rows == 0) {
        $sql = "INSERT INTO student_badges (user_id, badge_id, earned_at)
                VALUES ($user_id, $badge_id, NOW())";
        $conn->query($sql);
    }

    // Redirect to same page to avoid resubmission
    header("Location: reward_redemption.php");
    exit();
}
include "get_reward_data.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Badges</title>
    <link rel="stylesheet" href="4.0styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>

<div class="container">

    <!-- TOP BAR -->
    <div class="topbar">
        <a href="home.php" class="home">⬅ HOME</a>

        <div class="profile">
            <span><?php echo $username; ?></span>
            <img src="<?php echo $profile_pic; ?>" class="pfp">
        </div>
    </div>

    <div class="grid">
        <div class="card badge-box">
            <h3>BADGES</h3>
            <div class="badge-container enhanced">
                <?php
                if (!empty($all_badges)) {
                    foreach ($all_badges as $badge) {
                    $badge_id = $badge['badge_id'];
                ?>
                <div class="badge-card">
                    <img src="<?php echo $badge['badge_icon']; ?>" class="badge-icon">
                    <p class="badge-name"><?php echo $badge['badge_name']; ?></p>
                    <?php
                    // CLAIMED
                    if (in_array((int)$badge_id, array_map('intval', $earned_badges))) {
                        echo "<button class='btn claimed' disabled>Claimed</button>";

                    // ELIGIBLE
                    } elseif (in_array($badge_id, $eligible_badges)) {
                    ?>
                    <form method="POST" action="reward_redemption.php">
                        <input type="hidden" name="badge_id" value="<?php echo $badge_id; ?>">
                        <button class='btn redeem' onclick='this.disabled=true;'>Redeem</button>
                    </form>
                    <?php

                // LOCKED
                } else {
                    echo "<button class='btn locked' disabled>Locked</button>";
                }
                ?>
                </div>
                <?php
                    }
                } else {
                    echo "No badges available!";
                }
                ?>
            </div>
        </div>
    </div>