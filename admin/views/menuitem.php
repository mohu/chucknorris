<?php
class View_Menuitem {

  function admin() {
    global $dict;
    ## Include model
    App::includeModel('models/menuitem.php', 'menuitem', true);
    $model = App::initAdminModel('menuitem');

    include_once 'common.php';
  }

}