<?php

class Model_Usergroup extends RedBean_SimpleModel {

	function fields() {
		// Add fields here
		$fields['title']       = array('type'=>'text', 'label'=>'title', 'help'=>'', 'readonly'=>true);
		$fields['group']       = array('type'=>'text', 'label'=>'group', 'help'=>'', 'readonly'=>true);
		$fields['area']        = array('type'=>'radio', 'label'=>'access area', 'help'=>'', 'values'=>array('Frontend'=>'frontend', 'Backend'=>'backend'), 'readonly'=>true);

		$fields['paths']       = array('type'=>'foreignkey', 'relation'=>'shared', 'model'=>'access', 'selecttitle'=>'%path%', 'label'=>'paths', 'help'=>'<strong>Super administrators have full access to all areas by default</strong><br /><br />');

		return $fields;
	}

	function settings() {
   // Settings
   $settings['add']        = false;
   $settings['edit']       = true;
   $settings['delete']     = false;

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