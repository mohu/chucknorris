<?php
  class View_Usergroup {

    function admin() {
      global $dict;
      ## Include model
      App::includeModel('models/usergroup.php', 'usergroup', true);
      ## Initialise model
      $model = App::initAdminModel('usergroup');
      ## Initialise default model function - view/add/edit/delete
      App::initAdminCommon($model);
    }

  }