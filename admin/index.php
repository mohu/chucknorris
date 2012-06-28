<?php
require_once 'core/app.php';
$app = new App();

$valid_paths  = array( // Core
                       'allowedips',
                       'backup',
                       'login',
                       'logout',
                       'menu',
                       'menuitem',
                       'settings',
                       'tweets',
                       'users',
                       // Custom
                      );

$dict            = array();
$dict['session'] = $app->loadSession();
$dict['menu']    = $app->loadMenu(array('Menus'               => array( 'menu' => 'Menu', 'menuitem' => 'Menu item', ),
                                        'Twitter'             => array( 'tweets' => 'Tweets', ),
                                        'Users'               => array( 'users' => 'Users', ),
                                        ));

## Router
include_once 'core/router.php';