<?php
require_once("../includes/db.php");
/*$subject = "PY151 Research Credits Added for " . $fname . " " . $lname;
$headers = "From: donotreply@clarkson.edu" . "\r\n" . "X-Mailer: PHP/" . phpversion() . "\r\n" . "Content-type: text/html\r\n";*/
echo br_create_backup();

$handle = opendir("download/");
$entry = readdir($handle);

$most_recent = $entry;
do{
	echo $entry;
	$file_parts = pathinfo($entry);
	if( $file_parts['extension'] == 'csv' && filemtime($entry) > $most_recent )
		filemtime($file_parts);
}
while ($entry = readdir($handle));
closedir($handle);
$info = pathinfo($most_recent);
echo $most_recent;

ob_start();
//print info['filename'];

/*echo <<<EOF
<html>

<head>
</head>

<body>
<div id="studentcontent">

EOF;
echo <<<EOF
<p>
$message
<p>

<p>
This is an automatically-generated email, please do not reply to it. If you have questions, please ask your professor.
</p>
EOF;
echo <<<EOF
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
	email_msg = "Oops! Something went wrong contacting the mail server, so your report could not be sent.";
} */


?>
