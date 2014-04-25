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

$blocks = get_blocks();
$tstamp = time();
$bcount = sizeof($blocks);

/* first check that and POST variables are set - if so, then we need to do some action */
if( !$is_repeat_action ) {
	print $POST;
	if( isset($_POST['modify_blocks']) && !credits_exist() ) {
		$message = update_blocks($bcount);
	}
	else if( isset($_POST['update_email']) ) {
		$title = $_POST['update_email'];
		//strip off additional text
		$title = substr( $title, strpos( $title, "'" ) );
		$title = substr( $title, 0, strlen( $title ) - strpos( $title, "'" ) - 1 );
		$title = substr( $title, 1 );
		$message = update_email($title, $_POST['new_text']);
	}
	else if( isset($_POST['send_test_email'])) {
		$message = send_email($_SESSION['ad'], $_POST['email_choice']);
	}
	else
		$message = '';
}else{
	$message = '';
}


print_action_result($message);

echo <<<EOF
<div id="system_options">
	<div class="section">
		<h2>Modify Blocks</h2> <i>Note: This feature will only work if no credits are assigned.</i>	
		<form action="user.php?system_options" method="post">
			<input type="hidden" name="action_timestamp" value="$tstamp"/>			
			<p>All dates on this page use MM/DD/YY format:</p>
			<dl>
EOF;
for( $i = 1; $i <= $bcount; ++$i )
{
	if( $i < $bcount )
		echo "<dt>Start of Block $i</dt>";
	else
		echo "<dt>End of Semester</dt>";
	echo '<dd>';
	
	$date = date("m/d/y", $blocks[$i]);
	$name = "b".$i."Date";
	echo "<input class=\"text\" type=\"text\" name=\"$name\" value=\"$date\"/>";
	echo '</dd>';
}
$emails = get_all_emails();
if( credits_exist() )
	$modifiable = "disabled";
echo <<<EOF
			</dl>
			<p><input type="submit" name="modify_blocks" value="Modify Blocks" $modifiable/></p>
		</form>
	</div>
	<div class="section">
EOF;
foreach($emails as $email) {
	echo "
		<h2>Modify Text for '$email[title]'</h2>
		<form action=\"user.php?system_options\" method=\"post\">
			<dl>
				<textarea rows=\"4\" cols=\"50\" name=\"new_text\">
$email[text]</textarea>
				<p><input type=\"submit\" name=\"update_email\" value=\"Update text of '$email[title]'\"/></p>
			</dl>
		</form>
	</br>";
}
echo '
	<h2>Test Email</h2>
		<form action="user.php?system_options" method="post">
			<dl>
				<dd>
					<select name="email_choice" id="email_choice">';
foreach($emails as $email) {
	if($email['title'] == 'footer')
		continue;
	echo "<option value=\"$email[title]\" ";
	if($i==1)
		echo "selected=\"selected\"";
	echo ">$email[title]</option>";
}
echo '				</select>
					<input type="submit" name="send_test_email" value="Send Test Email"/>
				</dd>
			</dl>
		</form>
	</div>';

/* -------------------------------------------------------------------------- */
/* System Options functions */
/* -------------------------------------------------------------------------- */

function update_blocks($bcount) {
	$times = array();
	for( $i = 1; $i <= $bcount; ++$i )
	{
		$str = "b".$i."Date";
		if( $_POST["$str"] == "00/00/00" )
			break;
		$new_time = strtotime($_POST["$str"]);
		$times[$i] = $new_time;
		if( $i > 1 && $times[$i] <= $times[$i-1] )
		{
			return 'ERROR: Block times not in order.';
		}
	}
	return update_block_times($times);
}
?>
