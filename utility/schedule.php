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
	print_system_message("Invalid Session", "You can't use this page until you log in.", "index.php");
	exit;
}

$is_repeat_action = check_action_timestamp();

/* first check that and POST variables are set - if so, then we need to do some action */
if( !$is_repeat_action ) {
	print $POST;
	if( isset($_POST['dept_name']) ) {
		$dept = $_POST['dept_name'];
	}
}else{
	$message = '';
}


print_action_result($message);

if( !isset($dept) )
	$dept = query_dept($_SESSION['ad']);
	
if( isset($_GET['start']) && strtotime($_GET['start']) != false)
	$time = strtotime($_GET['start']);
else
	$time = strtotime('last monday');

get_timeslots($time);
	
$startDate = date('D m/d/y', $time);
$endDate = date('D m/d/y', strtotime('+6 days', $time));

$nextStart = date('m/d/y', strtotime('+7 days', $time));
$prevStart = date('m/d/y', strtotime('-7 days', $time));
$thisWeek = date('m/d/y', strtotime('last monday'));

$startDay = date('d', strtotime($startDate));
$days = array();
for($i=0; $i < 7; ++$i){
	$days[$i] = date('d', strtotime("+$i days", $time));
}

echo <<<EOF

<div class="section">
<h2>Calendar for $dept</h2><br><br>
Currently Viewing: 
	<a href=user.php?sched&start=$prevStart><</a>&nbsp $startDate - $endDate &nbsp<a href=user.php?sched&start=$nextStart>></a>
</br>
<a href=user.php?sched&start=$thisWeek>Return to current week</a>
</br>
<table>
	<tbody>
		<tr>
			<th>Hour</th>
			<th>$days[0] Monday</th>
			<th>$days[1] Tuesday</th>
			<th>$days[2] Wednesday</th>
			<th>$days[3] Thursday</th>
			<th>$days[4] Friday</th>
			<th>$days[5] Saturday</th>
			<th>$days[6] Sunday</th>
		</tr>
EOF;
for($i = 7; $i < 17; ++$i) {
	$hour = $i % 12 + 1;
	if($i < 11)
		$suffix = "AM";
	else
		$suffix = "PM";
		
echo <<<EOF
<tr>
	<td>$hour:00 $suffix</td>
	<td>Available</br>Book</td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
</tr>
EOF;
}
echo <<<EOF
	</tbody>
</table>
EOF;

echo <<<EOF

</table>
EOF;


echo '</div> <!-- closing div "user_management" -->';
?>