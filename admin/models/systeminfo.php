<?php

class Model_Systeminfo extends RedBean_SimpleModel {

  function fields() {
  }

  function sysinfo() {
    ob_start();
    phpinfo();
    $phpinfo = array('phpinfo' => array());

    if(preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER))
      foreach($matches as $match) {
        if(strlen($match[1])) {
          $phpinfo[$match[1]] = array();
        } elseif(isset($match[3])) {
          $keys = array_keys($phpinfo);
          $phpinfo[end($keys)][$match[2]] = isset($match[4]) ? array($match[3], $match[4]) : $match[3];
        } else {
        $keys = array_keys($phpinfo);
        $phpinfo[end($keys)][] = $match[2];
        }
      }

      $template = '<div class="tabbable tabs-right">';
      $template .= '<ul class="nav nav-tabs" id="systeminfo">';
      $i = 0;
      foreach($phpinfo as $name => $section) {
        if ($i == 0) {
          $active = 'active';
        } else {
          $active = '';
        }
        $template .= '<li class="'. $active .'"><a href="#'. strtolower(str_replace(" ", "", $name)) .'">'. ucfirst($name) .'</a></li>';
        $i++;
      }
      $template .= '</ul>';

      $template .= '<div class="tab-content">';
      $i = 0;
      foreach($phpinfo as $name => $section) {
        if ($i == 0) {
          $active = ' active';
        } else {
          $active = '';
        }

        $template .= '<div class="tab-pane'. $active .'" id="'. strtolower(str_replace(" ", "", $name)) .'">';
        $template .= '<blockquote><br /><p>'. $name .'</p><br /></blockquote>';
        $template .= '<table class="table">';
        foreach($section as $key => $val) {
          if(is_array($val)) {
            $template .= '<tr><td>'. $key . '</td><td>'. wordwrap($val[0], 20, "<br />", true) .'</td><td>'. wordwrap($val[1], 20, "<br />", true) .'</td></tr>';
          } elseif(is_string($key)) {
            $template .= '<tr><td>'. $key .'</td><td>'. wordwrap($val, 20, "<br />") .'</td><td></td></tr>';
          } else {
            $template .= '<tr><td>'. $val .'</td><td></td><td></td></tr>';
          }
        }
        $template .= '</table>';
        $template .= '</div>';
        $i++;
      }
      $template .= '</div>';
      $template .= '</div>';
    return $template;
  }
}