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
	if( isset($_POST['modify_blocks']) ) {
		$message = update_blocks($_POST['b1Begin'], $_POST['b2Begin'], $_POST['b3Begin'], $_POST['dateEnd']);
	}
	else
		$message = '';
}else{
	$message = '';
}


print_action_result($message);

$blocks = get_blocks();
$tstamp = time();
$dateB1 = date("m/d/y", $blocks[1]);
$dateB2 = date("m/d/y", $blocks[2]);
$dateB3 = date("m/d/y", $blocks[3]);
$dateEnd = date("m/d/y", $blocks[4]);

echo <<<EOF
<div id="sysOpts">

<div class="section">
<h2>Modify Blocks</h2>

<form action="user.php?system_options" method="post">
<input type="hidden" name="action_timestamp" value="$tstamp"/>

<p>All dates on this page use MM/DD/YY format:</p>
<dl>
<dt>Start of Semester</dt>
<dd>

	<input class="text" type="text" name="b1Begin" value="$dateB1"/>
</dd>

<dt>Start of Block Two</dt>
<dd>
	<input class="text" type="text" name="b2Begin" value="$dateB2"/>
</dd>

<dt>Start of Block Three</dt>
<dd>
	<input class="text" type="text" name="b3Begin" value="$dateB3"/>
</dd>

<dt>End of Semester</dt>
<dd>
        <input class="text" type="text" name="dateEnd" value="$dateEnd"/>
</dd>
</dl>

<p>
<input type="submit" name="modify_blocks" value="Modify Blocks"/>
</p>

</div> <!-- closing section div -->

EOF;

/* -------------------------------------------------------------------------- */
/* System Options functions */
/* -------------------------------------------------------------------------- */

function update_blocks($b1Begin, $b2Begin, $b3Begin, $endSem) {
	$b1Stamp = strtotime($b1Begin);
	$b2Stamp = strtotime($b2Begin);
	$b3Stamp = strtotime($b3Begin);
	$endSemStamp = strtotime($endSem);
	if($b2Stamp <= $b1Stamp || $b3Stamp <= $b1Stamp || $endSemStamp <= $b1Stamp)
                return 'ERROR: Block one must have the earliest start date.';
	else if($b3Stamp <= $b2Stamp || $endSemStamp <= $b2Stamp)
                return 'ERROR: Dates appear to be out of order.';
	else if($endSemStamp <= $b3Stamp)
                return 'ERROR: Dates appear to be out of order.';
	else
		return update_block_times($b1Stamp,$b2Stamp,$b3Stamp,$endSemStamp);
}
?>
