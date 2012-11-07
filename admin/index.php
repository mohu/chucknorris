<?php
require_once 'core/app.php';
$app = new App();

## Get URL patterns
require_once 'core/urls.php';

$dict            = $app->beginDict();
$dict['menu']    = $app->loadMenu(array('Menus'               => array( 'menu' => 'Menu', 'menuitem' => 'Menu item', ),
                                        'Twitter'             => array( 'tweets' => 'Tweets', ),
                                        'Users'               => array( 'user' => 'Users',
                                                                        'usergroup' => 'User groups',
                                                                        'access' => 'Valid paths', ),
                                        ));

## Router
include_once 'core/router.php';