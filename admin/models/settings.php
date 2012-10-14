<?php

class Model_Settings extends RedBean_SimpleModel {

  function fields() {
    // Add fields here
    $fields['pagination'] = array('type'=>'select', 'label'=>'pagination', 'values'=>array('5 items'=>5, '10 items'=>10, '15 items'=>15, '20 items'=>20, '25 items'=>25, '30 items'=>30, '50 items'=>50, '100 items'=>100, 'All items'=>0), 'help'=>'The number of items to show in the administration data tables');
    
    $fields['sitename']   = array('type'=>'text', 'label'=>'Site name', 'help'=>'');
    $fields['analytics']  = array('type'=>'text', 'label'=>'Google analytics ID', 'help'=>'Enter your Google analytics tracking code in the format of <strong>UA-XXXXX-X</strong>');
    $fields['debug']      = array('type'=>'radio', 'label'=>'Enable debugger', 'help'=>'', 'values'=>array('on'=>1, 'off'=>0));

    $fields['twitter']    = array('type'=>'text', 'label'=>'Twitter username', 'help'=>'');
    $fields['facebook']   = array('type'=>'text', 'label'=>'Facebook URL', 'help'=>'');
    $fields['linkedin']   = array('type'=>'text', 'label'=>'Linkedin URL', 'help'=>'');
    $fields['pinterest']  = array('type'=>'text', 'label'=>'Pinterest URL', 'help'=>'');

    $fields['contact']    = array('type'=>'text', 'label'=>'Contact us email', 'help'=>'');
    
    return $fields;
  }

  function globalSettings() {
    return App::globalSettings();
  }
}