<?php
$allowed_ips = R::getAll( 'SELECT * FROM allowedips' );

$allowed = false;

if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] != '')
    $ip = $_SERVER['HTTP_CLIENT_IP'];
elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '')
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != '')
    $ip = $_SERVER['REMOTE_ADDR'];
if (($commapos = strpos($ip, ',')) > 0)
    $ip = substr($Ip, 0, ($commapos - 1));

foreach ($allowed_ips as $allowedip) {
  if ($allowedip["ip"]) {
    if (substr_count($ip, trim($allowedip["ip"])) != "0") {
        $allowed = true;
    }
  }
}
if ($allowed != true) {
  // the banned display page
  header('HTTP/1.1 401 Unauthorized');
  include_once realpath(dirname(__FILE__).'/../..'). '/admin/views/forbidden.php';
}