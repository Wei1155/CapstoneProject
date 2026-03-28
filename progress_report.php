<?php
include "get_progress_data.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Progress Report</title>
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

        <!-- LEFT -->
        <div class="left">
            <div class="card level-card">
                <p>LEVEL: <?php echo $level; ?></p>

                <p>XP Progress</p>
                <div class="xp-bar">
                    <div class="xp-fill" style="width: <?php echo $xp_percent; ?>%;"></div>
                </div>
            </div>

            <p>RANK PLACE: <?php echo $rank_position; ?></p>
            <p>TOTAL BADGES: <?php echo $badges_count; ?></p>
            <p>QUIZZES DONE: <?php echo $totalq; ?></p>
            <p>AVERAGE SCORE: <?php echo round($avg_score, 2); ?></p>
        </div>

        <!-- RIGHT -->
        <div class="right">

            <!-- BADGES -->
            <div class="card badge-box">
                <h3>LATEST BADGES</h3>

                <div class="badge-container">
                    <?php
                    if ($badgeinfo_result && $badgeinfo_result->num_rows > 0) {
                        while ($badge = $badgeinfo_result->fetch_assoc()) {
                            ?>
                            <div class="badge">
                                <img src="<?php echo $badge['badge_icon']; ?>">
                                <p><?php echo $badge['badge_name']; ?></p>
                            </div>
                            <?php
                        }
                    }else echo "No badges earned yet!"
                    ?>
                </div>
            </div>

            <!-- STATS -->
            <div class="stats-student">
                <div class="card small">
                    <p>Most Done Category</p>
                    <b><?php echo $top_category; ?></b>
                </div>

                <div class="card small">
                    <p>Best Avg Score</p>
                    <b><?php echo round($best_avg_score, 2); ?></b>
                </div>

                <div class="card small">
                    <p>Total Courses</p>
                    <b><?php echo $total_courses; ?></b>
                </div>
            </div>

        </div>

    </div>

</div>

</body>
</html>
    