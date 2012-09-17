<?php
App::requireModel('models/' . $module . '.php', true);
$model  = new Model_Access();

include_once 'common.php';