<?php
/*
This file is part of PYCTS, the PY151 Credit Tracking System.

PYCTS is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

PYCTS is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with PYCTS.  If not, see <http://www.gnu.org/licenses/>.

PYCTS and this file are Copyright 2011 by Mark Platek.
*/


require_once( "includes/html_print.php" );
require_once( "includes/db.php" );

if( !isset($_SESSION['active']) ) {
	print_system_message("Invalid Session", "You can't use this page until you log in.", "index.php");
	exit;
}

$is_repeat_action = check_action_timestamp();

if( !$is_repeat_action) {
	if(isset($_POST['quick_add'])){
		$message = display_quick_add($_POST['st_id']);
	}else if( isset($_POST['create']) ){
        $message = br_create_backup();
	}else
		$message = '';

}
else
	$message = '';

print_action_result($message);

echo '<div id="display">';

$all_students = get_all_students_credits($_GET['sort'], $_GET['dir']);
$tot_students = count($all_students);

if( $tot_students == 0 ) {
	echo '<p class="message">Nothing to display.</p>';
	echo '</div> <!-- closing div "display" -->';
	return;
}

/* if there is a search GET set, we need to trim the list of students shown */
if( isset($_GET['filter_element']) ) {
	$students = array();
	if( $_GET['filter'] == '' ) {
		;
	}
	else if( $_GET['filter_element'] == 'First Name' ) {
		foreach( $all_students as $student  ) {
			if( stristr($student['u_fname'], $_GET['filter']) )
				$students[] = $student;
		}
	}
	else if( $_GET['filter_element'] == 'Last Name' ) {
		foreach( $all_students as $student  ) {
			if( stristr($student['u_lname'], $_GET['filter']) )
				$students[] = $student;
		}
	}
	else if( $_GET['filter_element'] == 'AD Username' ) {
		foreach( $all_students as $student  ) {
			if( stristr($student['u_ad'], $_GET['filter']) )
				$students[] = $student;
		}
	}
	else if( $_GET['filter_element'] == 'Professor' ) {
		foreach( $all_students as $student  ) {
			if( stristr($student['u_prof'], $_GET['filter']) )
				$students[] = $student;
		}
	}
	else if( $_GET['filter_element'] == 'All Fields' || $_GET['filter_element'] == 'Search by...' ) {
		foreach( $all_students as $student  ) {
			if( stristr($student['u_lname'], $_GET['filter']) ||
				stristr($student['u_fname'], $_GET['filter']) ||
				stristr($student['u_ad'], $_GET['filter']) ||
				stristr($student['u_prof'], $_GET['filter'])
			)
				$students[] = $student;
		}
	}
	else {
		$students = $all_students;
	}
}
else {
	$students = $all_students;
}

$num_students_shown = count($students);

if( $num_students_shown == 0 ) {
	echo '<p class="message">No students match that search criteria.</p>';
	echo '<p class="message"><a class="message" href="user.php">Return</a></p>';
	echo '</div> <!-- closing div "display" -->';
	return;
}

$last_sort = $_GET['sort'];

if( isset($_GET['sort']) ) {		
	if( $_GET['dir'] == "asc")
		$dir = "desc";
	else
		$dir = "asc";
}
else
	$dir = "desc";

$studies = get_studies();

echo '<form id="roster_select" method="post" action="user.php">';
echo '<input type="hidden" name="action_timestamp" value="' . time() . '"/>';
echo '<div id="display_quickadd">';

if( empty($studies) ) {
	echo "<p>No studies exist yet. You have to create at least one before you can use quick-add.</p>";
}
else {

echo <<<EOF
<dl>
<dt>Study:</dt>
	<dd>
	<select name="st_id">
EOF;

	foreach( $studies as $study ) {
		echo "<option value=\"$study[st_id]\">IRB #$study[st_irb]: $study[st_desc] [$study[st_credits] credits]</option>";
	}

echo <<<EOF
	</select>
	</dd>

<dt>Description:</dt>
	<dd>
	<input class="text" type="text" name="desc"/> <span class="input_explanation">(Not required)</span>
	</dd>

EOF;

$block_num = 0;
$blocks = get_blocks();
$block_count = count($blocks)-1;
$block_num = get_current_block();

echo "<dt>Block:</dt>";
echo "<dd>";
echo "<select name=\"block_num\">";
for( $i=1; $i<=$block_count; ++$i ) {
	echo "<option value=\"$i\"";
	if( $i == $block_num )
		echo ' selected="selected"';
	echo ">$i</option>";
}
echo "</select>";
echo "</dd>";
echo "</dl>";
echo <<<EOF


<p>
	<input type="submit" name="quick_add" value="Add Credits to Selection"/>
</p>
EOF;
}

echo <<<EOF
<p id="student_count">
Showing $num_students_shown/$tot_students students.
</p>

</div> <!-- closing div "display_quickadd" -->

<div id="display_roster">
<table border="1">
<tr>
EOF;
	echo '<th><a href="user.php?sort=u_lname&dir='.$dir.'">Last Name</a></th>';
	echo '<th><a href="user.php?sort=u_fname&dir='.$dir.'">First Name</a></th>';
	echo '<th><a href="user.php?sort=ad&dir='.$dir.'">AD Username</a></th>';
	echo '<th><a href="user.php?sort=u_prof&dir='.$dir.'">Professor</a></th>';
	echo '<th><a href="user.php?sort=Total&dir='.$dir.'">Total</a></th>';
	for($i = 1; $i <= $block_count; ++$i) {
		$str = "B".strval($i)."Total";
		echo "<th><a href=\"user.php?sort=$str&dir=".$dir."\">Block $i</a></th>";		
	}
	
echo <<<EOF
	<th>
		Select<br/>
		<input type="button" value="All" onclick="checkall(true)"/>
		<input type="button" value="None" onclick="checkall(false)"/>
	</th>
</tr>
EOF;

$c = 1;
foreach( $students as $student ) {
	if( $c == 1 )
		$c = 0;
	else
		$c = 1;
	echo "<tr class=\"color$c\">";

echo <<<EOF

	<td><a href="user.php?studentdisplay=$student[ad]">$student[lname]</a></td>
	<td>$student[fname]</td>
	<td>$student[ad]</td>
	<td>$student[prof]</td>

	<td class="center">$student[credits]</td>

EOF;
	for($i = 1; $i <= $block_count; ++$i) {
		$str = "credits_b".strval($i);
		echo "<td class=\"center\">$student[$str]</td>";		
	}
	
	echo '<td class="center"><input type="checkbox" name="' . $student['ad'] . '" value="' . $student['ad'] . '"/></td>';
	echo '</tr>';
}

echo '</table></div> <!-- closing div "display_roster" -->';
echo '</form>';

echo '</div> <!-- closing div "display" --> ';

/* if there is only one student, pre-select the checkbox */
/* this feature is useful in conjunction with searches for a specific student */
if( $num_students_shown == 1 ) {
	echo '<script type="text/javascript">checkall(true)</script>';
}



/* -------------------------------------------------------------------------- */
/* display functions (all involve adding/removing credits) */
/* -------------------------------------------------------------------------- */
function display_quick_add($st_id) {
	/* the first four elements in the $_POST array are not ADs, so we need to strip them out */
	$student_ads = array();
	$keys = array_keys($_POST);
	$count = count($_POST);
	for($i=5; $i<$count; $i++) {
		$student_ads[] = $keys[$i];
	}

	if( sizeof($student_ads) == 0 )
		return 'ERROR: No students selected.';
	$studies = get_studies();
	$num_points = $studies[$_POST['st_id']]['st_credits'];
	/* valid study ID not checked for since it always comes from a dropdown */
	$result = insert_credits($student_ads, $num_points, $_POST['st_id'], $_POST['desc'], $_SESSION['ad'], $_POST['block_num']);
	if( $result )
		return 'Credits were added successfully.';
	return 'ERROR: Credits could not be added. Not all credits could be added successfully, please try again.';
}

?>
