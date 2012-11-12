<?php
class Model_User extends RedBean_SimpleModel {

  function fields() {
    // Add fields here
//    $fields['username']   = array('type'=>'text', 'label'=>'username', 'help'=>'', 'required'=>true, 'table_hide'=>true);
//    $fields['name']       = array('type'=>'text', 'label'=>'name', 'help'=>'', 'required'=>true);
//    $fields['email']      = array('type'=>'text', 'label'=>'email', 'help'=>'', 'required'=>true, 'validate'=>'email');
//    $fields['password']   = array('type'=>'text', 'label'=>'password', 'help'=>'', 'table_hide'=>true, 'required'=>true);
//    $fields['salt']       = array('type'=>'text', 'label'=>'salt', 'help'=>'', 'table_hide'=>true, 'readonly'=>true, 'onload'=>'hide');
//    $fields['signupdate']    = array('type'=>'text', 'label'=>'signup date', 'help'=>'', 'readonly'=>true);
//
//    $fields['group']   = array('type'=>'foreignkey', 'relation'=>'shared', 'model'=>'usergroup', 'one'=>true, 'selecttitle'=>'%title% (%area%)', 'label'=>'group', 'help'=>'', 'required'=>true);

    return $fields;
  }

  function settings() {
    // Settings
    $settings['add']        = true;
    $settings['edit']       = true;
    $settings['delete']     = true;

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

  function update() {
    if ($this->id == 0) {
      // Get site details
      $fromemail = R::getCell("SELECT contact FROM settings");
      $fromname  = R::getCell("SELECT sitename FROM settings");

      // PHP mail - notifies user when added
      App::twigEmail($this->email, $this->name, $fromemail, $fromname, 'user-added', array(
        'name'        => $this->name,
        'username'    => $this->username,
        'email'       => $this->email,
        'url'         => "http://" . $_SERVER['HTTP_HOST'],
        'password'    => $this->password,
      ));
    }

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

    if (!$this->signupdate) {
      // Add signup date
      $user = R::load('user', $this->id);
      $user->signupdate = date("Y-m-d H:i:s");
      R::store($user);
    }
  }
}