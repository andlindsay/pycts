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


session_start();

require_once("includes/db.php");
require_once("includes/html_print.php");
require_once("includes/globals.php");
include("jobsched/firepjs.php");

/* someone is trying to log in */
if( isset($_POST['login']) ) {
	$ad_user = $_POST['user'];
	$ad_pass = $_POST['pass'];

	/* bugfix: ldap bind won't fail if password is empty */
	if( empty($ad_pass) || empty($ad_user) ) {
		print_system_message("Login Failed", "Username or password is invalid.", "index.php");
		exit;
	}

	/* special case: check if user is trying to auth as non-AD root user */
	if( $ad_user == 'root' ) {
		if( $ad_pass == $root_pw ) {
			set_session_vars( "root", array() );
			include "user.php";
		}
		else
			print_system_message("Login failed.", "Username or password is invalid.", "index.php");
			exit;
	}

	/* check auth tokens against AD, but only if you enable AD in globals.php */
	if( $use_ad ) {
		$ad_conn = ldap_connect($ad_server);
		if( $ad_conn == false ) {
			print_system_message("Login Failed", "Couldn't connect to the Active Directory server.", "index.php");
			exit;
		}
		$ad_bind = @ldap_bind($ad_conn,"$ad_user@$ad_server",$ad_pass);
		if( !$ad_bind ) {
			print_system_message("Login Failed", "Username or password is incorrect.", "index.php");
			exit;
		}
		ldap_unbind($ad_conn);
	}

	/* see if they're a student or points user */
	$userdetails = query_users( $ad_user );
	$studentdetails = query_students( $ad_user );
	if( empty($userdetails) && empty($studentdetails) ) {
		print_system_message("Login Failed", "Sorry, you don't seem to be in the system. Please ask your PY151 professor to add you.", "index.php");
		exit;
	}
	if( empty($userdetails) ) {
		set_session_vars( "student" , $studentdetails);
		include "studentindex.php";
	}
	else {
		set_session_vars( "professor", $userdetails );
		include "user.php";
	}
}
else if( isset($_POST['logout']) || isset($_GET['logout']) ) {
	session_destroy();
	session_start();
	print_system_message("Logout", "You have been logged out, thank you for using PYCTS.", "index.php");
}
else if( isset($_SESSION['active']) ) {
	/* there is already an active session and the user is returning */
	if( isset($_SESSION['is_student']) ) {
		include "studentindex.php";
	}
	else
		include "user.php";
}
else {
	print_html_head("Login");

	print_navbar();

echo <<<EOF
<div id="front_page">
<h1>PY151 Credit Tracking System</h1>

<p>
You can use this website to track your PY151 research credits and see studies that are being offered to students. Please login using your Clarkson username and password.
</p>

<form method="post" action="index.php">

<fieldset>
<p class="input">
<label>Username:</label>
<input type="text" name="user"/>
</p>

<p class="input">
<label>Password:</label>
<input type="password" name="pass"/>
</p>

<p>
<input name="login" type="submit" value="Login"/>
</p>
</fieldset>

</form>
EOF;

	print_student_timestamp();

echo '</div>';

	print_html_tail();
}

/* -------------------------------------------------------------------------- */
/* functions */
/* -------------------------------------------------------------------------- */
function set_session_vars( $usertype, $details ) {
	if( $usertype == "student" ) {
		$_SESSION['active'] = true;
		$_SESSION['is_student'] = true;
		$_SESSION['ad'] = $details['ad'];
		$_SESSION['fname'] = $details['fname'];
		$_SESSION['lname'] = $details['lname'];
		$_SESSION['prof'] = $details['prof'];
	}
	else if( $usertype == "professor" ) {
		$_SESSION['active'] = true;
		$_SESSION['role'] = $details['role'];
		$_SESSION['fname'] = $details['fname'];
		$_SESSION['lname'] = $details['lname'];
		$_SESSION['ad'] = $details['ad'];
		$_SESSION['filter'] = '';
		$greetings = array( 'Greetings', 'Ahoy', 'Guten Tag', 'Salutations', 'Hello', 'Good day', 'G\'day', 'Hey', 'Hi', 'Welcome', 'Aloha', 'Hello there', 'Top \'o the morning', 'Howdy', 'Hi there', 'Ciao', 'Bonjour', 'Buenos dias', 'Shalom', 'Hullo', 'Hallo');
		$_SESSION['salutation'] = $greetings[array_rand( $greetings )];
		$_SESSION['action_timestamp'] = time();
	}
	else if( $usertype == "root" ) {
		$_SESSION['active'] = true;
		$_SESSION['role'] = 0;
		$_SESSION['fname'] = 'root';
		$_SESSION['lname'] = 'root';
		$_SESSION['ad'] = 'system_root';
		$_SESSION['filter'] = '';
		$_SESSION['salutation'] = 'Make changes at your own peril';
		$_SESSION['action_timestamp'] = time();
	}
}

?>
