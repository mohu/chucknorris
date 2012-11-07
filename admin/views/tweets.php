<?php
class View_Tweets {

  function admin() {
    global $dict;
    ## Include model
    App::includeModel('models/tweets.php', 'tweets', true);
    $model = App::initAdminModel('tweets');

    include_once 'common.php';
  }

}