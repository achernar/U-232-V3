<?php
//==09 Birthday mod
    $age = $birthday ='';
    if ($user['birthday'] != "0000-00-00") {
    $current = date("Y-m-d", time());
    list($year2, $month2, $day2) = explode('-', $current);
    $birthday = $user["birthday"];
    $birthday = date("Y-m-d", strtotime($birthday));
    list($year1, $month1, $day1) = explode('-', $birthday);
    if ($month2 < $month1) {
        $age = $year2 - $year1 - 1;
    }
    if ($month2 == $month1) {
        if ($day2 < $day1) {
            $age = $year2 - $year1 - 1;
        } else {
            $age = $year2 - $year1;
        }
    }
    if ($month2 > $month1) {
        $age = $year2 - $year1;
    }
    $HTMLOUT .="<tr><td class='rowhead'>Age</td><td align='left'>".htmlentities($age)."</td></tr>\n";
    $birthday = date("Y-m-d", strtotime($birthday));
    $HTMLOUT .="<tr><td class='rowhead'>Birthday</td><td align='left'>".htmlentities($birthday)."</td></tr>\n";
    }
//==End
// End Class

// End File