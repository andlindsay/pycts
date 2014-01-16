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

if(  !$is_repeat_action ) {
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

$all_students = get_all_students_credits($_GET);
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
			if( stristr($student['s_fname'], $_GET['filter']) )
				$students[] = $student;
		}
	}
	else if( $_GET['filter_element'] == 'Last Name' ) {
		foreach( $all_students as $student  ) {
			if( stristr($student['s_lname'], $_GET['filter']) )
				$students[] = $student;
		}
	}
	else if( $_GET['filter_element'] == 'AD Username' ) {
		foreach( $all_students as $student  ) {
			if( stristr($student['s_ad'], $_GET['filter']) )
				$students[] = $student;
		}
	}
	else if( $_GET['filter_element'] == 'Professor' ) {
		foreach( $all_students as $student  ) {
			if( stristr($student['s_prof'], $_GET['filter']) )
				$students[] = $student;
		}
	}
	else if( $_GET['filter_element'] == 'All Fields' || $_GET['filter_element'] == 'Search by...' ) {
		foreach( $all_students as $student  ) {
			if( stristr($student['s_lname'], $_GET['filter']) ||
				stristr($student['s_fname'], $_GET['filter']) ||
				stristr($student['s_ad'], $_GET['filter']) ||
				stristr($student['s_prof'], $_GET['filter'])
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

/* if we need to reorder the list of students, do it now */
if( isset($_GET['sort']) ) {
	if( $_GET['sort'] == 'lname' )
		usort( $students, 'compare_students_lname' );
	else if( $_GET['sort'] == 'fname' )
		usort( $students, 'compare_students_fname' );
	else if( $_GET['sort'] == 'ad' )
		usort( $students, 'compare_students_ad' );
	else if( $_GET['sort'] == 'prof' )
		usort( $students, 'compare_students_prof' );
	else if( $_GET['sort'] == 'credits' )
		usort( $students, 'compare_students_credits' );
	else if( $_GET['sort'] == 'credits_b1' )
		usort( $students, 'compare_students_credits_b1' );
	else if( $_GET['sort'] == 'credits_b2' )
		usort( $students, 'compare_students_credits_b2' );
	else if( $_GET['sort'] == 'credits_b3' )
		usort( $students, 'compare_students_credits_b3' );
}

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
$block_one_start = $blocks[1];
$block_two_start = $blocks[2];
$block_three_start = $blocks[3];
$semester_end = $blocks[4];
if(time() >= $block_one_start && time() < $block_two_start) {
        $block_num = 1;
} else if(time() >= $block_two_start && time() < $block_three_start) {
        $block_num = 2;
} else if(time() >= $block_three_start && time() < $semester_end) {
        $block_num = 3;
} else {
        $block_num = 3;
}

echo "<dt>Block:</dt>";
echo "<dd>";
echo "<select name=\"block_num\">";
if( $block_num == 1 )
{
        echo "<option value=\"1\" selected=\"selected\">1</option>";
        echo "<option value=\"2\">2</option>";
        echo "<option value=\"3\">3</option>";
}
else if( $block_num == 2 )
{
        echo "<option value=\"1\">1</option>";
        echo "<option value=\"2\" selected=\"selected\">2</option>";
        echo "<option value=\"3\">3</option>";
}
else if( $block_num == 3 )
{
        echo "<option value=\"1\">1</option>";
        echo "<option value=\"2\">2</option>";
        echo "<option value=\"3\" selected=\"selected\">3</option>";
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

<form action="user.php" method="post">
<p>
<input type="submit" name="create" value="Create Backup"/>
</p>
</form>

</div> <!-- closing div "display_quickadd" -->
EOF;

if( isset( $_GET['filter_element'] ) ) {
	$extraget = "&amp;filter_element=$_GET[filter_element]&amp;filter=$_GET[filter]";
}
else {
	$extraget = '';
}

echo <<<EOF
<div id="display_roster">
<table border="1">
<tr>
	<th><a href="user.php?sort=lname$extraget">Last Name</a></th>
	<th><a href="user.php?sort=fname$extraget">First Name</a></th>
	<th><a href="user.php?sort=ad$extraget">AD Username</a></th>
	<th><a href="user.php?sort=prof$extraget">Professor</a></th>
	<th><a href="user.php?sort=credits$extraget">Total</a></th>
	<th><a href="user.php?sort=credits$extraget">Block 1</a></th>
	<th><a href="user.php?sort=credits$extraget">Block 2</a></th>
	<th><a href="user.php?sort=credits$extraget">Block 3</a></th>
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

	<td><a href="user.php?studentdisplay=$student[s_ad]">$student[s_lname]</a></td>
	<td>$student[s_fname]</td>
	<td>$student[s_ad]</td>
	<td>$student[s_prof]</td>

	<td class="center">$student[s_credits]</td>
	<td class="center">$student[s_credits_b1]</td>
	<td class="center">$student[s_credits_b2]</td>
	<td class="center">$student[s_credits_b3]</td>
EOF;

	echo '<td class="center"><input type="checkbox" name="' . $student['s_ad'] . '" value="' . $student['s_ad'] . '"/></td>';
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
	for($i=4; $i<$count; $i++) {
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

/* these are used for sorting students */
function compare_students_lname( $a, $b ) {
	return strnatcmp( $a['s_lname'], $b['s_lname'] );
}
function compare_students_fname( $a, $b ) {
	return strnatcmp( $a['s_fname'], $b['s_fname'] );
}
function compare_students_ad( $a, $b ) {
	return strnatcmp( $a['s_ad'], $b['s_ad'] );
}
function compare_students_prof( $a, $b ) {
	return strnatcmp( $a['s_prof'], $b['s_prof'] );
}
function compare_students_credits( $a, $b ) {
	return ( -1 * strnatcmp( $a['s_credits'], $b['s_credits'] ) );
}
function compare_students_credits_b1( $a, $b ) {
	return ( -1 * strnatcmp( $a['s_credits_b1'], $b['s_credits_b1'] ) );
}
function compare_students_credits_b2( $a, $b ) {
	return ( -1 * strnatcmp( $a['s_credits_b2'], $b['s_credits_b2'] ) );
}
function compare_students_credits_b3( $a, $b ) {
	return ( -1 * strnatcmp( $a['s_credits_b3'], $b['s_credits_b3'] ) );
}

?>
