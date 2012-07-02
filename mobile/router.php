<?php
$current_path   = explode('/', $_SERVER["REQUEST_URI"]);
//echo '<pre>' . print_r($current_path, true) . '</pre>'; exit;

if(!isset($current_path) || (isset($current_path[2]) && $current_path[2] == 'home')) {

  header("HTTP/1.0 301 Moved Permanently");
  header("Location: http://{$_SERVER["SERVER_NAME"]}/mobile");

} elseif(in_array($current_path[2], $valid_paths)) {

  $module = $current_path[2];
  $dict['page'] = $module;
  include_once 'views/'.$current_path[2].'.php';

} elseif(isset($current_path[2]) == 'index.php' || $current_path[2] == '' ) {

  $module = 'home';
  $dict['page'] = $module;
  include_once 'views/home.php';

} else {

  header('HTTP/1.1 404 Not Found');
  include_once 'views/404.php';

}