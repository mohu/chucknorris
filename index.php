<?php
require_once 'includes/redbean/rb.php';
require_once 'includes/common/dbconnector.php';
require_once 'includes/common/functions.php';
require_once 'includes/Twig/Autoloader.php';
require_once 'includes/common/twigloader.php';

$valid_paths = array(
  // Home page
  '{^/$}' => array('home', 'index'),
  // Examples
  '{^/about$}' => array('about', 'index'),
  '{^/blog/(?<page>\d+)/(?<slug>[\w-]+)$}' => array('blog', 'index'),
  '{^/blog/(?<slug>[\w-]+)$}' => array('blog', 'post'),
);

$dict = array();

## Get common dictionaries
require_once 'models/common.php';
$model  = new Model_Common();
$dict['common'] = $model->common();
$dict['uri']  = "http://" . $_SERVER['HTTP_HOST'];

## Router
include_once 'router.php';