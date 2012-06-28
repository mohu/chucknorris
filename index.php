<?php
require_once 'includes/redbean/rb.php';
require_once 'includes/common/dbconnector.php';
require_once 'includes/common/functions.php';
require_once 'includes/Twig/Autoloader.php';
require_once 'includes/common/twigloader.php';
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

$valid_paths  = array('home',
                      // Add more urls below e.g.
                      // 'blog',
                      );

$dict = array();

## Get common dictionaries
require_once 'models/common.php';
$model  = new Model_Common();
$dict['common'] = $model->common();
$dict['uri']  = "http://" . $_SERVER['HTTP_HOST'];

## Router
include_once 'router.php';