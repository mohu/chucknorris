<?php
if (defined('ADMIN')) {
	$path = LOCAL_PATH . 'admin/templates';
} else {
	$path = 'templates';
}
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem($path);
$twig = new Twig_Environment($loader, array(
  'cache' => 'cache',
  'auto_reload' => true,
));