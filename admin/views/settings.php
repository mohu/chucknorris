<?php
class View_Settings {

  function admin() {
    global $dict;
    ## Include model
    App::includeModel('models/settings.php', 'settings', true);
    $model = App::initAdminModel('settings');

    $dict['settings'] = $model->globalSettings();

    App::renderTwig('settings.twig', $dict);
  }

}