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


if( !isset($_SESSION) ) {
	session_start();
}

require_once( "includes/html_print.php" );
require_once( "includes/db.php" );

// send user to login if they do not have an active session
if( !isset($_SESSION['active']) ) {
	echo '<meta http-equiv="refresh" content="0; url=index.php">';
	exit;
}

/* load requested page based on GET variable */
if( isset($_GET['user_management']) ) {
	$title = "Administration";
	$include = "utility/user_management.php";
}
else if( isset($_GET['statistics']) ) {
	$title = "Statistics";
	$include = "utility/statistics.php";
}
else if( isset($_GET['studies']) ) {
	$title = "Studies";
	$include = "utility/studies.php";
}
else if( isset($_GET['backup_restore']) ) {
	$title = "Backup and Restore";
	$include = "utility/backup_restore.php";
}
else if( isset($_GET['studentdisplay']) ) {
	$title = "$_GET[studentdisplay]";
	$include = "utility/studentdisplay.php";
}
else if( isset($_GET['studydisplay']) ) {
	$title = "$_GET[sirb]";
	$include = "utility/studydisplay.php";
}
else if( isset($_GET['system_options']) ) {
	$title = "System Options";
	$include = "utility/system_options.php";
}
else if( isset($_GET['sched']) ) {
	$title = "Schedule";
	$include = "utility/schedule.php";
}
else if( isset($_GET['report']) ) {
	$title = "Credit Report";
	$include = "studentindex.php";
}
else {
	// There are two different default locations based on user type
	if( $_SESSION['is_student'] ) {
		$title = "Credit Report";
		$include = "studentindex.php";
	}
	else {
		$title = "Roster";
		$include = "utility/display.php";
	}
}


print_html_head( $title );

print_navbar();

include $include;

print_html_tail();

/* -------------------------------------------------------------------------- */
/* global user-specific functions */
/* -------------------------------------------------------------------------- */
/* check if a POSTed timestamp is older than the session timestamp, so that users don't perform the same action twice */
function check_action_timestamp() {
	if( isset($_POST['action_timestamp']) ) {
		if( $_POST['action_timestamp'] <= $_SESSION['action_timestamp'] )
			return true;
		else {
			$_SESSION['action_timestamp'] = $_POST['action_timestamp'];
			return false;
		}
	}
	return false;
}

?>
