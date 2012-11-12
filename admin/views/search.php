<?php
class View_Search {

  function admin() {
    global $dict;
    ## Include model
    App::includeModel('models/search.php', 'search', true);
    ## Initialise model
    $model = App::initAdminModel('search');
    ## Initialise custom model function
    $dict['search'] = $model->globalSearch();

    App::renderTwig('search.twig', $dict);
  }

}