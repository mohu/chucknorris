<?php
class View_Allowedips {

  function admin() {
    global $dict;
    ## Include model
    App::includeModel('models/allowedips.php', 'allowedips', true);
    ## Initialise model
    $model = App::initAdminModel('allowedips');
    ## Initialise default model function - view/add/edit/delete
    App::initAdminCommon($model);
  }

}