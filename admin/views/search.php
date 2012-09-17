<?php
App::requireModel('models/' . $module . '.php', true);
$model  = new Model_Search();

$dict['search'] = $model->globalSearch();

App::renderTwig('search.twig', $dict);

//echo '<pre>' . print_r($dict, true) . '</pre>';