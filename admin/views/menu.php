<?php
class View_Menu {

  function admin() {
    global $dict;
    ## Include model
    App::includeModel('models/menu.php', 'menu', true);
    $model = App::initAdminModel('menu');

    include_once 'common.php';
  }

}