<?php
include "get_performance_data.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Instructor Course Performance List</title>
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

        <!-- LEFT: COURSES -->
        <div class="course-list">

            <?php
            if ($courseinfo_results && $all_courses > 0) {
                while ($courses = $courseinfo_results->fetch_assoc()) {
            ?>
                <div class="course-card">
                    <img src="<?php echo $courses['course_image']; ?>">

                    <div class="course-info">
                        <p><b><?php echo $courses['course_title']; ?></b></p>
                        <p><?php echo $courses['category']; ?></p>
                        <p>Level: <?php echo $courses['level']; ?></p>
                        <p>⭐ <?php echo $courses['rating']; ?></p>
                        <p><?php echo $courses['created_at']; ?></p>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "No courses made yet!";
            }
            ?>

        </div>

        <!-- RIGHT: STATS -->
        <div class="stats-course">

            <!-- Engagement -->
            <div class="circle-card">
                <div class="circle" style="--value: <?php echo $engagement; ?>;">
                    <span><?php echo round($engagement,1); ?>%</span>
                </div>
                <p>ENGAGEMENT</p>
            </div>

            <!-- Avg Score -->
            <div class="circle-card">
                <div class="circle blue" style="--value: <?php echo $overall_avg_score; ?>;">
                    <span><?php echo round($overall_avg_score,1); ?>%</span>
                </div>
                <p>AVG SCORE</p>
            </div>

            <!-- Course Count -->
            <div class="circle-card">
                <div class="circle static">
                    <span><?php echo $total_courses; ?></span>
                </div>
                <p>COURSES</p>
            </div>

            <!-- Student Count -->
            <div class="circle-card">
                <div class="circle static">
                    <span><?php echo $student_count; ?></span>
                </div>
                <p>STUDENTS</p>
            </div>

        </div>

    </div>

</div>

</body>
</html>