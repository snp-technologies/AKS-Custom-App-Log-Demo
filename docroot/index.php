
<?php
echo '<H1>Syslog Demo</H1>';

// Fetch the URL query string and assign to a variable. 
$qstring = $_SERVER['QUERY_STRING'];

parse_str($qstring, $output);

$value = $output['value'];

// For an error evaluation, we divide 1 by $value.
// A value of 0 will throw a division by zero error.
function inverse($x) {
    if (!$x) {
        throw new Exception('Division by zero.');
    }
    return 1/$x;
}

// Get formatted datetime to include in messages.
$access = date("Y/m/d H:i:s");

try {
    echo "<p>Inverse of value " , $value , " is " , inverse($value) , "</p>";
    $message = "Inverse of value succeeded";
    $severity = LOG_INFO;
    $logtext = "INFORMATION: at " . $access . "\n" . $message . "\n" . $_SERVER['REMOTE_ADDR'];
    _log($severity, $logtext);
    
} catch (Exception $e) {
    $message = $e->getMessage();
    echo '<p>Caught exception: ',  $message, "</p>";
    $severity = LOG_ERR;
    $logtext = "ERROR: at " . $access . "\n" . $message . "\n" . $_SERVER['REMOTE_ADDR'];
    _log($severity, $logtext);
}

// Open and close connection of system logger.
function _log($priority, $text) {
  openlog("myApp", LOG_PID | LOG_PERROR, LOG_LOCAL0);  
  syslog($priority, $text);
  closelog();
}

?>
