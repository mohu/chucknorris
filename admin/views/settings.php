<?php
require_once 'models/settings.php';
$model  = new Model_Settings();

$dict['settings'] = $model->globalSettings();

App::renderTwig('settings.twig', $dict);