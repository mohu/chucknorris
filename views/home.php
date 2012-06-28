<?php
require_once 'models/' . $module . '.php';
$model  = new Model_Home();

$dict[$module] = $model->home();

echo $twig->render('home.html', $dict);

// echo '<pre style="color:#fff;">' . print_r($dict, true) . '</pre>';