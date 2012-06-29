<?php
App::requireModel('models/' . $module . '.php', true);
$model  = new Model_Menu();

include_once 'common.php';