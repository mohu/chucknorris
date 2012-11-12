<?php
class View_Menuitem {

  function admin() {
    global $dict;
    ## Include model
    App::includeModel('models/menuitem.php', 'menuitem', true);
    ## Initialise model
    $model = App::initAdminModel('menuitem');
    ## Initialise default model function - view/add/edit/delete
    App::initAdminCommon($model);
  }

}