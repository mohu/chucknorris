<?php
// Name array variables
function name($dict, $name = 'data') {
	$dict[$name] = $dict[0];
	unset($dict[0]);
	return $dict;
}

function linkify($link) {
  return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $link);
}

function sanitize($input) {
  $output = null;
  if (is_array($input)) {
    foreach($input as $var=>$val) {
      $output[$var] = sanitize($val);
    }
  } else {
    if (get_magic_quotes_gpc()) {
      $input = stripslashes($input);
    }
    $output  = cleanInput($input);
    //$output = str_replace("\r\n",'',$input);
  }
  return $output;
}

function cleanInput($input) {
  $search = array(
    '@<script[^>]*?>.*?</script>@si',   // Strip out javascript
                                        // Strip out HTML tags  '@<[\/\!]*?[^<>]*? >@si',
    '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
    '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
  );

    $output = preg_replace($search, '', $input);
    return $output;
 }

 function array_remove_empty2($haystack) {
    foreach ($haystack as $key => $value) {
      if (is_array($value)) {
        $haystack[$key] = array_remove_empty2($haystack[$key]);
      }
      
      $total = count($haystack);

      
      echo '<pre>' . print_r($haystack, true) . '</pre>';
      $count = 0;
      if (isset($haystack[$key])) {
        $count++;
        echo $haystack[$key];
      }
      echo $total . ' - ' . $count . '<br>';
      if ($total - $count == 2) { // This checks if only bean type and region are set
        unset($haystack['region']);
      }
    }
    return $haystack;
}

function array_remove_empty($input) {

    if (!is_array($input)) {
      return $input;
    }

    $non_empty_items = array();
    
    foreach ($input as $key => $value) {
      $count = count(array_filter($input)); // Counts none empty
      if ($count == 2) {
        unset($input['region']);
      }

      if($value) {
        // Use recursion to evaluate cells 
        $non_empty_items[$key] = array_remove_empty($input[$key]);
      } else {
        $non_empty_items[$key] = $input[$key];
      }

    }
    return $non_empty_items;
}

function ago($time) {
   $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
   $lengths = array("60","60","24","7","4.35","12","10");

   $now = time();

       $difference     = $now - $time;
       $tense         = "ago";

   for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
       $difference /= $lengths[$j];
   }

   $difference = round($difference);

   if($difference != 1) {
       $periods[$j].= "s";
   }

   return "$difference $periods[$j] ago ";
}

function truncate($text, $length, $suffix = '&hellip;', $isHTML = true){
  $i = 0;
  $tags = array();
  if($isHTML){
      preg_match_all('/<[^>]+>([^<]*)/', $text, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
      foreach($m as $o){
          if($o[0][1] - $i >= $length)
              break;
          $t = substr(strtok($o[0][0], " \t\n\r\0\x0B>"), 1);
          if($t[0] != '/')
              $tags[] = $t;
          elseif(end($tags) == substr($t, 1))
              array_pop($tags);
          $i += $o[1][1] - $o[0][1];
      }
  }
  
  $output = substr($text, 0, $length = min(strlen($text),  $length + $i)) . (count($tags = array_reverse($tags)) ? '</' . implode('></', $tags) . '>' : '');
  
  // Get everything until last space
  $one = substr($output, 0, strrpos($output, " "));
  // Get the rest
  $two = substr($output, strrpos($output, " "), (strlen($output) - strrpos($output, " ")));
  // Extract all tags from the last bit
  preg_match_all('/<(.*?)>/s', $two, $tags);
  // Add suffix if needed
  if (strlen($text) > $length) { $one .= $suffix; }
  // Re-attach tags
  $output = $one . implode($tags[0]);
  
  return $output;
}

function getFilesize($file,$digits = 2) {
  if (is_file($file)) {
    $filePath = $file;
    if (!realpath($filePath)) {
      $filePath = $_SERVER["DOCUMENT_ROOT"].$filePath;
  }
  $fileSize = filesize($filePath);
    $sizes = array("TB","GB","MB","KB","B");
    $total = count($sizes);
    while ($total-- && $fileSize > 1024) {
      $fileSize /= 1024;
    }
    return round($fileSize, $digits)." ".$sizes[$total];
 }
 return false;
}