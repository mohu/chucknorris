<?php

class Model_Backup extends RedBean_SimpleModel {

  function fields() {
    // Add fields here
    $fields['file']      = array('type'=>'text', 'label'=>'file', 'max_length'=>'255', 'help'=>'');
    $fields['date']      = array('type'=>'text', 'label'=>'date', 'max_length'=>'255', 'help'=>'');
    
    // Settings
    $fields['add']        = false;
    $fields['edit']       = false;
    $fields['delete']     = true;

    // Cron
    $fields['run']        = array('path'=>'/admin/core/backup.php', 'button'=>'Run backup', 'button_running'=>'Backing up...');

    return $fields;
  }

  function backup() {
    $start = ($_GET['start']) ? (int)$_GET['start'] : 0;
    $limit = R::getCell('SELECT pagination FROM settings LIMIT 1');

    $limit = ($limit) ? (int)$limit : 999999;

    $data = R::getAll( 'SELECT *
                        FROM backup
                        LIMIT ' . $start . ', ' . $limit );

    return $data;
  }

  function settings() {
    $dict = App::getSettings($this->fields());
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