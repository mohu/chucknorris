<?php
  class View_Usergroup {

    function admin() {
      global $dict;
      ## Include model
      App::includeModel('models/usergroup.php', 'usergroup', true);
      $model = App::initAdminModel('usergroup');

      include_once 'common.php';
    }

  }