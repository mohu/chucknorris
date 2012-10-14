<?php
$id     = (isset($_GET['id'])) ? $_GET['id'] : null;
$action = (isset($_GET['action'])) ? $_GET['action'] : null;

/**
* Add view
*/
if ($action == 'add') {

  $dict['fields']   = $model->add();
  $dict['groups']   = App::getGroups();
  App::renderTwig('module-add.twig', $dict);
 
/**
* Edit view
*/
} elseif ($action == 'edit' && $id > 0) {

  $dict['fields']       = $model->edit($id);
  $dict['o2mstructure'] = App::buildEditformownfields($model->fields());
  $dict['settings']     = $model->settings();
  $dict['groups']       = App::getGroups();
  App::renderTwig('module-edit.twig', $dict);

/**
* Delete view
*/
} elseif ($action == 'delete' && $id) {

  $dict['result']     = $model->trash($id);
  $dict['data']       = $model->view();
  $dict['pagination'] = $model->count();
  $dict['settings']   = $model->settings();
  App::renderTwig('module.twig', $dict);

/**
* List view
*/
} else {

  $dict['data']       = $model->view();
  $dict['pagination'] = $model->count();
  $dict['settings']   = $model->settings();
  App::renderTwig('module.twig', $dict);

}

//echo '<pre>' . print_r($dict, true) . '</pre>';