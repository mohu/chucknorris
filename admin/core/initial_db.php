<?php
## Data for the initial database installation

$database['allowedips'] = array(
  'type' => 'allowedips',
  'ip' => '127.0.0.1',
  'reference' => 'Local host'
);

$database['menu'] = array(
  'type' => 'menu',
  'name' => 'Main menu',
  'ownMenuitem' => array(
    0 => array(
      'type' => 'menuitem',
      'ordering' => 1,
      'title' => 'Home',
      'link' => '#',
      'class' => NULL,
      'published' => 1
    )
  )
);

$database['settings'] = array(
  'type' => 'settings',
  'pagination' => 10,
  'twitter' => NULL,
  'facebook' => NULL,
  'linkedin' => NULL,
  'pinterest' => NULL,
  'contact' => NULL,
  'sitename' => NULL,
  'analytics' => NULL,
  'debug' => 0
);

$database['usergroup'] = array(
  'type' => 'usergroup',
  'title' => 'Administrator',
  'group' => 'admin',
  'area' => 'backend',
  'paths' => NULL
);

$database['usergroup2'] = array(
  'type' => 'usergroup',
  'title' => 'Super administrator',
  'group' => 'superadmin',
  'area' => 'backend',
  'paths' => NULL
);

$database['usergroup3'] = array(
  'type' => 'usergroup',
  'title' => 'Public member',
  'group' => 'superadmin',
  'area' => 'frontend',
  'paths' => NULL
);

$database['user'] = array(
  'type' => 'user',
  'username' => NULL,
  'name' => NULL,
  'email' => NULL,
  'password' => NULL,
  'salt' => NULL,
  'signupdate' => NULL,
  'sharedUsergroup' => array(
    0 => array(
      'type' => 'usergroup',
      'id' => 1
    )
  )
);