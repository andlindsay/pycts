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

if( !isset($_SESSION['active']) ) {
	print_system_message("Invalid Session", "You can't use this page until you log in.", "index.php");
	exit;
}

$is_repeat_action = check_action_timestamp();

/* first check that and POST variables are set - if so, then we need to do some action */
if( !$is_repeat_action ) {
	print $POST;
	if( isset($_POST['add_user']) ) {
		$message = admin_add_user($_POST['lname'], $_POST['fname'], $_POST['ad'],$_POST['prof'], $_POST['role']);
	}
	else if( isset($_POST['delete_user']) ) {
		$message = admin_delete_user($_POST['to_remove']);
	}
	else if( isset($_POST['delete_student']) ) {
		$message = admin_delete_student($_POST['ad']);
	}else
		$message = '';
}else{
	$message = '';
}


print_action_result($message);

$users = query_users('');
$tstamp = time();
$dept = query_dept($_SESSION['ad']);
$all_depts = get_depts();
for( $i = 0; $i < count($all_depts); $i++ ) {
	echo $all_depts[$i]."\n";
}
echo <<<EOF
<div id="admin">

<div class="section">
<h2>Calendar for $dept</h2>
<p>
<select name="view_dept">
EOF;

for( $i = 0; $i < count($all_depts); $i++ ) {
	echo "<option value=\"$all_depts[$i]\"></option>";
}

echo <<<EOF
</select>

<input type="submit" name="delete_user" value="Delete User"/>
</p>
</form>
</div> <!-- closing section div -->

<div class="section">
<h2>Delete Student</h2>
<p>To delete a student from the roster, enter their AD username below. Deleting a student causes all of their credits to be erased.</p>

<form action="user.php?admin" method="post" onsubmit="return confirm('Are you sure you want to delete?');">
<input type="hidden" name="action_timestamp" value="$tstamp"/>
<p>
<input class="text" type="text" name="ad"/>
<input type="submit" name="delete_student" value="Delete Student"/>
</p>
</form>
</div> <!-- closing section div -->

<div class="section">
EOF;
echo <<<EOF
<h2>Current Users</h2>
<table>
<tr>
        <th>Last Name</th>
        <th>First Name</th>
        <th>AD Username</th>
        <th>Role</th>
</tr>
EOF;

$c = 1;
foreach( $users as $user ) {
        if( $c == 0 )
                $c = 1;
        else
                $c = 0;
        echo "<tr class=\"color$c\">";
echo <<<EOF
<td>$user[lname]</td>
<td>$user[fname]</td>
<td>$user[ad]</td>
<td>$user[role]</td>
EOF;
        echo '</tr>';
}

echo <<<EOF

</table>
EOF;


echo '</div> <!-- closing div "admin" -->';

/* -------------------------------------------------------------------------- */
/* admin functions */
/* -------------------------------------------------------------------------- */

function admin_add_user($lname, $fname, $ad, $prof, $role) {
	if( $ad == 'root' )
                return 'ERROR: That AD username is reserved for the system root user.';
        if($role == 2){
		if( empty($lname) || empty($fname) || empty($ad) || empty($prof) )
                	return 'ERROR: One or more fields have been left blank.';
        	$success = insert_student($lname, $fname, $ad, $prof);
	}else{
		if( empty($lname) || empty($fname) || empty($ad) )
			return 'ERROR: One or more fields have been left blank.';
		$success = insert_user($lname, $fname, $ad, $role);
	}
	if( $success )
		return "User was added successfully.";
	else
		return "ERROR: User could not be added.";
	
}

function upload_add_single($lname, $fname, $ad, $prof) {
        if( empty($lname) || empty($fname) || empty($ad) || empty($prof) )
                return 'ERROR: One or more fields have been left blank.';
        if( $ad == 'root' )
                return 'ERROR: That AD username is reserved for the system root user.';
        $success = insert_student($lname, $fname, $ad, $prof);
        if( $success )
                return 'Student was added successfully.';
        return 'ERROR: Student could not be added.';
}


/* refck problem - you shouldn't be able to delete a user if they have given or removed any credits */
/* but, it doesn't break anything if this is not verified */
function admin_delete_user($ad) {
	if( empty($ad) )
		return "ERROR: No user was given for deletion.";
	if( $ad == $_SESSION['ad'] )
		return "ERROR: You can't delete yourself!";
	$users = query_users('');
	$admins = array();
	$delete_admin = false;
	foreach( $users as $user )
		if( $user['role'] == 0 ) {
			$admins[] = $user;
			if( $user['ad'] == $_SESSION['ad'] );
				$delete_admin == true;
		}
	if( $delete_admin && count($admins) <= 1 )
		return "ERROR: It would be a bad idea to delete the last Professor-level user.";
	$success = delete_user($ad);
	if( $success )
		return "User was removed successfully.";
	else
		return "ERROR: User could not be removed.";
}

function admin_delete_student($ad) {
	$students = query_students('');
	$student_exists = false;
	foreach( $students as $student )
		if( $student['ad'] == $ad )
			$student_exists = true;
	if( !$student_exists )
		return "ERROR: The student '$ad' doesn't exist.";
	$success = delete_student($ad);
	if( $success )
		return "Student and credits were removed successfully.";
	else
		return "ERROR: Student could not be removed.";
}




