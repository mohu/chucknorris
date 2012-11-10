<?php

class Model_Menuitem extends RedBean_SimpleModel {

  function fields() {
    // Add fields here
    $fields['title']      = array('type'=>'text', 'label'=>'title', 'max_length'=>'255', 'help'=>'');
    $fields['link']       = array('type'=>'text', 'label'=>'link', 'max_length'=>'255', 'help'=>'', 'readonly' => true);
    $fields['class']      = array('type'=>'text', 'label'=>'class', 'max_length'=>'255', 'help'=>'Menu item class (to fire modals etc)', 'readonly' => true);
    $fields['ordering']    = array('type'=>'order', 'label'=>'ordering', 'required'=>true);
    $fields['published']  = array('type'=>'radio', 'label'=>'publish?', 'max_length'=>'255', 'help'=>'', 'values'=>array('yes'=>1, 'no'=>0));

    return $fields;
  }

  function settings() {
    // Settings
    $settings['add']        = false;
    $settings['edit']       = true;
    $settings['delete']     = true;

    $settings['orderby']    = 'ordering';
    $settings['order']      = 'asc';

    return $settings;
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