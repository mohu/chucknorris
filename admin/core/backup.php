<?php
require_once 'app.php';
$app = new App();

// Run backup function
echo $app->backupDatabase();