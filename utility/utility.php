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


/* check for unauthenticated access */
if( !isset($_SESSION['active']) ) {
	echo '<meta http-equiv="refresh" content="0; url=index.php">';
	exit;
}

echo '<div id="utility">';

$sel_roster = $sel_user_management = $sel_stats = $sel_studies = $sel_backup_restore = $sel_sys_opts = $sel_report  = $sel_sched = "";
$sel = 'class="selected"';
if( isset($_GET['user_management']) )
	$sel_user_management = $sel;
else if( isset($_GET['statistics']) )
	$sel_stats = $sel;
else if( isset($_GET['studies']) )
	$sel_studies = $sel;
else if( isset($_GET['backup_restore']) )
	$sel_backup_restore = $sel;
else if( isset($_GET['system_options']) )
	$sel_sys_opts = $sel;
else if( isset($_GET['report']) )
	$sel_report = $sel;
else if( isset($_GET['sched']) )
	$sel_sched = $sel;
else
	$sel_roster = $sel;

/* print navigation */
echo '<ul>';
if( $_SESSION['is_student'] == true)
{
	echo '<li><a ' . $sel_report . ' href="user.php?report">Report</a></li>';
	echo '<li><a ' . $sel_studies . ' href="user.php?studies">Studies</a></li>';
	//echo '<li><a ' . $sel_sched . ' href="user.php?sched">Schedule</a></li>';
}
else
{
	echo '<li><a ' . $sel_roster . ' href="user.php">Roster</a></li>';
	echo '<li><a ' . $sel_stats . ' href="user.php?statistics">Statistics</a></li>';
	//echo '<li><a ' . $sel_sched . ' href="user.php?sched">Schedule</a></li>';
	//prof only pages
	if( $_SESSION['role'] == 0 )
	{
		echo '<li><a ' . $sel_studies . ' href="user.php?studies">Studies</a></li>';
		echo '<li><a ' . $sel_user_management . ' href="user.php?user_management">Users</a></li>';
		echo '<li><a ' . $sel_backup_restore . ' href="user.php?backup_restore">Database</a></li>';
		echo '<li><a ' . $sel_sys_opts . ' href="user.php?system_options">Options</a></li>';
	}
}
echo <<<EOF
</ul>
EOF;

echo '</div> <!-- closing div "utility" -->';

?>

