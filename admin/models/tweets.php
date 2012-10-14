<?php
class Model_Tweets extends RedBean_SimpleModel {

  function fields() {
    // Add fields here
    $fields['tid']        = array('type'=>'text', 'label'=>'tid', 'max_length'=>'255', 'help'=>'', 'readonly'=>true);
    $fields['screenname'] = array('type'=>'text', 'label'=>'screen name', 'max_length'=>'255', 'help'=>'', 'readonly'=>true);
    $fields['time']       = array('type'=>'text', 'label'=>'time stamp', 'max_length'=>'255', 'help'=>'', 'readonly'=>true);
    $fields['text']       = array('type'=>'text', 'label'=>'tweet', 'max_length'=>'255', 'help'=>'', 'readonly'=>true);
    $fields['published']  = array('type'=>'radio', 'label'=>'publish?', 'max_length'=>'255', 'help'=>'', 'values'=>array('yes'=>1, 'no'=>0));

    return $fields;
  }

  function settings() {
    // Settings
    $settings['add']        = false;
    $settings['edit']       = true;
    $settings['delete']     = true;

    $settings['orderby']    = 'id';
    $settings['order']      = 'desc';

    // Cron
    $settings['run']        = array('path'=>'/cron/twitter-cron.php', 'button'=>'Fetch', 'button_running'=>'Fetching...');

    $dict = App::getSettings($settings);
    return $dict;
  }

  function view() {
    global $module;
    $dict = App::view($module, __CLASS__); // Region optional
    return $dict;
  }

  function count() {
    global $module;
    $dict = App::count($module); // Region optional
    return $dict;
  }

  function add() {
    return App::buildForm($this->fields());
  }

  function edit($id) {
    global $module;
    sanitize($id);
    return App::buildEditform($this->fields(), $module, $id);
  }

  function trash($id) {
    global $module;
    sanitize($id);
    return App::trash($id, $module);
  }
}