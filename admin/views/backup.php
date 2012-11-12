<?php
class View_Backup {

  function admin() {
    global $twig, $dict;
    ## Include model
    App::includeModel('models/backup.php', 'backup', true);
    ## Initialise model
    $model = App::initAdminModel('backup');
    ## Initialise custom model function - view/delete

    $id     = (isset($_GET['id'])) ? $_GET['id'] : null;
    $action = (isset($_GET['action'])) ? $_GET['action'] : null;

    /**
     * Delete view
     */
    if ($action == 'delete' && $id) {

      $dict['result']   = $model->trash($id);
      $dict['data']     = $model->backup();
      $dict['settings'] = $model->settings();
      echo $twig->render( 'backup.twig', $dict);

      /**
       * List view
       */
    } else {

      $dict['data']       = $model->backup();
      $dict['pagination'] = $model->count();
      $dict['settings']   = $model->settings();
      echo $twig->render( 'backup.twig', $dict);

    }
  }

}