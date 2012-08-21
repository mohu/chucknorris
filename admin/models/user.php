<?php
class Model_User extends RedBean_SimpleModel {

  function fields() {
    // Add fields here
    $fields['username']   = array('type'=>'text', 'label'=>'username', 'help'=>'', 'required'=>true, 'table_hide'=>true);
    $fields['firstname']  = array('type'=>'text', 'label'=>'first name', 'help'=>'', 'required'=>true);
    $fields['lastname']   = array('type'=>'text', 'label'=>'last name', 'help'=>'', 'required'=>true);
    $fields['position']   = array('type'=>'text', 'label'=>'position', 'help'=>'');
    $fields['email']      = array('type'=>'text', 'label'=>'email', 'help'=>'', 'required'=>true);
    $fields['password']   = array('type'=>'text', 'label'=>'password', 'help'=>'', 'table_hide'=>true, 'required'=>true);
    $fields['biography']  = array('type'=>'textarea', 'label'=>'biography', 'max_length'=>'255', 'help'=>'', 'table_hide'=>true, 'rich_editor'=>true, 'readonly'=>true);
    $fields['image']      = array('type'=>'file', 'label'=>'image', 'path'=>'img', 'help'=>'Profile image 200 x 200 px', 'table_hide'=>false);
    $fields['twitter']    = array('type'=>'text', 'label'=>'Twitter account', 'help'=>'Enter the user\'s Twitter <strong>user name</strong> (without the @ symbol)', 'table_hide'=>true);
    $fields['linkedin']   = array('type'=>'text', 'label'=>'Linkedin account', 'help'=>'Enter the user\'s Linkedin <strong>Public Profile URL</strong> (including http://)', 'table_hide'=>true);
    $fields['group']      = array('type'=>'select', 'label'=>'group', 'help'=>'', 'values'=>array('Super administrator'=>'superadmin', 'Public member'=>'member'), 'required'=>true);

    // Settings
    $fields['add']        = true;
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

  function after_update() {
    if (!$this->salt) {
      // Create unique salt if none exists
      $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    }

    // Update password with salt
    $sha1 = (bool) preg_match('/^[0-9a-f]{40}$/i', $this->password);
    if (!$sha1) {
      $user = R::load('user', $this->id);
      $user->password = sha1($this->password . $this->salt);
      R::store($user);
    }
  }
}