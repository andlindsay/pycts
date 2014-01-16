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

/* check for old files in the download directory to delete */
$files = scandir( "utility/download/" );
foreach( $files as $file ) {
	if( $file == "." || $file == ".." || $file == ".svn" )
		continue;
	$fts = filemtime( "utility/download/$file" );
	$ts = time();
	/* 900 seconds is 15 minutes */
	if( ($ts - $fts) > 900 ) {
		unlink( "utility/download/$file" );
	}
}

$is_repeat_action = check_action_timestamp();

if( !$is_repeat_action ) {
	if( isset($_POST['create']) )
		$message = br_create_backup();
	else if( isset( $_POST['restore'] ) )
		$message = br_backup_restore();
	else if( isset($_POST['database_wipe']) ) {
                $message = admin_database_wipe($_POST['confirm']);
        }else if( isset($_POST['csv']) ){
                $message = upload_csv();
        }
	else
		$message = '';
}
else
	$message = '';

print_action_result( $message );
$counts = get_totals();
$tstamp = time();

echo <<<EOF
<div id="backup_restore">
<div class="section">
<p>
With this utility, you can create a backup of the database and restore from it later. The backup includes all students in the roster and their credit counts, but any restored credits will appear as miscellaneous credits.
</p>

<p>
Right now, there are $counts[credits] credits distributed to $counts[students] students.
</p>

</div> <!-- closing section div -->


<div class="section">
<h2>CSV Upload</h2>
<p>
You can add students to the roster in bulk by uploading a CSV file of student information.
</p>

<p>
You <strong>must</strong> observe the following rules when creating the CSV:
</p>

<ul>
        <li>The field separator shall be a comma.</li>
        <li>Quotes shall be used to delimit text fields (such as names).</li>
        <li>The first row in the CSV is a "header" row, and shall not contain student information.</li>
        <li>The expected order of data fields is: <tt>last name, first name, AD username, professor's AD name</tt>.</li>
</ul>

<p>
So, for example, a row in this CSV might look like this:
</p>
<pre>
"bushey","joe","busheyj","awilke"
</pre>

<p>
If you wish, use the pre-formatted <a href="utility/pycts-template.csv">template CSV file</a>. Open it in the spreadsheet application of your choice and copy in the values you wish to use. When you're finished, be sure to save it as a CSV file. Refer to the PYCTS user manual for a thorough explanation of the CSV formatting rules.
</p>

<form enctype="multipart/form-data" action="user.php?backup_restore" method="post">
<input type="hidden" name="action_timestamp" value="$tstamp"/>
<p>
Choose CSV
<input name="uploaded" type="file"/>
</p>

<p>
<input type="submit" name="csv" value="Upload CSV"/>
</p>
</form>
</div> <!-- closing section div -->
                                               
<div class="section">
<h2>Create Backup</h2>
<p>
Click the button below to download a backup. No data will be changed or deleted.
</p>

<form action="user.php?backup_restore" method="post">
<p>
<input type="hidden" name="action_timestamp" value="$tstamp"/>
<input type="submit" name="create" value="Create Backup"/>
</p>
</form>

</div> <!-- closing section div -->

<div class="section">
<h2>Restore From Backup</h2>

<p>
<strong>Warning:</strong> the restore process causes all students and credits in the database to be <strong>deleted</strong>. After that happens, all students in the CSV file are added, and miscellaneous credits are added to each to restore correct credit counts. If you are certain this is what you want to do, choose the CSV backup file to upload, then type the word "confirm" in the box.
</p>

<form enctype="multipart/form-data" action="user.php?backup_restore" method="post">
<p>
<input type="hidden" name="action_timestamp" value="$tstamp"/>

</p>

<dl>
<dt>CSV Backup File</dt>
<dd>
	<input name="uploaded" type="file"/>
</dd>

<dt>Confirmation</dt>
<dd>
	<input type="text" name="confirm"/>
</dd>
</dl>

<p>
<input type="submit" name="restore" value="Restore From Backup"/>
</p>
</form>


</div> <!-- closing section div -->
<div class="section">
<h2>Database Wipe</h2>
<p>This action will forcibly delete all students and their associated credits. Please consider <a href="user.php?backup_restore">backing up</a> this data before it is removed. If you truly want to do this, type the word "confirm" into the text box below, and hit the button.</p>

<form action="user.php?backup_restore" method="post">
<input type="hidden" name="action_timestamp" value="$tstamp"/>
<p>
<input class="text" type="text" name="confirm"/>
<input type="submit" name="database_wipe" value="Irreversibly delete all students and credits"/>
</p>
</form>
</div> <!-- closing section div -->


EOF;

echo '</div> <!-- closing div "backup_restore" -->';
/* -------------------------------------------------------------------------- */
/* upload functions */
/* -------------------------------------------------------------------------- */
function upload_csv() {
        // check that a file was even uploaded
        if( $_FILES['uploaded']['error'] == 4 ) {
                return 'ERROR: No file selected for upload.';
        }
        // check filetype, we will only accept CSVs
        if (    ($_FILES['uploaded']['type'] == "text/comma-separated-values") ||
                ($_FILES['uploaded']['type'] == "text/x-comma-separated-values") ||
                ($_FILES['uploaded']['type'] == "application/vnd.ms-excel") ||
                ($_FILES['uploaded']['type'] == "text/csv") ) {

                $upname = "utility/upload/" . $_SESSION['ad'] . "_" . time();
                $success = move_uploaded_file($_FILES['uploaded']['tmp_name'], $upname);
                if( !$success )
                       return 'ERROR: An upload error occurred.';
        }
        else
                return "ERROR: This file has type: \"" . $_FILES['uploaded']['type'] . "\", only CSV file uploads are accepted.";

        // now update the database with the contents of the file
        $handle = fopen($upname, "r");
        if( !$handle )
                return 'ERROR: The uploaded file could not be opened.';

        // eat the first line, as it's supposed to be a header
        $data = fgetcsv($handle, 1000, ",");
        $students = array();
        if( count($data) != 4 ) {
                fclose($handle);
                unlink($upname);
                return 'ERROR: CSV is not formatted correctly, there aren\'t 4 data columns';
        }
     while( ($data = fgetcsv($handle, 1000, ",")) !== FALSE ) {
                $student = array('lname' => $data[0], 'fname' => $data[1], 'ad' => $data[2], 'prof' => $data[3]);
                $students[] = $student;
        }
        fclose($handle);
        unlink($upname);
        $results = insert_students($students);
        $success = true;
        foreach( $results as $result ) {
                if( $result['code'] != 'ok' )
                        $success = false;
        }
        if( $success ) {
                return 'All students were imported successfully.';
        }
        $message = 'ERROR: There was a problem, not all students could be added. Here are the results:<br/><br/>';
        foreach( $results as $result ) {
                if( $result['code'] == 'exists' )
                        $message .= "<tt>$result[ad]</tt>: student already exists.<br/>";
                else if( $result['code'] == 'error' )
                        $message .= "<tt>$result[ad]</tt>: database error occurred when adding student.<br/>";
                else if( $result['code'] == 'ok' )
                        $message .= "<tt>$result[ad]</tt>: this student was added successfully.<br/>";
                else if( $result['code'] == 'root' )
                    $message .= "<strong><tt>$result[ad]</tt>: this AD username is reserved for the system root user.</strong><br/>";
        }
        return $message;
}


/* -------------------------------------------------------------------------- */
/* backup/restore functions */
/* -------------------------------------------------------------------------- */
function admin_database_wipe($confirm) {
        if( empty($confirm) || $confirm != 'confirm' )
                return "ERROR: You must type 'confirm' in the text box to perform this action.";
        $success = wipedb();
        if( $success )
                return "All students and credits were permanently deleted.";
        else
                return "ERROR: Database wipe failed.";
}

function br_backup_restore() {
	if( $_POST['confirm'] != "confirm" )
		return 'ERROR: Please type the word "confirm" in the confirmation box.';
	if( $_FILES['uploaded']['error'] == 4 ) {
		return 'ERROR: No file selected for upload.';
	}
	if ( 	($_FILES['uploaded']['type'] == "text/comma-separated-values") ||
		($_FILES['uploaded']['type'] == "text/x-comma-separated-values") ||
		($_FILES['uploaded']['type'] == "application/vnd.ms-excel") ||
		($_FILES['uploaded']['type'] == "text/csv") ) {
		
		$upname = "utility/upload/" . $_SESSION['ad'] . "_" . time() . '.csv';
		$success = move_uploaded_file( $_FILES['uploaded']['tmp_name'], $upname );
		if( !$success ) 
			return 'ERROR: An upload error occurred.';
	}
	else
		return "ERROR: This file has type: \"" . $_FILES['uploaded']['type'] . "\", only CSV file uploads are accepted.";
	$handle = fopen( $upname, "r" );
	if( !$handle )
		return 'ERROR: The uploaded file could not be opened.';
	$data = fgetcsv($handle, 1000, ",");
	if( count($data) != 5 ) {
		fclose($handle);
		unlink($upname);
		return "ERROR: CSV is not formatted correctly, there aren't 5 data columns.";
	}
	/* the next line should be the date the backup was taken */
	$date = fgetcsv( $handle, 1000, "," );
	if( count($date) != 1 ) {
		fclose($handle);
		unlink($upname);
		return "ERROR: CSV is not formatted correctly, the date row is missing or wrong.";
	}
	if( !wipedb() ) {
		fclose($handle);
		unlink($upname);
		return "ERROR: couldn't wipe database.";
	}
	
	$students = array();
	while( ($data = fgetcsv($handle, 1000, ",")) !== FALSE ) {
		$student = array('lname' => $data[0], 'fname' => $data[1], 'ad' => $data[2], 'prof' => $data[3], 'credits' => $data[4]);
		$student['add_success'] = insert_student( $student['lname'], $student['fname'], $student['ad'], $student['prof'] );
		$student['credit_success'] = insert_credits( array($student['ad']), $student['credits'], -1, "This credit was restored from a backup taken on $date[0].", $_SESSION['ad'] );
		$students[] = $student;
	}
	fclose($handle);
	unlink($upname);


	$failed = array();
	foreach( $students as $student ) {
		if( !$student['add_success'] )
			$failed[] = array( 'ad' => $student['ad'], 'code' => 'add' );
		else if( !$student['credit_success'] )
			$failed[] = array( 'ad' => $student['ad'], 'code' => 'credits', 'num' => $student['credits']);
	}

	if( count( $failed ) == 0 )
		return 'All student data was restored successfully.';
	
	$return = "ERROR: There was a problem restoring some students' data, the errors are listed below.<br/><br/>";
	foreach( $failed as $fail ) {
		if( $fail['code'] == 'add' )
			$return .= "The student <tt>$fail[ad]</tt> could not be added to the roster.<br/>";
		else if( $fail['code'] == 'credits' )
			$return .= "The student <tt>$fail[ad]</tt> was added to the roster, but has the wrong number of credits (should be $fail[num] credits).<br/>";
	}
	return $return;
}

?>








