<?php
class View_Systeminfo {

  function admin() {
    global $twig, $dict;
    ## Include model
    App::includeModel('models/systeminfo.php', 'systeminfo', true);
    $model = App::initAdminModel('systeminfo');

    $dict['systeminfo'] = $model->sysinfo();

    echo $twig->render( 'systeminfo.twig', $dict);
  }

}