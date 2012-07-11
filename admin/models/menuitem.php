<?php

class Model_Menuitem extends RedBean_SimpleModel {

  function fields() {
    // Add fields here
    $fields['title']      = array('type'=>'text', 'label'=>'title', 'max_length'=>'255', 'help'=>'');
    $fields['link']       = array('type'=>'text', 'label'=>'link', 'max_length'=>'255', 'help'=>'');
    $fields['class']      = array('type'=>'text', 'label'=>'class', 'max_length'=>'255', 'help'=>'Menu item class (to fire modals etc)');
    $fields['published']  = array('type'=>'radio', 'label'=>'publish?', 'max_length'=>'255', 'help'=>'', 'values'=>array('yes'=>1, 'no'=>0));

    //$fields['menu']        = array('type'=>'foreignkey', 'label'=>'users', 'relation'=>'shared', 'selecttitle'=>'name', 'model'=>'menu', 'class'=>'Model_Menu');

    // Settings
    $fields['add']        = false;
    $fields['edit']       = true;
    $fields['delete']     = true;
    return $fields;
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