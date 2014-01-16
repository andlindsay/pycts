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


/* This file holds function used to output html for various purposes */

function print_html_head($title) {

echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<link rel="shortcut icon" href="favicon.ico"/>
EOF;

	if( isset($_SESSION) ) {
		if( isset($_SESSION['is_student']) ) {
			echo '<link rel="stylesheet" type="text/css" href="style.css"/>';
		}
		else if( isset($_SESSION['role']) ) {
			echo '<link rel="stylesheet" type="text/css" href="userstyle.css"/>';
			echo '<script type="text/javascript" src="javascript.js"></script>';
		}
		else {
			echo '<link rel="stylesheet" type="text/css" href="style.css"/>';
		}
	}
	else {
		echo '<link rel="stylesheet" type="text/css" href="style.css"/>';
	}

echo <<<EOF
	<title>PYCTS: $title</title>

	<!-- This is PYCTS: the PY151 Credit Tracking System -->
	<!-- PYCTS is free software, released under the terms of the GPLv3 -->

</head>
<body>

EOF;

}

function print_html_tail() {

echo <<<EOF

</body>
</html>
EOF;

}

function print_navbar() {

echo <<<EOF
<div id="student_navbar">

<ul>
	<li><a class="img" href="index.php"><img src="logo.png" alt="Logo"/></a></li>
</ul>

</div>
EOF;

}

function print_user_navbar() {

echo<<<EOF
<div id="user_navbar">

<ul>
	<li><a class="img" href="user.php"><img src="logo.png" alt="Logo"/></a></li>
	<li><span id="greeting">$_SESSION[salutation], $_SESSION[fname].</span></li>
	<li id="logout"><a href="index.php?logout">Log Out</a></li>
</ul>

</div>
EOF;

}

function print_system_message( $title, $msg, $target ) {
	print_html_head($title);
	print_navbar();
	echo "<p class=\"system_message\">$msg</p>";
	echo "<p class=\"system_message\"><a class=\"system_message\" href=\"$target\">Return</a></p>";
	print_html_tail();
}

function print_user_system_message( $msg, $target ) {
	echo "<p class=\"system_message\">$msg</p>";
	echo "<p class=\"system_message\"><a class=\"system_message\" href=\"$target\">Return</a></p>";
	print_html_tail();
	exit();
}

function print_student_timestamp() {
	date_default_timezone_set('America/New_York');
	$date = date("F jS, Y");
	$time = date("g:i:s A");
	echo "<p id=\"timestamp\">This page generated on $date at $time.<br/>";
	echo "PYCTS version: " . $GLOBALS['software_version'] . "</p>";
}

function print_action_result($message) {
	/* If the message begins with "ERROR:", then the last action has failed */
	if( !empty($message) ) {
		if( strncmp($message, "ERROR:", 6) == 0 ) {
			$message = str_replace('ERROR:', '', $message);
			$div_id = 'action_result_fail';
		}
		else
			$div_id = 'action_result';
		echo '<div id="'. $div_id . '">';
		echo "<p>$message</p>";
		echo '</div>';
	}
}

function debug_print( $array ) {
	echo '<pre>';
	print_r( $array );
	echo '</pre>';
}

?>
