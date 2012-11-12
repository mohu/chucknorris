<?php
class View_Tweets {

  function admin() {
    global $dict;
    ## Include model
    App::includeModel('models/tweets.php', 'tweets', true);
    ## Initialise model
    $model = App::initAdminModel('tweets');

    ## Initialise default model function - view/add/edit/delete
    App::initAdminCommon($model);
  }

}