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
	if( isset($_POST['modify_blocks']) ) {
		$message = update_blocks($bcount);
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
		<h2>Modify Blocks</h2>		
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
echo <<<EOF
			</dl>
			<p><input type="submit" name="modify_blocks" value="Modify Blocks"/></p>
		</div>
EOF;

/* -------------------------------------------------------------------------- */
/* System Options functions */
/* -------------------------------------------------------------------------- */

function update_blocks($bcount) {
	$times = array();
	for( $i = 1; $i <= $bcount + 2; ++$i )
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
