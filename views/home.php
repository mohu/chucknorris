<?php
App::requireModel('models/' . $module . '.php', false);
$model  = new Model_Home();

$dict[$module] = $model->home();

echo $twig->render('home.twig', $dict);

// echo '<pre style="color:#fff;">' . print_r($dict, true) . '</pre>';