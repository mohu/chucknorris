<?php
class View_Access {

  function admin() {
    global $dict;
    ## Include model
    App::includeModel('models/access.php', 'access', true);
    $model = App::initAdminModel('access');

    include_once 'common.php';
  }

}