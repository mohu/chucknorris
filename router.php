<?php
$current_path   = explode('/', $_SERVER["REQUEST_URI"]);
//echo '<pre>' . print_r($current_path, true) . '</pre>'; exit;

if(!isset($current_path) || ($current_path[1] == 'index.php') || ($current_path[1] == 'home')) {

  header("HTTP/1.0 301 Moved Permanently");
  header("Location: http://{$_SERVER["SERVER_NAME"]}/");

} elseif(isset($current_path[1]) && in_array($current_path[1], $valid_paths)) {

  $module = $current_path[1];
  $dict['page'] = $module;
  include_once 'views/'.$current_path[1].'.php';

} elseif($current_path[1] == 'index.php' || ($current_path[1] == '') ) {

  $module = 'home';
  $dict['page'] = $module;
  include_once 'views/home.php';

} else {

  header('HTTP/1.1 404 Not Found');
  include_once 'views/404.php';

}