<?php
$filename = '.\\test.txt';
$file = fopen( $filename, "w" );
echo "FOO";
var_dump($file);
echo "BAR";
fwrite( $file, "test" );
fclose( $file );
?>
