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

if( !isset($_SESSION['active']) || $_SESSION['is_student']) {
	echo '<meta http-equiv="refresh" content="0; url=index.php">';
	exit;
}

$students = query_study_users($_GET['studydisplay']);
$info = get_study_info($_GET['studydisplay']);

echo '<div class="section">';

echo "<h1>Report for study $info[st_irb]</h1></br>";
echo "<h2>$info[st_desc] (worth $info[st_credits] credits)</h2>";

if(empty($students))
	echo '</br></br>No students have received credit for this study.';
else {
	echo '<p></br>All credits assigned for this study:</p>';
	echo '<table>';
	echo '<tr>
		<th>Last Name</th>
		<th>AD Username</th>
		<th>Assigned By</th>
		<th>Assigned On</th>
		<th>Credit Block</th>
		</tr>';
	foreach($students as $student) {
		$lname = query_student_lname($student['u_ad']);
		$date = date('m/d/y', $student['time_add']);
		echo "<tr>
			<td><a href=\"user.php?studentdisplay=$student[u_ad]\">$lname</a></td>
			<td>$student[u_ad]</td>
			<td>$student[u_add_ad]</td>
			<td>$date</td>
			<td>$student[block_num]</td>
			</tr>";
	}
	echo '</table>';
}

echo '</div>';
?>

