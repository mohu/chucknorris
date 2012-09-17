<?php
require_once 'models/' . $module . '.php';
$model  = new Model_Home();

$dict[$module] = $model->home();

echo $twig->render('home.twig', $dict);

//echo '<pre style="color:#fff; clear:both;">' . print_r($dict, true) . '</pre>';