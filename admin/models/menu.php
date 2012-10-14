<?php

class Model_Menu extends RedBean_SimpleModel {

  function fields() {
    // Add fields here
    $fields['name']       = array('type'=>'text', 'label'=>'name', 'max_length'=>'255', 'help'=>'This is optional help text');
    $fields['menuitem']   = array('type'=>'foreignkey', 'label'=>'menu item', 'relation'=>'own', 'model'=>'menuitem', 'class'=>'Model_Menuitem');

    return $fields;
  }

  function settings() {
    // Settings
    $settings['add']        = true;
    $settings['edit']       = true;
    $settings['delete']     = false;

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