<?php
class View_Allowedips {

  function admin() {
    global $dict;
    ## Include model
    App::includeModel('models/allowedips.php', 'allowedips', true);
    $model = App::initAdminModel('allowedips');

    include_once 'common.php';
  }

}