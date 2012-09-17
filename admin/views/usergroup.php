<?php
App::requireModel('models/' . $module . '.php', true);
$model  = new Model_Usergroup();

include_once 'common.php';