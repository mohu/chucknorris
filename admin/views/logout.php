<?php
unset($_SESSION);
session_destroy();
unset($_POST);
header( "Location: /admin/index.php" );