<?php
require_once 'core/app.php';
$app = new App();

$valid_paths  = array( // Core
                       'access',
                       'allowedips',
                       'backup',
                       'login',
                       'logout',
                       'menu',
                       'menuitem',
                       'search',
                       'settings',
                       'system-info',
                       'tweets',
                       'user',
                       'usergroup',
                       // Custom
                      );

$dict            = $app->beginDict();
$dict['menu']    = $app->loadMenu(array('Menus'               => array( 'menu' => 'Menu', 'menuitem' => 'Menu item', ),
                                        'Twitter'             => array( 'tweets' => 'Tweets', ),
                                        'Users'               => array( 'user' => 'Users',
                                                                        'usergroup' => 'User groups',
                                                                        'access' => 'Valid paths', ),
                                        ));

## Router
include_once 'core/router.php';