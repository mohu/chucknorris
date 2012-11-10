<?php

class Model_Search extends RedBean_SimpleModel {

	function fields() {
		// Add fields here
		$fields['title']       = array('type'=>'text', 'label'=>'title', 'help'=>'This is optional help text');

		return $fields;
	}

  function globalSearch() {
    return App::globalSearch();
  }
}