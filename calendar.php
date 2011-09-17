<?php
    require 'lib/common.php';
    
    $daynames = array('Sunday','Monday','Tuesday','Wednesday',
                      'Thursday','Friday','Saturday');
    $monthnames = array(1=>'January',  'Febuary','March',   'April',
                           'May',      'June',   'July',    'August',
                           'September','October','November','December');
    
    $today = getdate(ctime());
    
    if (isset($_REQUEST['m']) && is_numeric($_REQUEST['m'])) {
        $month = $_REQUEST['m'];
    } else {
        $month = $today['mon'];
    }
    if (isset($_REQUEST['y']) && is_numeric($_REQUEST['y'])) {
        $year = $_REQUEST['y'];
    } else {
        $year = $today['year'];
    }
    if (isset($_REQUEST['d']) && is_numeric($_REQUEST['d'])) {
        $day = $_REQUEST['d'];
    } else if ($year == $today['year'] && $month == $today['mon']) {
        $day = $today['mday'];
    } else {
        $day = 0;
    }
        
    
    $mtstamp = mktime(0,0,0,$month,1,$year);
    $mdays = intval(date('t', $mtstamp));
    $wday = intval(date('w', $mtstamp));
    
    //Fetch birthdays - uses the same crude but effective method as the old one
    $bdaytext = array();
    $bdayres = $sql->query("SELECT id,name,sex,power,birth FROM users WHERE birth != -1");
    while ($bdayarr = $sql->fetch($bdayres)) {
        $bdaydecode = getdate($bdayarr['birth']);
        if ($bdaydecode['mon'] == $month) {
            $age = $year - $bdaydecode['year'];
            $t = userlink($bdayarr);
            if ($age > 0) {
                $t .= " turns $age";
            } else if ($age < 0) {
                $t .= " is born in ".(-$age)." year".(($age!=-1)?'s':'');
            } else {
                $t .= " is born";
            }
            if (isset($bdaytext[$bdaydecode['mday']])) {
                $bdaytext[$bdaydecode['mday']] .= '<br/>'.$t;
            } else {
                $bdaytext[$bdaydecode['mday']] = $t;
            }
        }
    }
    
    pageheader('Calendar');
    print "$L[TBL] width=\"100%\">
".        "    $L[TR]>
".        "        $L[TDc] colspan=7 style=\"font-size:200%\">$monthnames[$month] $year</td>
".        "    </tr>
".        "    $L[TRh]>
";

    for ($w = 0; $w < 7; $w++) {//days of the week
        print "        $L[TDh] width=\"14%\">$daynames[$w]</td>\n";
    }
    
    print "    </tr>
".        "    $L[TR] style=\"height:80\">\n";

    for ($w = 0; $w < $wday; $w++) {//unused cells in the first week
        print "$L[TD]></td>";
    }

    for ($mday = 1; $mday <= $mdays; $mday++, $wday++) {//main day cells
        if ($wday > 6) {  //week wrap around
            $wday = 0;
            print "</tr>$L[TR] style=\"height:80\">\n";
        }
        $l = ($mday == $day) ? $L['TD3l'] : (($wday==0 || $wday==6) ? $L['TD2l'] : $L['TD1l']);
        print "$l width=\"14%\" valign=\"top\">$mday";
        if (isset($bdaytext[$mday])) {
            print "<br/>$bdaytext[$mday]";
        }
        print "</td>\n";
        
    }
    
    for (;$wday <= 6; $wday++) { //unused cells in the last week
        print "$L[TD]></td>";
    }
    
    print "    </tr>
".        "    $L[TR]>
".        "        $L[TDc] colspan=7> Month:";
    
    for ($i = 1; $i <= 12; $i++) {//month links
        if ($i == $month) {
            print " $i\n";
        } else {
            print " <a href=\"calendar.php?m=$i&amp;y=$year\">$i</a>\n";
        }
    }
    
    print "             | Year:\n";
    
    for ($i = $year-2; $i <= $year+2; $i++) {//year links
        if ($i == $year) {
            print " $i\n";
        } else {
            print " <a href=\"calendar.php?m=$month&amp;y=$i\">$i</a>\n";
        }
    }
    
    print "        </td>
".        "    </tr>
".         $L['TBLend'];
    
        

    pagefooter();
?>