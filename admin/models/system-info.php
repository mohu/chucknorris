<?php
App::requireModel('models/' . $module . '.php', true);
$model  = new Model_Systeminfo();

$dict['systeminfo'] = $model->sysinfo();

App::renderTwig('system-info.html', $dict);