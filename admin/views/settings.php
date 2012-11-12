<?php
class View_Settings {

  function admin() {
    global $dict;
    ## Include model
    App::includeModel('models/settings.php', 'settings', true);
    ## Initialise model
    $model = App::initAdminModel('settings');
    ## Initialise custom model function
    $dict['settings'] = $model->globalSettings();

    App::renderTwig('settings.twig', $dict);
  }

}