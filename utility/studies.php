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

if( !isset($_SESSION['active'])) {
	print_system_message("Invalid Session", "You can't use this page until you log in.", "index.php");
	exit;
}

$is_repeat_action = check_action_timestamp();

if( !$is_repeat_action ) {
	if( isset($_POST['add_study']) )
		$message = studies_add_study($_POST['irb'], $_POST['credits'], $_POST['desc']);
	else if( isset($_POST['delete_study']) )
		$message = studies_delete_study($_POST['st_id']);
	else if( isset($_POST['toggle_visibility']) )
		$message = studies_toggle_visibility($_POST['st_id']);
	else if( isset($_POST['edit_info']) )
		$message = studies_edit_info($_POST['st_id'], $_POST['irb'], $_POST['credits'], $_POST['desc']);
	else
		$message = '';
}
else
	$message = '';

print_action_result($message);

echo '<div id="studies">';

echo '<div class="section">';
$studies = get_studies();

if( empty($studies) ) {
	echo 'There are no studies to display.';
	echo '</div>';
}
else {

if($_SESSION['is_student'])
{
	echo '<h2>Studies Currently Available to Students</h2>';
}

echo <<<EOF
<script type="text/javascript" charset="utf-8">
    $(document).ready(function() {
        $('#studiesTable').dataTable.( {
			"bPaginate": false,
			"bLengthChange": false,
			"bFilter": false,
			"bSort": false,
			"bInfo": false,
			"bAutoWidth": false
		} ).makeEditable({
			sUpdateURL: "UpdateData.php"
		});
    } );
</script>
<table id="studiesTable" border="1">
	<thead>
		<tr>
			<th>IRB Number</th>
			<th>Description</th>
			<th>Credits</th>
			<th>Flyer</th>
EOF;

	if(!$_SESSION['is_student'])
		echo '<th>Visible?</th>';
	echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
	foreach( $studies as $study) {
		if($_SESSION['is_student'] && !$study['st_visible'])
			continue;
        $id = $study['st_id'];
		echo '<tr>';
		echo "<td class=\"editableSingle IRBnumber removable id$id\">IRB #$study[st_irb]</td>";
		echo "<td class=\"editableSingle Description removable id$id\">$study[st_desc]</td>";
		echo "<td class=\"editableSingle Credits removable id$id\">$study[st_credits]</td>";
		echo '<td><a href="utility/flyers/' . $study['st_id'] . '/' . $study['st_flyer'] . '">' . $study['st_flyer'] . '</a></td>';
		if(!$_SESSION['is_student']) {
			if( $study['st_visible'] == 1 )
				echo "<td class=\"editableSingle Visible removable id$id\">Yes</td>";
			else
				echo "<td class=\"editableSingle Visible removable id$id\">No</td>";
		}
		echo '</tr>';
	}
    echo '</tbody>';
	echo '</table>';
	echo '</div><!-- closing section div -->';
}	

$tstamp = time();

if(!$_SESSION['is_student']) {
echo <<<EOF
<div class="section">
	<h2>Add Study</h2>
	<p>Fill out this form to create a new study. You have to upload the flyer in PDF format.</p>
	
	<form enctype="multipart/form-data" method="post" action="user.php?studies">
		<input type="hidden" name="action_timestamp" value="$tstamp"/>		
		<dl>
			<dt>IRB Number</dt>
			<dd><input class="text" type="text" name="irb"/> <span class="input_explanation">(e.g. 11-19)</span></dd>			
			<dt>Credits Worth</dt>
			<dd>
				<select name="credits">
					<option value="0.5">0.5 Credits</option>
					<option value="1">1.0 Credit</option>
					<option value="1.5">1.5 credits</option>
					<option value="2">2.0 Credits</option>
					<option value="2.5">2.5 Credits</option>
					<option value="3">3.0 Credits</option>
					<option value="3.5">3.5 Credits</option>
					<option value="4">4.0 Credits</option>
					<option value="4.5">4.5 Credits</option>
					<option value="5">5.0 Credits</option>
				</select>
			</dd>
			
			<dt>Upload Flyer</dt>
			<dd><input name="flyer" type="file"/></dd>
			
			<dt>Description</dt>
			<dd><input class="text" type="text" name="desc"/> <span class="input_explanation">(e.g. "Categorization and Multiple-Cue Judgements")</span></dd>
		</dl>
		
		<p><input type="submit" name="add_study" value="Add Study"/></p>
	</form>
</div>

EOF;

if( count($studies) == 0 ) {
	echo '</div>';
	return;
}

echo <<<EOF
<div class="section">
	<h2>Delete Studies</h2>
	<form method="post" action="user.php?studies" onsubmit="return confirm('Are you sure you want to delete?');">
		<input type="hidden" name="action_timestamp" value="$tstamp"/>
		<p>
			<select name="st_id">
EOF;

foreach( $studies as $study ) {
	echo "<option value=\"$study[st_id]\">IRB #$study[st_irb]: $study[st_desc]</option>";
}

echo <<<EOF
			</select>
			<input type="submit" value="Delete Study" name="delete_study"/>
		</p>
	</form>
</div>

<div class="section">
	<h2>Toggle Visibility</h2>
	<p>
		Invisible studies are tracked by PYCTS but are not displayed to students. To make an invisible study 
		visible (and vice versa), select it from the list below.
	</p>
	
	<form method="post" action="user.php?studies">
		<input type="hidden" name="action_timestamp" value="$tstamp"/>
		<p>
			<select name="st_id">
EOF;

foreach( $studies as $study ) {
	echo '<option value="' . $study['st_id'] . '">IRB #' . $study['st_irb'] . ': ' . $study['st_desc'] . ' ';
	if( $study['st_visible'] == 1 )
		echo "(Visible)</option>";
	else
		echo "(Not Visible)</option>";
}

echo <<<EOF
			</select>
			<input type="submit" name="toggle_visibility" value="Toggle Visibility"/>
		</p>
	</form>
</div>

<div class="section">
	<h2>Edit Study Info</h2>
	<p>You can change the information associated with a study from this form. Leave blank any fields you do not wish to change.</p>
	
	<form enctype="multipart/form-data" method="post" action="user.php?studies">
		<input type="hidden" name="action_timestamp" value="$tstamp"/>
		<p>
			<select name="st_id">
EOF;

foreach( $studies as $study ) {
	echo "<option value=\"$study[st_id]\">IRB #$study[st_irb]: $study[st_desc]</option>";
}

echo <<<EOF
			</select>
		</p>
	<dl>
		<dt>New IRB Number</dt>
		<dd><input class="text" type="text" name="irb"/> <span class="input_explanation">(e.g. 11-19)</span></dd>
		
		<dt>Credits Worth</dt>
		<dd>
			<select name="credits">
				<option value="0.5">0.5 Credits</option>
				<option value="1">1.0 Credit</option>
				<option value="1.5">1.5 credits</option>
				<option value="2">2.0 Credits</option>
				<option value="2.5">2.5 Credits</option>
				<option value="3">3.0 Credits</option>
				<option value="3.5">3.5 Credits</option>
				<option value="4">4.0 Credits</option>
				<option value="4.5">4.5 Credits</option>
				<option value="5">5.0 Credits</option>
			</select>
		</dd>
		
		<dt>Replace Flyer</dt>
		<dd><input name="flyer" type="file"/></dd>
		
		<dt>New Description</dt>
		<dd><input class="text" type="text" name="desc"/> <span class="input_explanation">(e.g. "Categorization and Multiple-Cue Judgements")</span></dd>
	</dl>

	<p><input type="submit" value="Change Info" name="edit_info"/></p>
EOF;
}
echo <<<EOF

	</form>
</div>
EOF;

echo '</div>';

/* -------------------------------------------------------------------------- */
/* study function */
/* -------------------------------------------------------------------------- */

function studies_add_study($irb, $credits, $desc) {
	// no input checking is done on the IRB number in case its format changes in the future
	// check that a flyer was uploaded 
	if( $_FILES['flyer']['error'] == 4 ) {
		return 'ERROR: No file selected for upload.';
	}
	// check that there are no errors 
	elseif( $_FILES['flyer']['error'] != 0 ) {
		return 'ERROR: An upload error occurred.' . $_FILES['flyer']['error'] . '"';
	}
	// check the file type
	if( $_FILES['flyer']['type'] != "application/pdf" ) {
		return 'ERROR: Only PDF files are accepted. This file has the MIME type "' . $_FILES['flyer']['type'] . '".';
	}	
	// check that there is a description and irb number
	if( empty($_POST['desc']) || empty($_POST['irb'])) {
		return 'ERROR: You must enter both a description and IRB number.';
	}

	// move the file to the tmp directory
	$upname = "utility/flyers/tmp/" . time() .'_' . $_FILES['flyer']['name'];
	$result = move_uploaded_file($_FILES['flyer']['tmp_name'], $upname);
	if( !$result ) {
		return 'ERROR: Flyer could not be moved.';
	}
	
	$st_id = add_study($irb, $credits, $desc, $_FILES['flyer']['name']);
	if( $st_id == -1 ) {
		unlink($upname);
		return 'ERROR: The study could not be added due to a database error.';
	}
	else {
		
		mkdir("utility/flyers/$st_id", 0777);
		copy($upname, "utility/flyers/$st_id/" . $_FILES['flyer']['name']);
		unlink($upname);
		return 'The study was added successfully.';
	}
}

function studies_delete_study($st_id) {
	// check that the study to be removed is not referenced by existing credits 
	$ok = refck_studies_credits($st_id);
	if( $ok == -1 ) {
		return 'ERROR: You can\'t remove that study now, at least one student has been given credits for it. You can remove the study next time the database is wiped.';
	}

	$st_ids = array($st_id);
	$result = delete_studies($st_ids);
	if( !$result ) {
		return 'ERROR: There was an error, the study couldn\'t be removed.';
	}

	// here scan all flyers/st_id/ and unlink any files present, then rmdir() the directory
	// result of removing the files is not checked:
	//	- the resulting error message would confuse the user
	//	- it desn't break anything if it fails

	$files = scandir( "utility/flyers/${st_id}/" );
	foreach( $files as $file ) {
		if( $file == "." || $file == ".." )
			continue;
		unlink("utility/flyers/${st_id}/$file");
	}
	rmdir("utility/flyers/${st_id}");

	return 'The study was removed successfully.';
}

function studies_toggle_visibility($st_id) {
	$result = study_toggle_visibility($_POST['st_id']);
	if( $result )
		email_all_students($ad, $st_id);
		return 'The visibility was toggled.';
	return 'ERROR: The visibility could not be toggled.';
}

function email_all_students($ad, $st_id) {
	$conn = open_connection();
	if( empty($ad) ) {
		$query = "select * from students";
	}
	else {
		$ad_san = mysql_real_escape_string($ad, $conn);
		$query = "select * from student where s_ad = '$ad_san'";
	}
	$result = mysql_query($query, $conn);
	mysql_close($conn);
	if( empty($ad) ) {
		$return = array();
		$num_students = mysql_num_rows($result);
		for($i=0; $i<$num_students; $i++) {
			$s_lname = mysql_result($result, $i, "s_lname");
			$s_fname = mysql_result($result, $i, "s_fname");
			$s_ad = mysql_result($result, $i, "s_ad");
			
			send_email($s_lname, $s_fname, $s_ad, $st_id);
		}
	}
	else {
		if( mysql_num_rows($result) != 1 )
			return " ";
		$s_lname = mysql_result($result, 0, "s_lname");
		$s_fname = mysql_result($result, 0, "s_fname");
	}
}

function send_email($s_lname, $s_fname, $s_ad, $st_id) {
	$conn = open_connection();
	$studies = get_studies();
	$study = $studies[$st_id];
	if($study['st_visible']==0){
		return "does not email when studies are no longer visible";
	}	
	$recipient = $s_ad . "@clarkson.edu";
	$subject = "PY151: New Study Available";
	$headers = "From: donotreply@clarkson.edu" . "\r\n" . "X-Mailer: PHP/" . phpversion() . "\r\n" . "Content-type: text/html\r\n";
	
	/* use PHP object buffering to make it easier to output the HTML message */
	ob_start();
	$num_credits = $study['st_credits'];
	$st_desc = $study['st_desc'];
	$st_flyer = $study['st_flyer'];

echo <<<EOF
<html>
	<head></head>
	<body>
		<div id="studentcontent">
			<p>
				The study <b>$st_desc</b> has been made available for <b>$num_credits</b> credits.<br>
				You can find more information for this study at the link below: 
				<br><br>
				web2.clarkson.edu/projects/researchcredit/utility/flyers/$st_id/$st_flyer
			</p>
			
			<p>This is an automatically-generated email, please do not reply to it. If you have questions, please ask your professor.</p>
		</div>
	</body>
</html>
EOF;

	$message = ob_get_contents();
	ob_end_clean();
	$success = mail($recipient, $subject, $message, $headers);
	if($success){
		$email_msg = "the report has been sent.";
	}else{
		$email_msg = "Oops! Something went wrong contacting the mail server, so your report could not be sent.";
	}
	mysql_close($conn); 

}

function studies_edit_info($st_id, $irb, $credits, $desc) {
	// check that a flyer was uploaded
	if( $_FILES['flyer']['error'] != 4 )
		$flyer = true;
	else
		$flyer = false;

	if( $irb == "" && $credits == -1 && $desc == "" && $flyer == false ) {
		return 'ERROR: You didn\'t change anything!';
	}

	// check that there are no upload errors
	if( $flyer ) {
		if( $_FILES['flyer']['error'] != 0 ) {
			return 'ERROR: A file upload error occurred.';
		}
		// check the file type
		if( $_FILES['flyer']['type'] != "application/pdf" ) {
			return 'ERROR: Only PDF files are accepted. This file has the MIME type "' . $_FILES['flyer']['type'] . '".';
		}
		// move the file to the tmp directory
		$upname = "utility/flyers/tmp/" . time() .'_' . $_FILES['flyer']['name'];
		$result = move_uploaded_file($_FILES['flyer']['tmp_name'], $upname);
		if( !$result ) {
			return 'ERROR: A file upload error occurred.';
		}
		// delete the existing flyer
		$files = scandir("utility/flyers/${st_id}/");
		foreach( $files as $file ) {
			if( $file == "." || $file == ".." )
				continue;
			unlink("utility/flyers/$_POST[st_id]/$file");
		}
		// move the new file in place
		$newname = 'utility/flyers/' . $_POST['st_id'] . '/' . $_FILES['flyer']['name'];
		$result = rename("$upname", $newname);
		if( !$result ) {
			return 'ERROR: The old flyer could not be removed.';
		}
		$flyer = $_FILES['flyer']['name'];
	}
	else
		$flyer = "";
	
	// update the database
	$result = study_update($st_id, $irb, $credits, $desc, $flyer);
	if( $result )
		return 'The study\'s information was updated successfully.';
	return 'ERROR: The study\'s information couldn\'t be updated.';
}

?>
