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

if( !isset($_SESSION['active']) || $_SESSION['is_student']) {
	echo '<meta http-equiv="refresh" content="0; url=index.php">';
	exit;
}

if( isset($_POST['addmisc']) )
	$message = studentdisplay_add_misc_credits($_POST['num_credits'], $_POST['description'], $_POST['ad']);
else if( isset($_POST['quick_add']) )
	$message = studentdisplay_add_study_credits($_POST['st_id'], $_POST['desc'], $_POST['ad']);
else if( isset($_POST['remove']) )
	$message = studentdisplay_remove_credits($_POST['description'], $_POST['ad']);
else
	$message = '';

$block_num = 0;
$blocks = get_blocks();
$block_one_start = $blocks[1];
$block_two_start = $blocks[2];
$block_three_start = $blocks[3];
$semester_end = $blocks[4];

if( time() >= $block_one_start && time() < $block_two_start )
	$block_num = 1;
else if( time() >= $block_two_start && time() < $block_three_start )
	$block_num = 2;
else if( time() >= $block_three_start && time() < $semester_end )
	$block_num = 3;
else
	$block_num = 1;

$studentinfo = query_students($_GET['studentdisplay']);
$studentcredits = query_credits($_GET['studentdisplay'], false);
$removedcredits = query_credits($_GET['studentdisplay'], true);
$aggregated_credits = count($studentcredits) + count($removedcredits);
$total_credits = 0;

for( $i = 0; $i < sizeof($studentcredits); $i++)
	$total_credits += $studentcredits[$i][u_amount];
	
$studies = get_studies();
print_action_result($message);

echo '<div id="studentdisplay">';

if( empty($studentinfo) )
	return "ERROR: Invalid Student.";
	
echo <<<EOF
<div class="section">
	<h2>Student:  </h2> $studentinfo[fname] $studentinfo[lname] ($studentinfo[ad]) <br>
	<h2>Professor: </h2>   $studentinfo[prof]
	<br><br>
EOF;

if( $total_credits == 1 )
	echo "<p>This student has <strong>$total_credits</strong> credit.</p>";
else
	echo "<p>This student has <strong>$total_credits</strong> credits.</p>";

echo <<<EOF
</div>
<div class="section">
	<h2>Add Miscellaneous Credits</h2>
		<p>Miscellaneous credits will show up in the credit listing for this student, but they are not associated with a particular study.</p>
		<form method="post" action="user.php?studentdisplay=$_GET[studentdisplay]">
	
		<dl>
			<dt>Number of credits</dt>
			<dd>
				<select name="num_credits">
					<option value="0.5">0.5</option>
					<option value="1">1.0</option>
					<option value="1.5">1.5</option>
					<option value="2">2.0</option>
					<option value="2.5">2.5</option>
					<option value="3">3.0</option>
					<option value="3.5">3.5</option>
					<option value="4">4.0</option>
					<option value="4.5">4.5</option>
					<option value="5">5.0</option>
				</select>
			</dd>
		<dt>Block</dt>
		<dd>
			<select name="block_num">
EOF;
if( $block_num == 1 )
{
	echo "<option value=\"1\" selected=\"selected\">1</option>";
	echo "<option value=\"2\">2</option>";
	echo "<option value=\"3\">3</option>";
}
else if( $block_num == 2 )
{
	echo "<option value=\"1\">1</option>";
	echo "<option value=\"2\" selected=\"selected\">2</option>";
	echo "<option value=\"3\">3</option>";
}
else if( $block_num == 3 )
{
	echo "<option value=\"1\">1</option>";
	echo "<option value=\"2\">2</option>";
	echo "<option value=\"3\" selected=\"selected\">3</option>";
}

echo <<<EOF
			</select>
		</dd>
	<dt></dt>
	<dt>Description</dt>
	<dd><input class="text" type="text" name="description"/> <span class="input_explanation">(Required)</span></dd>
</dl>

<p>
	<input type="hidden" name="ad" value="$_GET[studentdisplay]"/>
	<input type="submit" value="Add Credits" name="addmisc"/>
</p>
</form>
</div>

<div class="section">
	<h2>Add Study Credits</h2>
	<p>Credits added this way are associated with a study.</p>
	<form method="post" action="user.php?studentdisplay=$_GET[studentdisplay]">
EOF;

if( empty($studies) ) {
	echo '		<p>No studies exist yet. At least one must exist before you can add credits.</p>';
}
else {

echo <<<EOF
		<dl>
			<dt>Study</dt>
			<dd>
				<select name="st_id">
EOF;

	foreach( $studies as $study ) {
		echo "				<option value=\"$study[st_id]\">IRB #$study[st_irb]: $study[st_desc] [$study[st_credits] credits]</option>";
	}

echo <<<EOF
				</select>
			</dd>
		<dt>Block</dt>
		<dd>
			<select name="block_num_study">
EOF;
	if( $block_num == 1 )
	{
		echo "<option value=\"1\" selected=\"selected\">1</option>";
		echo "<option value=\"2\">2</option>";
		echo "<option value=\"3\">3</option>";
	}
	else if( $block_num == 2 )
	{
		echo "<option value=\"1\">1</option>";
		echo "<option value=\"2\" selected=\"selected\">2</option>";
		echo "<option value=\"3\">3</option>";
	}
	else if( $block_num == 3 )
	{
		echo "<option value=\"1\">1</option>";
		echo "<option value=\"2\">2</option>";
		echo "<option value=\"3\" selected=\"selected\">3</option>";
	}
echo <<<EOF

			</select>
		</dd>

		<dt>Description</dt>
		<dd><input class="text" type="text" name="desc"/> <span class="input_explanation">(Not required)</span></dd>
	</dl>

<p>
	<input type="hidden" name="ad" value="$_GET[studentdisplay]"/>
	<input type="submit" name="quick_add" value="Add Study Credits"/>
</p>

EOF;
}

echo '</form>
	</div>';

if( $aggregated_credits > 0 ) {
echo <<<EOF
<form method="post" action="user.php?studentdisplay=$_GET[studentdisplay]">
	<div class="section">
		<h2>Remove Selected Credits</h2>
		<p><strong>Removing a credit is permanent and cannot be reversed.</strong></p>
		<p>Please select the credit(s) you want to remove from the table, and give your reason for removing them below.</p>	
		<p>Reason: <input class="text" type="text" name="description"/> <span class="input_explanation">(Required)</span></p>
		<p>
			<input type="hidden" name="ad" value="$_GET[studentdisplay]"/>
			<input type="submit" value="Remove Credits" name="remove"/>
		</p>
	</div>
EOF;

	/* 
		this was a bit confusing... it separates all the credits in each list (nonremoved and removed) into groups made unique by the timestamp left when they were added
		this isn't really a good way to approach the problem, but it's the only way unless the schema is changed to include credit groups based on study
		hopefully the timestamp + username combo will prove to be "unique enough" :(
	*/
	$credits_show = array();
	foreach( $studentcredits as $credit ) {
		if( $credit['st_id'] != -1 )
			$credits_show["$credit[time_add]_$credit[u_add]"] = $credit;
	}
	$credits_show = array_values($credits_show);
	
	$removed_credits_show = array();
	foreach( $removedcredits as $credit ) {
		if( $credit['st_id'] != -1 )
			$removed_credits_show["$credit[time_add]_$credit[u_add]"] = $credit;
	}
	$removed_credits_show = array_values($removed_credits_show);
	
	// get arrays of all nonremoved and removed misc credits
	$misc_credits_show = array();
	foreach( $studentcredits as $credit ) {
		if( $credit['st_id'] == -1 )
			$misc_credits_show[] = $credit;
	}
	$misc_removed_credits_show = array();
	foreach( $removedcredits as $credit ) {
		if( $credit['st_id'] == -1 )
			$misc_removed_credits_show[] = $credit;
	}

echo <<<EOF
<div class="section">
	<table>
		<tr>
			<th>Study</th>
			<th>Given/Removed by</th>
			<th>Date/Time</th>
			<th>Description</th>
			<th>Credits</th>
			<th>Block</th>
			<th>Selection</th>
		</tr>
EOF;
	
	foreach( $credits_show as $credit ) {
		$credits = $studies[$credit['st_id']]['st_credits'];
		$irb = $studies[$credit['st_id']]['st_irb'];

echo <<<EOF
<tr>
	<td>IRB #$irb</td>
	<td>$credit[u_add]</td>
	<td>$credit[time_add]</td>
	<td>$credit[desc_add]</td>
	<td>$credits</td>
	<td>$credit[block_num]</td>
	<td>
	<input type="checkbox" name="$credit[time_add]_$credit[u_add]" value="identifier"/>
	</td>
</tr>
EOF;

	}
	foreach( $misc_credits_show as $credit ) {

echo <<<EOF
<tr>
	<td>Miscellaneous</td>
	<td>$credit[u_add]</td>
	<td>$credit[time_add]</td>
	<td>$credit[desc_add]</td>
	<td>$credit[u_amount]</td>
	<td>$credit[block_num]</td>
	<td>
		<input type="checkbox" name="$credit[p_id]" value="identifier_misc"/>
	</td>
</tr>
EOF;
		
	}
	
	foreach( $removed_credits_show as $credit ) {
		$credits = $studies[$credit['st_id']]['st_credits'];
		$irb = $studies[$credit['st_id']]['st_irb'];

echo <<<EOF
<tr>
	<td class="credit_removed">IRB #$irb</td>
	<td class="credit_removed">$credit[u_add]</td>
	<td class="credit_removed">$credit[time_rem]</td>
	<td class="credit_removed">$credit[desc_rem]</td>
	<td class="credit_removed">$credits</td>
	<td class="credit_removed">$credit[block_num]</td>
	<td class="credit_removed">(Removed)</td>
</tr>
EOF;

	}

	foreach( $misc_removed_credits_show as $credit ) {

echo <<<EOF
<tr class="credit_removed">
	<td class="credit_removed">Miscellaneous</td>
	<td class="credit_removed">$credit[u_rem]</td>
	<td class="credit_removed">$credit[time_rem]</td>
	<td class="credit_removed">$credit[desc_rem]</td>
	<td class="credit_removed">$credits</td>
	<td class="credit_removed">$credit[block_num]</td>
	<td class="credit_removed">(Removed)</td>
</tr>
EOF;
	}

echo <<<EOF
</table>
</div>
</form>
EOF;

}

echo '</div>';

/* -------------------------------------------------------------------------- */
/* studentdisplay functions */
/* -------------------------------------------------------------------------- */

function studentdisplay_add_misc_credits($num_credits, $desc, $s_ad) {
	if( empty($desc) )
		return 'ERROR: You must enter a description.';
	$block_num = $_POST['block_num'];
	if( $block_num == 1 || $block_num == 2 || $block_num == 3 )
		$result = insert_credits(array($s_ad), $num_credits, -1, $desc, $_SESSION['ad'], $block_num);
	if( $result )
		send_addition_email($s_ad, NULL, $num_credits);
		return 'Credits were added successfully.';
	return 'ERROR: Credits could not be added. Not all credits could be added successfully, please try again.';
}

function studentdisplay_add_study_credits($st_id, $desc, $s_ad) {
	$studies = get_studies();
	$num_credits = $studies[$_POST['st_id']]['st_credits'];
	$s_desc = $studies[$_POST['st_id']]['st_desc'];
	$block_num = $_POST['block_num_study'];
	if( $block_num == 1 || $block_num == 2 || $block_num == 3 )
		$result = insert_credits(array($s_ad), $num_credits, $st_id, $desc, $_SESSION['ad'], $block_num);
	if( $result )
		send_addition_email($s_ad, $s_desc, $num_credits);
		return 'Credits were added successfully.';
	return 'ERROR: Credits could not be added. Not all credits could be added successfully, please try again.';
}

function studentdisplay_remove_credits($desc, $ad){
	if( empty($desc) )
		return 'ERROR: You must enter a description.';
	send_removal_email($ad, $desc);
	$identifiers = array();
	$misc_pids = array();
	foreach( $_POST as $key => $val ) {
		if( $val == 'identifier' )
			$identifiers[] = $key;
		if( $val == 'identifier_misc' )
			$misc_pids[] = $key;
	}

	// PHP replaces the ' ' in all GET variables with an underscore
	// this is a problem for the identifiers, which have a space between the date and time in the timestamp
	// so, check for this and remove the underscore if necessary
	$ids = array();
	foreach( $identifiers as $id ) {
		if( $id[10] == '_' ) {
			$parts = explode('_', $id, 2);
			$ids[] = $parts[0] . ' ' . $parts[1];
		}
	}
	$student_credits = query_credits($ad, false);
	$to_remove = array();
	foreach( $student_credits as $credit ) {
		if( in_array("$credit[time_add]_$credit[u_add]", $ids) ) {
			$to_remove[] = $credit['p_id'];
		}
	}
	foreach( $misc_pids as $p_id ) {
		$to_remove[] = $p_id;
	}
	
	if( count($to_remove) == 0 )
		return 'ERROR: No credits selected for removal.';

	$success = remove_credits($to_remove, $_SESSION['ad'], $desc);
	if( $success )
		return 'All credits were removed successfully.';
	return 'ERROR: There was a database error, not all credits were removed.';
}

function send_addition_email($s_ad, $s_desc, $s_credits){
	$recipient = $s_ad . "@clarkson.edu";

	$fname = query_student_fname($s_ad);
	$lname = query_student_lname($s_ad);
	

	$subject = "PY151 Research Credits Added for " . $fname . " " . $lname;
	$headers = "From: donotreply@clarkson.edu" . "\r\n" . "X-Mailer: PHP/" . phpversion() . "\r\n" . "Content-type: text/html\r\n";
	/* use PHP object buffereing to make it easier to output the HTML message */
	ob_start();
	
	if( $s_desc == NULL )
		$message = "<b>$s_credits</b> miscellaneous credit(s) have been added to your PYCTS account.";
	else
		$message = "<b>$s_credits</b> credit(s) have been added to your PYCTS account for the study: <b>$s_desc.</b>";

echo <<<EOF
<html>
	<head></head>
	<body>
		<div id="studentcontent">
			<p>$message</p>
			<p>This is an automatically-generated email, please do not reply to it. If you have questions, please ask your professor.</p>
		</div>
	</body>
</html>
EOF;

	$message = ob_get_contents();
	ob_end_clean();
	$success = mail($recipient, $subject, $message, $headers);
	if( $success ) {
		$email_msg = "The report has been sent.";
	}
	else {
		$email_msg = "Oops! Something went wrong contacting the mail server, your report could not be sent.";
	}
}

function send_removal_email($s_ad, $desc){
	$recipient = $s_ad . "@clarkson.edu";

	$fname = query_student_fname($s_ad);
	$lname = query_student_lname($s_ad);

	$subject = "PY151 Research Credits Removed for " . $fname . " " . $lname;
	$headers = "From: donotreply@clarkson.edu" . "\r\n" . "X-Mailer: PHP/" . phpversion() . "\r\n" . "Content-type: text/html\r\n";

	/* use PHP object buffereing to make it easier to output the HTML message */
	ob_start();

echo <<<EOF
<html>
	<head></head>
	<body>
		<div id="studentcontent">
			<p>Credits have been removed from your account. (Reason: <b>$desc</b>)</p>
			<p>This is an automatically-generated email, please do not reply to it. If you have questions, please ask your professor.</p>
		</div>
	</body>
</html>
EOF;

	$message = ob_get_contents();
	ob_end_clean();
	$success = mail($recipient, $subject, $message, $headers);
	if( $success ) {
		$email_msg = "The report has been sent.";
	}
	else {
		$email_msg = "Oops! Something went wrong contacting the mail server, your report could not be sent.";
	}
}

?>

