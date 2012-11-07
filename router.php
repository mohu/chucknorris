<?php

foreach($valid_paths as $pattern => $callback) {
  if (preg_match('{'.$pattern.'}', $_SERVER['REQUEST_URI'], $request) != false) {
    ## Set global dictionary values
    $dict['view'] = $view = $callback[0];
    $dict['function'] = $function = $callback[1];
    $dict['request'] = $request;

    ## Include view
    $app->includeView('views/' . $view . '.php', $function);

    ## Instantiate
    $app->initView($view, $function); exit;
  }
}

// If no URL patterns are matched, return the 404...
header('HTTP/1.1 404 Not Found');
include_once('views/404.php');