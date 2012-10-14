<?php

class Model_Search extends RedBean_SimpleModel {

	function fields() {
		// Add fields here
		$fields['title']       = array('type'=>'text', 'label'=>'title', 'help'=>'This is optional help text');

		return $fields;
	}

  function settings() {
    // Settings
    $settings['add']        = true;
    $settings['edit']       = true;
    $settings['delete']     = true;

    $dict = App::getSettings($settings);
    return $dict;
  }

  function globalSearch() {
    return App::globalSearch();
  }
}