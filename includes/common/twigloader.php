<?php
if (defined('ADMIN') && !defined('FRONTEND')) {
	$path = LOCAL_PATH . 'admin/templates';
} else {
	$path = 'templates';
}
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem($path);
$twig = new Twig_Environment($loader, array(
  'cache' => 'cache',
  'auto_reload' => true,
  'debug' => true,
));

$twig->addExtension(new Twig_Extension_Debug());