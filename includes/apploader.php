<?php
## Define frontend
define ('FRONTEND', 1 );

## Initialise core app
require_once realpath(dirname(__FILE__).'/..') . '/' . 'admin/core/app.php';
$app = new App();