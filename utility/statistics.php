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


require_once("includes/html_print.php");
require_once("includes/db.php");

if( !isset($_SESSION['active']) || $_SESSION['is_student'] ) {
	echo '<meta http-equiv="refresh" content="0; url=index.php">';
	exit;
}

/* number of credits, students and their average */
$students = get_all_students_credits("Last Name");
/* -------------------------------------------------------------------------- */
$counts = get_totals();
$num_credits = $counts[credits];
if( $num_credits == 0 ) {
	echo '<div class="section">';
	echo '<p>There are no credits in the system.</p>';
	echo '</div>';
	echo '</div>';
	return;
}

$num_students = $counts[students];
$average = $num_credits/$num_students;
$average = number_format($average, 2);
echo '<div class="section">';
echo "<p>There are <span class=\"stats\">$num_credits</span> credits distributed to <span class=\"stats\">$num_students</span> students averaging <span class=\"stats\">$average</span> credits per student.";
echo '</div>';

/* average credits by professor */
/* -------------------------------------------------------------------------- */
echo '<div class="section">';
$profs = array();
foreach( $students as $student ) {
	if( in_array($student['u_prof'], $profs) == false )
		$profs[] = $student['u_prof'];
}

$prof_nums = array();
foreach( $profs as $prof ) {
	$prof_nums[$prof] = array('name' => $prof, 'num_students' => 0, 'tot_credits' => 0);
}

foreach( $students as $student) {
	$prof_nums[$student['u_prof']]['num_students']++;
	$prof_nums[$student['u_prof']]['tot_credits']+=$student['u_credits'];
}

echo <<<EOF
<h2>Average credits per student per professor:</h2>
<table border="1">
	<tr>
		<th>Professor</th>
		<th>Number of students</th>
		<th>Total credits given</th>
		<th>Average credits per student</th>
	</tr>
EOF;

foreach($prof_nums as $prof) {
	echo '<tr>';
	echo "	<td>$prof[name]</td>";
	echo "	<td>$prof[num_students]</td>";
	echo "	<td>$prof[tot_credits]</td>";
	$avg = $prof['tot_credits'] / $prof['num_students'];
	$avg = number_format($avg, 2);
	echo "	<td>$avg</td>";
	echo '</tr>';
}

echo '</table>';
echo '</div>';

/* credit counts */
/* -------------------------------------------------------------------------- */
echo '<div class="section">';
$credit_nums = array();
$highest = 0;
foreach( $students as $student ) {
	if( in_array($student['u_credits'], $credit_nums) == false ) {
		$credit_nums[] = $student['u_credits'];
	}
}
$result = sort($credit_nums);

$credits = array();
foreach( $credit_nums as $credit_num ) {
	$credits[$credit_num] = 0;
}

foreach( $students as $student ) {
	$credits[$student['u_credits']]++;
}

echo <<<EOF
<h2>Students by number of credits:</h2>
<table border="1">
	<tr>
		<th>Number of credits</th>
		<th>Number of students</th>
	</tr>
EOF;

$creditu_keys = array_keys($credits);
foreach( $creditu_keys as $key ) {
	echo '<tr>';
	$key_disp = $key;
	echo "	<td>$key_disp credits</td>";
	if( $credits[$key] == 1 )
		$s = '';
	else
		$s = 's';
	echo "	<td>$credits[$key] student$s</td>";
	echo '</tr>';
}

echo '</table>';
echo '</div>';

/* extra table for study-specific info - for each study, number of participants and number of credits assigned */
/* -------------------------------------------------------------------------- */
echo '<div class="section">';
$studies = get_studies();

echo <<<EOF
<h2>Study statistics:</h2>
<p><i>Note:</i>	Credits listed may not match total number of credits as miscellaneous credits are not reflected in this total.</p>

<table border="1">
	<tr>
		<th>Study</th>
		<th>Number of participants</th>
		<th>Credits assigned</th>
	</tr>
EOF;

foreach( $studies as $study ) {
	$num_students = query_study($study['st_id']);
	$num_credits = $num_students * $study['st_credits'];

echo <<<EOF
	<tr>
		<td><a href=user.php?studydisplay=$study[st_id]&sirb=$study[st_irb]>IRB #$study[st_irb] $study[st_desc]</a></td>
		<td>$num_students</td>
		<td>$num_credits</td>
	</tr>
EOF;

}

echo '</table>';
echo '</div>';
echo '</div>';

?>













