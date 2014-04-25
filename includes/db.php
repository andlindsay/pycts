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


/* 
This file provides all database functionality in POINTS by wrapping database interaction with publicly-available functions.

Conventions and other notes
- This should be the ONLY file to work with any sort of database connection. Though no strict OO PHP is used in POINTS, this file acts as a wrapper for the database. All information requests should go through a function in this file.
- All database input is santized, regardless of its source, by mysql_real_escape_string. When this happens, variable names are appended by (_san) to indicate their sanitized status.
- Functions that deal with insertions and deletions generally return only a single boolean that indicates if *all* of the insertions/deletions were successful. The rationale behind this behavior is that it simplifies the code a lot, and it is also unlikely that an operation will only prtially succeed. It is much more likely that either all operations will finish normally or none will.
- Due to oversight during the first version of POINTS, some functions refer to credits as "points". This will eventually be corrected, at which point this notice is to be removed.
*/

/* ########################################## */
/* PRIVATE FUNCTIONS                          */
/* ########################################## */

/*
	Creates an open database connection.
	This function should ONLY BE USED WITHIN THIS FILE. db.php is meant to be a wrapper for all database activity.
	
	args:
		(none)
	returns:
		open mysql database connection
*/
function open_connection() {
	require "includes/globals.php";
	$conn = mysql_connect($mysql_server,$mysql_user,$mysql_pass);
	if( $conn == FALSE ) {
		echo "<h1>Big problems!</h1><p>Can't connect to database server \"$mysql_server\"! Here is the mysql error message:</p><p>" . mysql_error() . '</p>';
		echo '</body></html>';
		exit;
	}
	$success = mysql_select_db($mysql_db, $conn);
	if( !$success ) {
		echo "<h1>Big problems!</h1><p>Can't connect to database \"$mysql_db\" on server \"$mysql_server\"! Here is the mysql error message:</p><p>" . mysql_error() . '</p>';
		echo '</body></html>';
		exit;
	}
	return $conn;
}

/* ########################################## */
/* PUBLIC FUNCTIONS                           */
/* ########################################## */

/*
	Creates an array of user information.
	
	arg 1 (pass a single AD username): returns an associative array containing a single user's data
	arg 1 (empty): returns a multilevel associative array containing data for all users
	
	returns: depends on arg 1, see arg 1 notes
*/
function query_users($username) {
	$conn = open_connection();
	if( empty($username) ) {
		$query = "select * from users where u_role != 2 order by u_lname asc";
	}
	else {
		$username_san = mysql_real_escape_string($username, $conn);
		$query = "select * from users where u_ad = '$username_san' and u_role != 2";
	}
	$result = mysql_query($query, $conn);
	mysql_close($conn);
	if( empty($username) ) {
		$num_rows = mysql_num_rows($result);
		$return = array();
		for($i=0; $i<$num_rows; $i++) {
			$u_role_num = mysql_result($result, $i, "u_role");
			if( $u_role_num == 0 )
				$u_role = "Professor";
			else if( $u_role_num == 1 )
				$u_role = "Assistant";
			$u_lname = mysql_result($result, $i, "u_lname");
			$u_fname = mysql_result($result, $i, "u_fname");
			$u_ad = mysql_result($result, $i, "u_ad");
			$push = array('role' => $u_role, 'lname' => $u_lname, 'fname' => $u_fname, 'ad' => $u_ad);
			$return[] = $push;
		}
	}
	else {
		if( mysql_num_rows($result) != 1 )
			return "";
		$u_role = mysql_result($result, 0, "u_role");
		$u_lname = mysql_result($result, 0, "u_lname");
		$u_fname = mysql_result($result, 0, "u_fname");
		$u_ad = mysql_result($result, 0, "u_ad");
		$return = array('role' => $u_role, 'lname' => $u_lname, 'fname' => $u_fname, 'ad' => $u_ad);	
	}
	return $return;	
}

/*
	Creates an array of student information.
	
	arg 1 (pass a single student AD username): returns an associative array containing a single student's data
	arg 1 (empty): returns a multilevel associative array containing all user data (STUB)
	
	returns: depends on arg 1, see arg 1 notes
*/
function query_students($ad) {
	$conn = open_connection();
	if( empty($ad) ) {
		$query = "select * from users where u_role = 2";
	}
	else {
		$ad_san = mysql_real_escape_string($ad, $conn);
		$query = "select * from users where u_ad = '$ad_san' and u_role = 2";
	}
	$result = mysql_query($query, $conn);
	mysql_close($conn);
	if( empty($ad) ) {
		$return = array();
		$num_students = mysql_num_rows($result);
		for($i=0; $i<$num_students; $i++) {
			$u_lname = mysql_result($result, $i, "u_lname");
			$u_fname = mysql_result($result, $i, "u_fname");
			$u_prof = mysql_result($result, $i, "u_prof");
			$u_ad = mysql_result($result, $i, "u_ad");
			$return[] = array('lname' => $u_lname, 'fname' => $u_fname, 'prof' => $u_prof, 'ad' => $u_ad);
		}
		return $return;
	}
	else {
		if( mysql_num_rows($result) != 1 )
			return "";
		$u_lname = mysql_result($result, 0, "u_lname");
		$u_fname = mysql_result($result, 0, "u_fname");
		$u_prof = mysql_result($result, 0, "u_prof");
		$u_ad = mysql_result($result, 0, "u_ad");
		return array('lname' => $u_lname, 'fname' => $u_fname, 'prof' => $u_prof, 'ad' => $u_ad);
	}
}

function refck_studies_credits($st_id) {
        $conn = open_connection();
        $query = "select * from points;";
        $result = mysql_query($query);
        mysql_close($conn);
        $numrows = mysql_num_rows($result);
        $ref_ok = true;
        for( $i=0; $i < $numrows; $i++ ) {
                if( mysql_result($result, $i, "st_id") == $st_id )
                        $ref_ok = false;
        }
        if( $ref_ok )
                return $st_id;
        return -1;
}

function query_student_fname($ad) {
	$conn = open_connection();
	if( empty($ad) ) {
		$query = "select * from student";
	}
	else {
		$ad_san = mysql_real_escape_string($ad, $conn);
		$query = "select * from users where u_ad = '$ad_san'";
	}
	$result = mysql_query($query, $conn);
	mysql_close($conn);
	$u_fname = mysql_result($result, $i, "u_fname");
	return $u_fname;

}

function query_student_lname($ad) {
	$conn = open_connection();
	if( empty($ad) ) {
		$query = "select * from student";
	}
	else {
		$ad_san = mysql_real_escape_string($ad, $conn);
		$query = "select * from users where u_ad = '$ad_san'";
	}
	$result = mysql_query($query, $conn);
	mysql_close($conn);
	$u_lname = mysql_result($result, $i, "u_lname");
	return $u_lname;
}


/*
	Creates an array of all points given to a single student
	
	arg 1: numeric string representing a single student ID number
	
	returns: associative array which is either empty (student has no points) or contains complete records for each point the student has
*/
function query_credits($studentad, $get_removed) {
	$conn = open_connection();
	$studentad_san = mysql_real_escape_string($studentad, $conn);
	if( $get_removed == false )
		$query = "select p_id, u_ad, u_amount, st_id, u_add_ad, u_rem_ad, from_unixtime(time_add) as time_add, block_num, 
			from_unixtime(time_rem) as time_rem, desc_add, desc_rem from points where u_ad = '$studentad_san' order by 
			time_add desc";
	else
		$query = "select p_id, u_ad, u_amount, st_id, u_add_ad, u_rem_ad, from_unixtime(time_add) as time_add, block_num, 
			from_unixtime(time_rem) as time_rem, desc_add, desc_rem from points where u_ad = '$studentad_san' order by 
			time_rem desc";
	$result = mysql_query($query, $conn);
	mysql_close($conn);
	$num_points = mysql_num_rows($result);
	$return = array();
	if( $num_points == 0 )
		return $return;
	for( $i=0; $i<$num_points; $i++) {
		// first check that the point hasn't been removed
		$u_rem_ad = mysql_result($result, $i, "u_rem_ad");
		if( $get_removed == false && $u_rem_ad != '' )
			continue;
		else if( $get_removed == true && $u_rem_ad == '' )
			continue;
		$p_id = mysql_result($result, $i, "p_id");
		$u_ad = mysql_result($result, $i, "u_ad");
		$st_id = mysql_result($result, $i, "st_id");
		$u_amount = mysql_result($result, $i, "u_amount");
		$u_add_ad = mysql_result($result, $i, "u_add_ad");
		$time_add = mysql_result($result, $i, "time_add");
		$block_num = mysql_result($result, $i, "block_num");
		$time_rem = mysql_result($result, $i, "time_rem");
		$desc_add = mysql_result($result, $i, "desc_add");
		$desc_rem = mysql_result($result, $i, "desc_rem");
		$values = array('p_id' => $p_id, 'u_ad' => $u_ad, 'st_id' => $st_id, 'u_amount' => $u_amount, 'u_add' => $u_add_ad, 
			'u_rem' => $u_rem_ad, 'time_add' => $time_add, 'block_num' => $block_num, 'time_rem' => $time_rem, 
			'desc_add' => $desc_add, 'desc_rem' => $desc_rem);
		$return[] = $values;
	}
	return $return;
}

/*
	Creates an array of point and student totals
	
	no args
	
	returns: associative array containing the total number of students and points
*/
function get_totals() {
	$conn = open_connection();
	$query = "select sum(u_amount) as u_amount from points where time_rem IS NULL;";
	$result = mysql_query($query, $conn);
	$c_count = mysql_result($result, 0, "u_amount");
	if(is_null($c_count))
		$c_count =0;
	$query = "select count(u_ad) as u_ads from users where u_role = 2;";
	$result = mysql_query($query, $conn);
	$u_count = mysql_result($result, 0, "u_ads");
	if(is_null($u_count))
                $u_count =0;
	mysql_close($conn);
	return array('credits' => $c_count, 'students' => $u_count);
}

/*
	Creates an array of all information in the points table, sorted by a certain criteria.
		
	arg 1: string representing the desired sorting criteria: {"Last Name", "First Name", "AD Username", "Student ID", "Professor", "Number of Points"}
	
	returns: associative array of all entries in the points table
*/
function get_all_students_credits($sort_name, $dir) {
	$conn = open_connection();
	
	if( $sort_name == "" || $sort_name == NULL )
		$sort_name = "ad";
		
	$sort_san = mysql_real_escape_string($sort_name, $conn);
	$query = "select $sort_san from points_totals;";
	$result = mysql_query($query, $conn);
	$num_rows = mysql_num_rows($result);
	// if an invalid field was provided, sort by ad
	if( $num_rows == 0 )
		$sort_san = "ad";
		
	// make sure dir is a valid value
	if( $dir != "asc" && $dir != "desc" )
		$dir = "asc";
	
	// fetch all students who have credits
	$query = "select * from points_totals order by $sort_san $dir;";
	$result = mysql_query($query, $conn);
	$students = array();
	$block_count = count(get_blocks());
	
	while($row = mysql_fetch_assoc($result)) {		
		$push = array('lname' => $row['u_lname'], 'fname' => $row['u_fname'], 'ad' => $row['ad'], 
			'prof' => $row['u_prof'], 'credits' => $row['Total']);
		
		for($j = 1; $j < $block_count; ++$j) {
			$column_name = "B".$j."Total";
			$curr_block_total = $row["$column_name"];
			if( $curr_block_total == NULL )
				$curr_block_total = 0;
			$push["credits_b$j"] = $curr_block_total;
		}
		$students[] = $push;
	}
	
	// fetch all students without credits
	mysql_close($conn);
	$conn = open_connection();
	$query = "SELECT u_lname AS lname, u_fname AS fname, u_ad AS ad, u_prof AS prof 
		FROM users WHERE u_ad NOT IN (SELECT ad FROM points_totals) AND u_role = 2;";
	$result = mysql_query($query, $conn);
	while($row = mysql_fetch_assoc($result)) {
		$row["credits"] = 0;
		for($j = 1; $j < $block_count; ++$j) {
			$row["credits_b$j"] = 0;
		}
		$students[] = $row;
	}

	mysql_close($conn);
	return $students;
}

/*
	Inserts a single student into the database.
*/
function insert_student($lname, $fname, $ad, $prof) {
	$conn = open_connection();
	$student_lname_san = mysql_real_escape_string($lname, $conn);
	$student_fname_san = mysql_real_escape_string($fname, $conn);
	$student_ad_san = mysql_real_escape_string($ad, $conn);
	$student_prof_san = mysql_real_escape_string($prof, $conn);

	$query = "select u_ad from users where u_ad = '$student_ad_san';";
	$result = mysql_query( $query, $conn );
	$num_results = mysql_num_rows($result);
	if( $num_results > 0 ){
		return false; 
	}
	
	$query = "insert into users (u_lname, u_role, u_fname, u_ad, u_prof) values ('$student_lname_san', '2', '$student_fname_san', '$student_ad_san', '$student_prof_san');";
	if( $student_ad_san == 'root' )
		$result = false;
	else
		$result = mysql_query( $query, $conn );
	mysql_close($conn);
	return $result;
}

/*
	Inserts more than one student into the database.

	arg 1: array of associative arrays of student information

	returns: associative array of student AD names => {'ok', 'error', 'exists'} indicating the status of each added record
*/
function insert_students($students) {
	$conn = open_connection();
	$return = array();
	foreach( $students as $student) {
		$query = "select u_ad from users where u_ad = '$student[ad]';";
		$result = mysql_query($query, $conn);
		if( mysql_num_rows($result) == 0 ) {
			// there isn't a student with that AD name in the database, add it to the database
			$student_lname_san = mysql_real_escape_string($student['lname'], $conn);
			$student_fname_san = mysql_real_escape_string($student['fname'], $conn);
			$student_ad_san = mysql_real_escape_string($student['ad'], $conn);
			$student_prof_san = mysql_real_escape_string($student['prof'], $conn);
			$query = "insert into users ( u_ad, u_role, u_lname, u_fname, u_prof ) values ( '$student_ad_san', '2', '$student_lname_san', '$student_fname_san', '$student_prof_san' );";
			if( $student_ad_san == 'root' )
				$return[] = array('ad' => $student['ad'], 'code' => 'root');
			else {
				$result = mysql_query($query, $conn);
				if( $result ) {
					$push = array('ad' => $student['ad'], 'code' => 'ok');
					$return[] = $push;
				}
				else {
					$push = array('ad' => $student['ad'], 'code' => 'error');
					$return[] = $push;
				}
			}
		}
		else {
			// that student exists already
			$push = array('ad' => $student['ad'], 'code' => 'exists');
			$return[] = $push;
		}
	}
	mysql_close($conn);
	return $return;
}

/* 
	insert_credits: Gives an arbitrary number of points to an arbtrary number of students
	
	args:
		array of student AD names
		number of points
		study ID
		description
		AD name of the user giving the credits
	returns:
		a bool that tells whether or not *all* credits were successfully added to all selected students
*/
function insert_credits($ads, $num_credits, $st_id, $desc_add, $u_add, $block_num) {
	require "includes/globals.php";
	$conn = open_connection();
	$return = true;
	foreach( $ads as $u_ad ) {
		$u_ad_san = mysql_real_escape_string($u_ad, $conn);
		$st_id_san = mysql_real_escape_string($st_id, $conn);
		$u_add_san = mysql_real_escape_string($u_add, $conn);
		$num_creditu_san = mysql_real_escape_string($num_credits, $conn);
		$desc_add_san = mysql_real_escape_string($desc_add, $conn);
		$timestamp_san = mysql_real_escape_string( time(), $conn);

		$block_num = mysql_real_escape_string($block_num, $conn);
			
		$query = "insert into points (u_ad, u_amount, st_id, u_add_ad, time_add, block_num, desc_add) values ('$u_ad_san', '$num_creditu_san', '$st_id_san', '$u_add_san', '$timestamp_san', '$block_num', '$desc_add_san');";

		$result = mysql_query($query, $conn);
		if( !$result ) {
			$return = false;
			break;
		}
		if( !$return ) {
			break;
		}
	}
	mysql_close($conn);
	return $return;
}

/*
	"Removes" any number of points.
	
	arg 1: array of point id numbers to remove
	arg 2: AD username of the user that is removing the points
	arg 3: description for why the point was removed
*/
function remove_credits($point_ids, $u_ad, $desc) {
	$conn = open_connection();
	foreach( $point_ids as $p_id ) {
		$u_ad_san = mysql_real_escape_string($u_ad, $conn);
		$desc_san = mysql_real_escape_string($desc, $conn);
		$p_id_san = mysql_real_escape_string($p_id, $conn);
		$timestamp_san = mysql_real_escape_string( time(), $conn);
		$query = "update points set u_rem_ad = '$u_ad_san', time_rem = $timestamp_san, desc_rem = '$desc_san' where p_id = $p_id_san;";
		$result = mysql_query($query, $conn);
		if( !$result ) {
			mysql_close($conn);
			return false;
		}
	}
	mysql_close($conn);
	return true;
}

/*
	deletes a single user from the database
*/
function delete_user($user) {
	$conn = open_connection();
	$return = array();
	$user_san = mysql_real_escape_string($user, $conn);
	$query = "delete from users where u_ad = '$user_san'";
	$result = mysql_query($query, $conn);
	mysql_close($conn);
	return $result;
}

/*
	inserts a single user into the database
*/
function insert_user($lname, $fname, $ad, $role) {
	$conn = open_connection();
	$new_ad_san = mysql_real_escape_string($ad, $conn);
	$new_role_san = mysql_real_escape_string($role, $conn);
	$new_lname_san = mysql_real_escape_string($lname, $conn);
	$new_fname_san = mysql_real_escape_string($fname, $conn);

	$query = "insert into users (u_ad, u_role, u_lname, u_fname) values 
		('$new_ad_san', $new_role_san, '$new_lname_san', '$new_fname_san')";
	if( $new_ad_san == 'root' )
		$result = false;
	else 
		$result = mysql_query($query, $conn);
	mysql_close($conn);
	return $result;
}

/*
	removes all credit and student records from the database
*/
function wipedb() {
	$conn = open_connection();
	$squery = "delete from users where u_role = 2;";
	$pquery = "delete from points;";
	$presult = mysql_query($pquery, $conn);
	$sresult = mysql_query($squery, $conn);
	mysql_close($conn);
	if( !$presult || !$sresult )
		return false;
	return true;
}

function get_study_info($st_id) {
	$conn = open_connection();
	$st_id_san  = mysql_real_escape_string($st_id, $conn);
	$query = "select st_irb, st_desc, st_credits from studies where st_id = '$st_id_san'";
	$result = mysql_query($query, $conn);
	$st_irb = mysql_result($result, 0, "st_irb");
	$st_desc = mysql_result($result, 0, "st_desc");
	$st_credits = mysql_result($result, 0, "st_credits");
	return array('st_irb' => $st_irb, 'st_desc' => $st_desc, 'st_credits' => $st_credits);
}

function query_study_users($st_id) {
	$conn = open_connection();
	$st_id_san  = mysql_real_escape_string($st_id, $conn);
	/* be careful not to return credits that have been removed! */
	$query = "select u_ad, u_add_ad, time_add, block_num from points where st_id = '$st_id_san' && time_rem is NULL;";
	$result = mysql_query($query);
	$return = array();
	if( empty( $result ))
		$numrows = 0;
	else {
		$numrows =  mysql_num_rows($result);
		for($i = 0; $i < $numrows; ++$i) {
			$u_ad = mysql_result($result, $i, "u_ad");
			$u_add_ad = mysql_result($result, $i, "u_add_ad");
			$time_add = mysql_result($result, $i, "time_add");
			$block_num = mysql_result($result, $i, "block_num");
			$return[$i] = array('u_ad' => $u_ad, 'u_add_ad' => $u_add_ad, 'time_add' => $time_add, 
				'block_num' => $block_num);
		}
	}
	return $return;
}

/*
	deletes a student entry and all associated point entries for that student
*/
function delete_student($ad) {
	$conn = open_connection();
	$ad_san = mysql_real_escape_string($ad, $conn);
	$squery = "delete from users where u_ad = '$ad_san';";
	$pquery = "delete from points where u_ad = '$ad_san';";
	$presult = mysql_query($pquery, $conn);
	$sresult = mysql_query($squery, $conn);
	mysql_close($conn);
	if( !$presult || !$sresult )
		return false;
	return true;
}

/*
	gets a list of all studies in the database

	args:
		(none)
	returns:
		array of arrays, each subarray contains all information for one study
*/
function get_studies() {
	$conn = open_connection();
	$query = "select * from studies order by st_irb";
	$result = mysql_query($query, $conn);
	mysql_close($conn);
	$studies = array();
	$numrows = mysql_num_rows($result);
	for($i=0; $i<$numrows; $i++) {
		$st_id = mysql_result($result, $i, "st_id");
		$st_irb = mysql_result($result, $i, "st_irb");
		$st_desc = mysql_result($result, $i, "st_desc");
		$st_credits = mysql_result($result, $i, "st_credits");
		$st_flyer = mysql_result($result, $i, "st_flyer");
		$st_visible = mysql_result($result, $i, "st_visible");
		$push = array('st_id' => $st_id, 'st_irb' => $st_irb, 'st_desc' => $st_desc, 'st_credits' => $st_credits, 'st_flyer' => $st_flyer, 'st_visible' => $st_visible);
		$studies[$st_id] = $push;
	}
	return $studies;
}

/*
	gets an array of all credits associated with a particular st_id

	*** this function is incompletely implemented ***
*/
function query_study($st_id) {
	$conn = open_connection();
	$st_id_san  = mysql_real_escape_string($st_id, $conn);
	/* be careful not to return credits that have been removed! */
	$query = "select * from points where st_id = '$st_id_san' && time_rem is NULL;";
	$result = mysql_query($query);
	if( empty( $result ))
		$numrows = 0;
	else
		$numrows = mysql_num_rows($result);
	$return = $numrows; 
	return $return;
}

/*
	adds a single new study to the database

	note: flyer string in database is just the filename. the full path, relative to studies.php, is "flyers/<st_id>/<flyer file name>"
	
	args:
		irb number string
		number of credits the study is worth
		description of the study
		name of the flyer (no path)
	returns:
		if successful, the st_id number of the newly created study, else -1
*/
function add_study($irb, $credits, $desc, $flyer) {
	$conn = open_connection();
	$irb_san = mysql_real_escape_string($irb, $conn);
	$creditu_san = mysql_real_escape_string($credits, $conn);
	$desc_san = mysql_real_escape_string($desc, $conn);
	$flyer_san = mysql_real_escape_string($flyer, $conn);
	$query = "insert into studies (st_irb, st_credits, st_desc, st_flyer, st_visible) values ('$irb_san', $creditu_san, '$desc_san', '$flyer_san', 0);";
	$result = mysql_query($query, $conn);
	if( !$result )
		return -1;
	else {
		$id = mysql_insert_id($conn);
		mysql_close($conn);
		return $id;
	}
}

/*
	deletes an arbitrary number of studies from the database
	
	args:
		array of st_id numbers
	returns:
		boolean indicating whether *all* studies were added successfully
*/
function delete_studies($st_ids) {
	$conn = open_connection();
	$return = true;
	foreach( $st_ids as $st_id ) {
		$st_id_san = mysql_real_escape_string($st_id, $conn);
		$query = "delete from studies where st_id = $st_id_san";
		$result = mysql_query($query, $conn);
		if( !$result )
			$return = false;
	}
	mysql_close($conn);
	return $return;
}

/*
	toggles the "visible" flag on the passed st_id

	args:
		an st_id
	
	returns:
		st_id of the toggled study if successful, -1 otherwise
*/

function study_toggle_visibility($st_id) {
	$conn = open_connection();
	$st_id_san = mysql_real_escape_string($st_id, $conn);
	$query = "select st_visible from studies where st_id = $st_id_san";
	$result = mysql_query($query);
	if( $result == false ) {
		return -1;
	}
	$state = mysql_result($result, 0, 'st_visible');
	if( $state == 1 )
		$query = "update studies set st_visible = 0 where st_id = $st_id_san;";
	else
		$query = "update studies set st_visible = 1 where st_id = $st_id_san;";
	$result = mysql_query($query);
	if( $result )
		return $st_id;
	else
		return -1;
}

/*
	modifies a study in the database to match the args
	if any args are blank (or st_credits is -1), that field is not updated

	args:
		an st_id
		new irb code
		new number of credits
		new description
		new flyer string
	
	returns
		true if update is successful, else false
*/
function study_update($st_id, $st_irb, $st_credits, $st_desc, $st_flyer) {
	$conn = open_connection();
	$st_id_san = mysql_real_escape_string($st_id, $conn);
	$query = "update studies set st_id = $st_id_san, ";
	if( $st_irb != "" ) {
		$st_irb_san = mysql_real_escape_string($st_irb, $conn);
		$query .= "st_irb = '$st_irb_san', ";
	}
	if( $st_credits != -1 ) {
		$st_creditu_san = mysql_real_escape_string($st_credits, $conn);
		$query .= "st_credits = $st_creditu_san, ";
	}
	if( $st_desc != "" ) {
		$st_desc_san = mysql_real_escape_string($st_desc, $conn);
		$query .= "st_desc = '$st_desc_san', ";
	}
	if( $st_flyer != "" ) {
		$st_flyer_san = mysql_real_escape_string($st_flyer, $conn);
		$query .= "st_flyer = '$st_flyer_san', ";
	}
	$query .= "st_id = $st_id_san where st_id = $st_id_san;";
	$result = mysql_query($query);
	mysql_close($conn);
	return $result;
}

function get_blocks() {
	$conn = open_connection();
	$query = "select * from blocks";
	$result = mysql_query($query, $conn);
	mysql_close($conn);
	$blocks = array();
	$numrows = mysql_num_rows($result);
	for($i=0; $i<$numrows; $i++) {
		$block = mysql_result($result, $i, "block");
		$u_time = mysql_result($result, $i, "u_time");
		$push = $u_time;
		$blocks[$block] = $push;
	}
	return $blocks;
}

function update_block_times($times) {
	$conn = open_connection();
	$i = 1;
	$query = "select * from blocks;";
    $result = mysql_query($query, $conn);
	$block_count = mysql_num_rows($result);
	foreach( $times as $time ) {
		$time = mysql_real_escape_string($time, $conn);
		if( $i > $block_count )
			$query = "insert into blocks (block, u_time) values ('$i', '$time');";
		else
			$query = "update blocks set u_time = $time where block = $i;";
		$result = mysql_query($query, $conn);
		++$i;
	}
	if( $i < $block_count ) {
		$query = "delete from blocks where block >= $i;";
		$result = mysql_query($query, $conn);
	}
	return 'Dates changed successfully.';
}

function query_dept($ad) {
	$conn = open_connection();
	//assumes only 1 entry for given AD user
	$query = "select u_dept from users where u_ad = '$ad';";
	$result = mysql_query($query);
	mysql_close($conn);
	return mysql_result($result, 0, 'u_dept');
}

function get_prof_ads() {
        $conn = open_connection();
        $query = "select u_ad, u_fname, u_lname from users where u_role = 0";
        $result = mysql_query($query, $conn);
        mysql_close($conn);
        $profs = array();
        $numrows = mysql_num_rows($result);
        for($i=0; $i<$numrows; $i++) {
                $lname = mysql_result($result, $i, "u_lname");
                $fname = mysql_result($result, $i, "u_fname");
		$ad = mysql_result($result, $i, "u_ad");
                $profs[$i] = array('lname' => $lname, 'fname' => $fname, 'ad' => $ad);
        }
        mysql_close($conn);
        return $profs;
}
/*creates backup of the roster*/
function br_create_backup() {
        date_default_timezone_set( 'America/New_York' );
        $downname = "backup_" . date("Y-m-d_H-i-s") . ".csv";
        $csvhandle = fopen("utility/download/$downname", 'w' );
        if( !$csvhandle )
                return "ERROR: Could not create file for download.";
        $line = '"Last Name","First Name","AD Username","Professor\'s AD Username","Credits"' . "\n";
        if( fwrite( $csvhandle, $line ) === false )
                return "ERROR: Write failed, backup is incomplete.";
        $line = '"' . date( 'Y-m-d' ) . '"' . "\n";
        if( fwrite( $csvhandle, $line ) === false )
                return "ERROR: Write failed, backup is incomplete.";
        $students = get_all_students_credits( "Last Name", "asc" );
        foreach( $students as $student ) {
                $credits = $student[u_credits];
                $line = "\"$student[u_lname]\",\"$student[u_fname]\",\"$student[u_ad]\",\"$student[u_prof]\",\"$credits\",\"$student[u_creditu_b1]\",\"$student[u_creditu_b2]\",\"$student[u_creditu_b3]\"";
                $line .= "\r\n";
                if( fwrite( $csvhandle, $line ) === false )
                        return "ERROR: Write failed, backup is incomplete.";
        }
        fclose($csvhandle);
        return "Backup is complete. You can download the file <a href=\"utility/download/$downname\">here</a>.";
}

function get_email_text($type) {
	$conn = open_connection();
	$query = "select text from emails where title = '$type'";
	$result = mysql_query($query);
	mysql_close($conn);
	return mysql_result($result, 0, 'text');
}

function get_all_emails() {
	$conn = open_connection();
	$query = "select * from emails order by title";
	$result = mysql_query($query, $conn);
	$emails = array();
	$numrows = mysql_num_rows($result);
	for($i=0; $i<$numrows; $i++) {
		$title = mysql_result($result, $i, "title");
		$text = mysql_result($result, $i, "text");
	$ad = mysql_result($result, $i, "u_ad");
		$emails[$i] = array('title' => $title, 'text' => $text);
	}
	mysql_close($conn);
	return $emails;
}

function update_email($title, $new_text) {
	$conn = open_connection();
	$query = "update emails set text = '$new_text' where title = '$title'";
	$result = mysql_query($query, $conn);
	if( $result )
		return "Succesfully updated email text for '$title'";
	else
		return "There was an error trying to update email text for '$title'";
}

function send_email($s_ad, $message_type){
	$recipient = $s_ad . "@clarkson.edu";

	$fname = query_student_fname($s_ad);
	$lname = query_student_lname($s_ad);
	
	$subject = "PY151 Research Credits Added for " . $fname . " " . $lname . " (".$s_ad.")";
	$headers = "From: donotreply@clarkson.edu" . "\r\n" . "X-Mailer: PHP/" . phpversion() . "\r\n" . "Content-type: text/html\r\n";
	
	/* use PHP object buffering to make it easier to output the HTML message */
	ob_start();
	
	$email_string = get_email_text($message_type);
	$footer_string = get_email_text("footer");
	$time = getdate();
	$AMPM = 'AM';
	if($time['hours'] > 12) {
		$time['hours'] -= 12;
		$AMPM = 'PM';
	}
	if($time['hours'] < 10)
		$timestring = '0'.$time['hours'];
	else
		$timestring = $time['hours'];
	
	if($time['minutes'] < 10)
		$timestring .= ':0'.$time['minutes'];
	else
		$timestring .= ':'.$time['minutes'];
		
	if($time['seconds'] < 10)
		$timestring .= ':0'.$time['seconds'];
	else
		$timestring .= ':'.$time['seconds'];
		
echo <<<EOF
<html>
	<head></head>
	<body>
		<div id="studentcontent">
			<p>$email_string</p>
			<p>This notification was generated $time[weekday], $time[mday] $time[month] at $timestring $AMPM.</p>
			<p>$footer_string</p>
		</div>
	</body>
</html>
EOF;

	$message = ob_get_contents();
	ob_end_clean();
	$success = mail($recipient, $subject, $message, $headers);
	if( $success ) {
		return "The report has been sent.";
	}
	else {
		return "ERROR: Something went wrong contacting the mail server, your report could not be sent.";
	}
}

function get_current_block()
{
	$timestamp = time();
	$conn = open_connection();
	$query = "SELECT block FROM blocks WHERE u_time < $timestamp ORDER BY block DESC LIMIT 1";
	$result = mysql_query($query);
	mysql_close($conn);
	return mysql_result($result, 0, 'block');
}

function create_blocks($block_amt) 
{
	$conn = open_connection();
	$query = "TRUNCATE blocks";
	mysql_query($query);
	
	$timestamp = time();
	for( $i = 1; $i <= $block_amt+1; ++$i ) {
		$query = "INSERT INTO blocks VALUES ('$i', '$timestamp')";
		//add four weeks
		$timestamp += (4 * 7 * 24 * 60 * 60);
		$result = mysql_query($query);
		if( !$result ) {
			mysql_close($conn);
			return "ERROR: Unable to insert blocks";
		}
	}
	mysql_close($conn);
	return "Succesfully updated blocks";
}

function credits_exist() {
	$conn = open_connection();
	$query = "SELECT COUNT(*) FROM points";
	$result = mysql_query($query);
	if(mysql_result($result, 0) == "0")
	{
		mysql_close($conn);
		return false;
	}
	mysql_close($conn);
	return true;
}

function create_points_view() {	
	//build query
	$query = "CREATE OR REPLACE VIEW `points_totals` AS select `p1`.`u_ad` AS `ad`, 
		`u`.`u_lname` AS `u_lname`, `u`.`u_fname` AS `u_fname`,`u`.`u_prof` AS `u_prof`, 
		sum(`p1`.`u_amount`) AS `Total`";
	$block_count = count(get_blocks());
	for($i = 1; $i < $block_count; ++$i) {
		$iplusone = $i + 1;
		$str = "B".$i."Total";
		$query .= ", (select sum(`p$iplusone`.`u_amount`) AS `SUM(u_amount)` from `points` `p$iplusone` where ((`p$iplusone`.`block_num` = $i) and (`p$iplusone`.`u_ad` = `p1`.`u_ad`) and (`p$iplusone`.`time_rem` IS NULL))) AS $str";
	}
	$query .= " from (`points` `p1` join `users` `u` on((`p1`.`u_ad` = `u`.`u_ad`))) where `p1`.`time_rem` IS NULL group by `p1`.`u_ad`";
	//echo $query;
	$conn = open_connection();
	$result = mysql_query($query);
	if(!$result) {
		echo $query;
		echo mysql_error();
		mysql_close($conn);
		return "Failed to create view";
	}
	mysql_close($conn);
	return true;
}
?>