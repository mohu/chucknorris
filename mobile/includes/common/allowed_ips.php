<?php
$allowed_ips = R::getAll( 'SELECT * FROM allowedips' );

$allowed = false;

foreach ($allowed_ips as $allowedip) {
  if ($allowedip["ip"]){
    if (substr_count($_SERVER['REMOTE_ADDR'], trim($allowedip["ip"])) != "0") {
        $allowed = true;
    }
  }
}
if ($allowed != true) {
  // the banned display page
  header('HTTP/1.1 401 Unauthorized');
  include_once realpath(dirname(__FILE__).'/../..'). '/admin/views/forbidden.php';
}