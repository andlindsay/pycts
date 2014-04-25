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

echo <<<EOF
<div id="admin">


<div class="section">
<h2>Add User or Student</h2>



<form action="user.php?admin" method="post">
<input type="hidden" name="action_timestamp" value="$tstamp"/>

<dl>

<dt>First Name</dt>
<dd>
	<input class="text" type="text" name="fname"/>
</dd>

<dt>Last Name</dt>
<dd>
	<input class="text" type="text" name="lname"/>
</dd>

<dt>AD Username</dt>
<dd>
	<input class="text" type="text" name="ad"/>
</dd>

<dt>Role</dt>
<dd>
<select name="role" id="role" onchange="profNameFieldDisplay(this.value)">
	<option value="1" selected="selected">Assistant</option>
	<option value="0">Professor</option>
	<option value="2">Student</option>
</select>
</dd>

<dt>Prof. AD Name</dt>
<dd>
	
       <input class="text" type="text" name="prof" id="Professor" disabled = true/>
</dd>

</dl>

<p>
<input type="submit" name="add_user" value="Add User"/>
</p>

</form>

<script type="text/javascript">
var professorTextBox = document.getElementById("Professor");

function profNameFieldDisplay(selected){
if(selected==2){
professorTextBox.disabled = false; 
}
else{
professorTextBox.disabled = true;
}
}
</script>


</div> <!-- closing section div -->

EOF;

echo <<<EOF

<div class="section">
<h2>Delete User</h2>
<form action="user.php?admin" method="post" onsubmit="return confirm('Are you sure you want to delete?');">
<input type="hidden" name="action_timestamp" value="$tstamp"/>

<p>
<select name="to_remove">
EOF;

foreach( $users as $user ) {
	echo "<option value=\"$user[ad]\">$user[fname] $user[lname] ($user[ad]) - $user[role]</option>";
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




