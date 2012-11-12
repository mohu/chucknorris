<?php
class View_Systeminfo {

  function admin() {
    global $twig, $dict;
    ## Include model
    App::includeModel('models/systeminfo.php', 'systeminfo', true);
    ## Initialise model
    $model = App::initAdminModel('systeminfo');
    ## Initialise custom model function
    $dict['systeminfo'] = $model->sysinfo();

    echo $twig->render( 'systeminfo.twig', $dict);
  }

}