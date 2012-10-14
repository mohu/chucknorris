<?php
define ('FRONTEND', 1 );
require_once 'admin/core/app.php';
$app = new App();

foreach($valid_paths as $pattern => $callback) {
  if (preg_match($pattern, $_SERVER['REQUEST_URI'], $matches) != false) {
    $view = $callback[0];
    $function = $callback[1];
    $module = $view;
    $dict['page'] = $view;
    App::includeView('views/'.$view.'.php', false);
    exit;
  }
}

// If no URL patterns are matched, return the 404...
header('HTTP/1.1 404 Not Found');
include_once 'views/404.php';