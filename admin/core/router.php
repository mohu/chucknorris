<?php
if (!$app->checkSession()) {
  include_once 'views/login.php';
  return;
}

$current_path   = explode('/', $_SERVER["REQUEST_URI"]);

if(!isset($current_path) || ($current_path[2] == 'index.php' || (isset($current_path[2]) && $current_path[2] == 'admin')) ) {

  header("HTTP/1.0 301 Moved Permanently");
  header("Location: http://{$_SERVER["SERVER_NAME"]}/admin");

} elseif(in_array($current_path[2], $valid_paths)) {

  ## Set global dictionary values
  $dict['module'] = $module = $view = $current_path[2];

  ## Include view
  $app->includeView('views/' . $view . '.php', 'admin', true);

  ## Instantiate
  $app->initView($view, 'admin'); exit;

} elseif($current_path[2] == 'index.php' || ($current_path[2] == '') ) {

  include_once 'views/admin.php';

} else {

  header('HTTP/1.1 404 Not Found');
  include_once 'views/404.php';

}