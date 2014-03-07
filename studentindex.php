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


/* need to check if a session has already been started */
/* this condition happens once, when this file is included in index.php immediately after login */
if (!isset($_SESSION)) {
    session_start();
}

require_once("includes/db.php");
require_once("includes/html_print.php");
require_once("includes/globals.php");


/* ------------------------------------------------------------------------- */
/* Check that the user is logged in */
/* ------------------------------------------------------------------------- */
if (!isset($_SESSION['active'])) {
    print_system_message("Invalid Session", "Sorry, you can't access this page until you log in.", "index.php");
    exit;
}

/* ------------------------------------------------------------------------- */
/* Send the user an email, as they've requested */
/* ------------------------------------------------------------------------- */
if (isset($_POST['send_email'])) {
    /* this RFC 2822-compliant regex should match all email addresses a student might use */
    /* it is not my own work, it is adapted from the freely given regex at http://www.regular-expressions.info/email.html */
    /* original author: Jan Goyvaerts */
    $rfcregex  = "[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?";
    $recipient = trim($_POST['address']);
    if (empty($recipient)) {
        $email_msg = "Please enter an e-mail address.";
    }
    if (preg_match("/$rfcregex/", $recipient) != 1) {
        $email_msg = "That email address ($recipient) is not valid.";
    }
    $subject = "PY151 Research Credits Report for $_SESSION[fname] $_SESSION[lname]";
    $headers = "From: donotreply@clarkson.edu" . "\r\n" . "X-Mailer: PHP/" . phpversion() . "\r\n" . "Content-type: text/html\r\n";
    /* use PHP object buffering to make it easier to output the HTML message */
    ob_start();
    echo <<<EOF
<html>
	<head></head>
	<body>
		<div id="studentcontent">
			<h1>Report for $_SESSION[fname] $_SESSION[lname]:</h1>
EOF;
    
    print_credits_table();
echo <<<EOF
			<p>
				This is an automatically-generated email, please do not reply to it. If you have questions, please ask your professor.
			</p>
		</div>
	</body>
</html>
EOF;
    
    $message = ob_get_contents();
    ob_end_clean();
    $success = mail($recipient, $subject, $message, $headers);
    if ($success) {
        $email_msg = "The report has been sent.";
    } else {
        $email_msg = "Oops! Something went wrong contacting the mail server, so your report couldn't be sent.";
    }
}

/* ------------------------------------------------------------------------- */
/* Print the main screen */
/* ------------------------------------------------------------------------- */
print_html_head("Credit Status");

echo <<<EOF
<div id="student_content">
<h2>PY151 Research Credit for $_SESSION[fname] $_SESSION[lname]</h2>
EOF;

print_credits_table();

echo <<<EOF

<form method="post" action="user.php?report">
<p class="table">
<input type="text" name="address" size="30" value="$_SESSION[ad]@clarkson.edu"/>
<input type="submit" name="send_email" value="Send a credit report."/>
</p>
</form>
EOF;

if (!empty($email_msg)) {
    echo "<p class=\"table\">$email_msg</p>";
}


echo '</div>';

print_html_tail();

/* ------------------------------------------------------------------------- */
/* functions */
/* ------------------------------------------------------------------------- */

/* echoes the table of credits for the current user */
function print_credits_table() {
    $credits     = query_credits($_SESSION['ad'], false);
    $num_credits = 0;
    for ($i = 0; $i < sizeof($credits); $i++) {
        $num_credits += $credits[$i][u_amount];
    }
    $studies = get_studies();
    echo "<p class=\"table\">You have <strong>$num_credits</strong> credits.</p>";
    
    if ($num_credits == 0) {
        return;
    }
    
echo <<<EOF
	<table>
		<tr>
			<th>Research Performed</th>
			<th>Date Added</th>
			<th>Credit Block</th>
			<th>Credits Earned</th>
		</tr>
EOF;
    
	// These two loops prepare the study credits to be printed
    $groups = array();
    foreach ($credits as $credit) {
        if ($credit['st_id'] != -1)
            $groups["$credit[time_add]_$credit[u_add]"] = $credit;
    }
    
    $groups   = array_values($groups);
    $to_print = array();
    foreach ($groups as $item) {
        $to_print[] = array(
            'study' => $studies[$item['st_id']]['st_desc'],
            'credits' => $studies[$item['st_id']]['st_credits'],
            'irb' => $studies[$item['st_id']]['st_irb'],
            'block' => $item['block_num'],
            'date' => $item['time_add']
        );
    }
    
	// These two loops prepare the misc credits to be printed
    $groups = array();
    foreach ($credits as $credit) {
        if ($credit['st_id'] == -1)
            $groups["$credit[time_add]_$credit[u_add]"] = $credit;
    }
    
    $groups = array_values($groups);
    foreach ($groups as $item) {
        $to_print[] = array(
            'study' => "Miscellaneous",
            'credits' => $item['u_amount'],
            'irb' => "N/A",
            'block' => $item['block_num'],
            'date' => $item['time_add']
        );
    }
    
	// Print to table
    foreach ($to_print as $item) {
        echo "<tr>";
        echo "<td>$item[study] <br/> (IRB #$item[irb])</td>";
        echo "<td>$item[date]</td>";
        echo "<td>$item[block]</td>";
        echo "<td>$item[credits]</td>";
        echo "</tr>";
        
    }
    echo '</table>';
}

?>
