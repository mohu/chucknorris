<?php

class Model_Search extends RedBean_SimpleModel {

	function fields() {
		// Add fields here
		$fields['title']       = array('type'=>'text', 'label'=>'title', 'help'=>'This is optional help text');

		// Settings
		$fields['add']        = true;
		$fields['edit']       = true;
		$fields['delete']     = true;
		return $fields;
	}

  function globalSearch() {
    return App::globalSearch();
  }
}