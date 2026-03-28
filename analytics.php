<?php
function calcXpPercentage($xp, $level) {
    $max_xp = 3000;

    while ($xp >= $max_xp) {
        $xp -= $max_xp;
        $level++;
    }

    $xp_percent = ($xp / $max_xp) * 100;
    $xp_percent = min($xp_percent, 100);

    return [
        'xp' => $xp,
        'level' => $level,
        'xp_percent' => $xp_percent
    ];
}

function engagementLevel($enrolled, $completed){
    $enrolled = $enrolled ?? 0;
    $completed = $completed ?? 0;
        $engagement = ($enrolled > 0) ? ($completed / $enrolled) * 100 : 0;
    return $engagement;
}
    
?>