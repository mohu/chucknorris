<?php
// error_reporting(0);
define ('ADMIN', 1 );
define ('LOCAL_PATH', realpath(dirname(__FILE__).'/../..') . '/' );
require_once LOCAL_PATH. 'includes/redbean/rb.php';
require_once LOCAL_PATH. 'includes/Twig/Autoloader.php';
require_once LOCAL_PATH. 'includes/common/dbconnector.php';
require_once LOCAL_PATH. 'includes/common/twigloader.php';
require_once LOCAL_PATH. 'includes/common/functions.php';

$GLOBALS['bufferederrors'] = array();

class App {

  /**
   * Constructor, runs core functions and listens to save/update calls
   */
  function __construct() {

    $this->coreFunctions(); // Run core functions
    
    if (isset($_POST['save'])) { 
      $this->save($_POST);
    }

    if (isset($_POST['update']) || isset($_POST['apply'])) {
      $this->update($_POST);
    }

    if (isset($_POST['clearcache'])) {
      $this->clearCache();
    }
  }

  /**
   * Runs core functions
   * @return mixed
   */
  public function coreFunctions() {
    $this->checkIP();
    $this->parseErrors();
    $this->log();
    return;
  }

  /**
   * Loads the start of the dictionary of data for the admin area
   * @static
   * @return array
   */
  public function beginDict() {
    $dict = array();
    $dict['session']  = App::loadSession();
    $dict['cms']      = array('name'=>'Chuck Norris',
                              'version'=>'0.5.0',
                              'dependancies'=>array('RedBeanPHP'=>'3.2.3',
                                                    'Twig'=>'1.8.3',
                                                    'Bootstrap, from Twitter'=>'2.0.4',
                                                    'Glyphicons'=>'1.6')
                              );

    $sitename         = R::getCell('SELECT sitename FROM settings');
    $dict['sitename'] = ($sitename) ? $sitename : $dict['cms']['name'];
    $dict['appletouchicon']  = (is_array(@getimagesize(LOCAL_PATH . 'apple-touch-icon-57x57-precomposed.png'))) ? true : false;
    $dict['cache']['status'] = $this->getCache();

    return $dict;
  }

  /**
   * Checks remote client IP address and compares to database white list
   */
  public function checkIP() {
    global $twig;
    if (!defined('FRONTEND')) {
      $allowed_ips = R::getAll( 'SELECT * FROM allowedips' );

      $allowed = false;

      if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] != '')
        $ip = $_SERVER['HTTP_CLIENT_IP'];
      elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '')
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != '')
        $ip = $_SERVER['REMOTE_ADDR'];
      if (($commapos = strpos($ip, ',')) > 0)
        $ip = substr($Ip, 0, ($commapos - 1));

      foreach ($allowed_ips as $allowedip) {
        if ($allowedip["ip"]) {
          if (substr_count($ip, trim($allowedip["ip"])) != "0") {
            $allowed = true;
          }
        }
      }
      if ($allowed != true) {
        // The banned display page
        header('HTTP/1.1 401 Unauthorized');
        include_once realpath(dirname(__FILE__).'/../..'). '/admin/views/forbidden.php';
      }
    }
  }

  /**
   * Displays global settings page
   * @static
   * @return array
   */
  public static function globalSettings() {
    require_once realpath(dirname(__FILE__).'/..'). '/models/settings.php';
    $class = new Model_Settings;

    return App::buildEditForm($class->fields(), 'settings', 1);
  }

  /**
   * Check $_SESSION exists
   * @return null
   */
  public function checkSession() {
    App::loadSession();
    $session = isset($_SESSION['user']) ? $_SESSION['user'] : null;

    return $session;
  }

  /**
   * Load $_SESSION
   * @return array
   */
  public static function loadSession() {
    $s = session_id();
    if(empty($s)) session_start();
    return $_SESSION;
  }

  /**
   * Loads cPanel side navigation menu array
   * @param $items
   *
   * @return array
   */
  public function loadMenu($items) {
    $dict = array();

    foreach ($items as $key => $value) {
      $dict[$key] = array();
      foreach ($value as $key2 => $value2) {
          $dict[$key][$key2] = $value2;
      }
    }

    return $dict;
  }

  /**
   * Custom Twig template rendered
   * @static
   *
   * @param $template
   * @param $dict
   */
  public static function renderTwig($template, $dict) {
    global $twig;
    if (empty($GLOBALS['bufferederrors'])) {
      echo $twig->render($template, $dict);
    }
  }

  /**
   * Custom include function for view files
   * @static
   *
   * @param      $view
   * @param bool $admin
   */
  public static function includeView($view, $admin = false) {
    global $dict, $module, $twig;

    if (!file_exists($view)) {
      App::createView($view, $module, $admin);
    } else {
      include_once($view);
    }
  }

  /**
   * Creates basic views for admin and frontend if they do not exist
   * @static
   *
   * @param $view
   * @param $module
   * @param $admin
   */
  public static function createView($view, $module, $admin) {
    // Strip all but letters and numbers and make lower case then upper case first letter in module name
    $module_lower = strtolower(preg_replace('/[^a-z0-9]/i','', $module));
    $module_upper = ucfirst($module_lower);

    if ($admin) {

      // Basic admin view file
      $file  = '<?php' . "\n";
      $file .= 'App::requireModel(\'models/\' . $module . \'.php\', true);' . "\n";
      $file .= '$model  = new Model_' . $module_upper . '();' . "\n\n";
      $file .= 'include_once \'common.php\';';

    } else {

      // Basic frontend view file
      $file  = '<?php' . "\n";
      $file .= 'App::requireModel(\'models/\' . $module . \'.php\', false);' . "\n";
      $file .= '$model  = new Model_' . $module_upper . '();' . "\n\n";
      $file .= '$dict[$module] = $model->' . $module_lower . '();' . "\n\n";
      $file .= 'echo $twig->render(\'' . $module_lower . '.html\', $dict);';

    }

    $fp = fopen($view, 'w');
    fwrite($fp, $file);
    fclose($fp);

    App::includeView($view, $admin);
  }

  /**
   * Custom require_once function for model files
   * @static
   *
   * @param      $model
   * @param bool $admin
   */
  public static function requireModel($model, $admin = false) {
    global $module;

    if (!file_exists($model)) {
      App::createModel($model, $module, $admin);
    } else {
      require_once($model);
    }
  }

  /**
   * Creates basic models for admin and frontend if they do not exist
   * @static
   *
   * @param $view
   * @param $module
   * @param $admin
   */
  public static function createModel($view, $module, $admin) {
    // Strip all but letters and numbers and make lower case then upper case first letter in module name
    $module_lower = strtolower(preg_replace('/[^a-z0-9]/i','', $module));
    $module_upper = ucfirst($module_lower);

    if ($admin) {

      // Basic admin view file
      $file  = '<?php' ."\n\n";
      $file .= 'class Model_' . $module_upper . ' extends RedBean_SimpleModel {' . "\n\n";
      $file .= "\t" . 'function fields() {' . "\n";
      $file .= "\t\t" . '// Add fields here' . "\n";
      $file .= "\t\t" . '$fields[\'title\']       = array(\'type\'=>\'text\', \'label\'=>\'title\', \'help\'=>\'This is optional help text\');' . "\n\n";
      $file .= "\t\t" . '// Settings' . "\n";
      $file .= "\t\t" . '$fields[\'add\']        = true;' . "\n";
      $file .= "\t\t" . '$fields[\'edit\']       = true;' . "\n";
      $file .= "\t\t" . '$fields[\'delete\']     = true;' . "\n";
      $file .= "\t\t" . 'return $fields;' . "\n";
      $file .= "\t" .'}' . "\n\n";
      $file .= "\t" .'function settings() {' . "\n";
      $file .= "\t\t" . '$dict = App::getSettings($this->fields());' . "\n";
      $file .= "\t\t" . 'return $dict;' . "\n";
      $file .= "\t" .'}' . "\n\n";
      $file .= "\t" .'function view() {' . "\n";
      $file .= "\t\t" . 'global $module;' . "\n";
      $file .= "\t\t" . '$dict = App::view($module, __CLASS__); // Region optional' . "\n";
      $file .= "\t\t" . 'return $dict;' . "\n";
      $file .= "\t" .'}' . "\n\n";
      $file .= "\t" .'function count() {' . "\n";
      $file .= "\t\t" . 'global $module;' . "\n";
      $file .= "\t\t" . '$dict = App::count($module); // Region optional' . "\n";
      $file .= "\t\t" . 'return $dict;' . "\n";
      $file .= "\t" .'}' . "\n\n";
      $file .= "\t" .'function add() {' . "\n";
      $file .= "\t\t" . 'return App::buildForm($this->fields());' . "\n";
      $file .= "\t" .'}' . "\n\n";
      $file .= "\t" .'function edit($id) {' . "\n";
      $file .= "\t\t" . 'global $module;' . "\n";
      $file .= "\t\t" . 'sanitize($id);' . "\n";
      $file .= "\t\t" . 'return App::buildEditform($this->fields(), $module, $id);' . "\n";
      $file .= "\t" .'}' . "\n\n";
      $file .= "\t" . 'function trash($id) {' . "\n";
      $file .= "\t\t" . 'global $module;' . "\n";
      $file .= "\t\t" . 'sanitize($id);' . "\n";
      $file .= "\t\t" . 'return App::trash($id, $module);' . "\n";
      $file .= "\t" . '}' . "\n";
      $file .= '}';

    } else {

      // Basic frontend view file
      $file  = '<?php' . "\n";
      $file .= 'class Model_' . $module_upper . ' extends RedBean_SimpleModel {' . "\n\n";
      $file .= "\t" . 'function ' . $module_lower . '() {' . "\n";
      $file .= "\t\t" . '$dict = array();' . "\n";
      $file .= "\t\t" . '// Add database calls here' . "\n";
      $file .= "\t\t" . 'return $dict;' . "\n";
      $file .= "\t" . '}' . "\n";
      $file .= '}';

    }

    $fp = fopen($view, 'w');
    fwrite($fp, $file);
    fclose($fp);

    App::includeView($view, $admin);
  }

  /**
   * Displays the data table list view in the cPanel
   * @static
   *
   * @param      $module
   * @param      $class
   * @param null $region
   *
   * @return mixed
   */
  public static function view($module, $class, $region = null) {
    require_once realpath(dirname(__FILE__).'/..'). '/models/'.$module.'.php';
    $class = new $class;
    $settings = App::getSettings($class->fields());

    $orderby = $settings['orderby'];
    $order   = $settings['order'];

    $start = (isset($_GET['start'])) ? (int)$_GET['start'] : 0;
    $limit = R::getCell('SELECT pagination FROM settings LIMIT 1');

    $limit = ($limit) ? (int)$limit : 999999;

    if ($region) {
      // Region specific
      $data  = R::getAll('SELECT * FROM `' . $module . '` WHERE region = "' . $region . '" ORDER BY `' . $orderby . '` ' . $order . ' LIMIT ' . $start . ',' .$limit);
    } else {
      // Site specific
      $data  = R::getAll('SELECT * FROM `' . $module . '` WHERE 1 ORDER BY `' . $orderby . '` ' . $order . ' LIMIT ' . $start . ',' .$limit);
    }
    $dict = App::removeForeignkeys($data);
    $dict = App::removeHidden($dict, $module, $class);
    $dict = App::parseTabledata($dict);
    $dict = App::parseImages($dict);
    return $dict;
  }

  /**
   * Pagination - gets data and creates a navigation template
   * @static
   *
   * @param      $module
   * @param null $region
   *
   * @return array
   */
  public static function count($module, $region = null) {
    $pagination = array();
    $limit = R::getCell('SELECT pagination FROM settings LIMIT 1');
    $limit = ($limit) ? (int)$limit : 999999;

    // How many adjacent pages should be shown on each side of the current tab?
    $adjacents = 3;

    $start = (isset($_GET['start'])) ? (int)$_GET['start'] : 0;

    if ($region) {
      // Region specific
      $total = count(R::find($module,' region = ?', array( $region )));
    } else {
      // Site specific
      $total = count(R::find($module));
    }
      $tabs = ceil($total / $limit);
      $page = ($start / $limit) + 1;

      $pagination['total']       = $total;
      $pagination['page']        = $page;
      $pagination['start']       = $start;
      $pagination['tabs']        = $tabs;
      $pagination['limit']       = $limit;
      $pagination['previous']    = $start - $limit;
      $pagination['next']        = $start + $limit;
      $pagination['penultimate'] = $tabs - 1;


      $template = "";

      if ($tabs > 1) {
        $template .= '<ul>';
        // Previous button
        if ($page > 1)
          $template .= '<li><a href="/admin/'. $module .'/?start='. $pagination['previous'] .'">&larr;</a></li>';
        else
          $template .= '<li class="disabled"><a href="#">&larr;</a></li>';

        // Pages
        if ($tabs < 7 + ($adjacents * 2)) {	// Not enough pages to bother breaking it up

          for ($counter = 1; $counter <= $tabs; $counter++) {
            if ($counter == $page)
              $template .= '<li class="active"><a href="/admin/'. $module .'/?start='. ($counter - 1) * $limit .'">'. $counter .'</a></li>';
            else
              $template .= '<li><a href="/admin/'. $module .'/?start='. ($counter - 1) * $limit .'">'. $counter .'</a></li>';
          }
        } elseif ($tabs > 5 + ($adjacents * 2)) { // Enough pages to hide some
          // Close to beginning; only hide later pages
          if($page < 1 + ($adjacents * 2)) {
            for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
              if ($counter == $page)
                $template .= '<li class="active"><a href="/admin/'. $module .'/?start='. ($counter - 1) * $limit .'">'. $counter .'</a></li>';
              else
                $template .= '<li><a href="/admin/'. $module .'/?start='. ($counter - 1) * $limit .'">'. $counter .'</a></li>';
            }
            $template .= '<li class="disabled"><a href="#">...</a></li>';
            $template .= '<li><a href="/admin/'. $module .'/?start='. (($pagination['penultimate'] * $limit) - $limit) .'">'. $pagination['penultimate'] .'</a></li>';
            $template .= '<li><a href="/admin/'. $module .'/?start='. $pagination['penultimate'] * $limit .'">'. $tabs .'</a></li>';

          } elseif ($tabs - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
            // In middle; hide some front and some back
            $template .= '<li><a href="/admin/'. $module .'/?start=0">1</a></li>';
            $template .= '<li><a href="/admin/'. $module .'/?start='. $limit .'">2</a></li>';
            $template .= '<li class="disabled"><a href="#">...</a></li>';
            for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
              if ($counter == $page)
                $template .= '<li class="active"><a href="/admin/'. $module .'/?start='. ($counter - 1) * $limit .'">'. $counter .'</a></li>';
              else
                $template .= '<li><a href="/admin/'. $module .'/?start='. ($counter - 1) * $limit .'">'. $counter .'</a></li>';
            }
            $template .= '<li class="disabled"><a href="#">...</a></li>';
            $template .= '<li><a href="/admin/'. $module .'/?start='. (($pagination['penultimate'] * $limit) - $limit) .'">'. $pagination['penultimate'] .'</a></li>';
            $template .= '<li><a href="/admin/'. $module .'/?start='. $pagination['penultimate'] * $limit .'">'. $tabs .'</a></li>';
          }
          // Close to end; only hide early pages
          else {
            $template .= '<li><a href="/admin/'. $module .'/?start=0">1</a></li>';
            $template .= '<li><a href="/admin/'. $module .'/?start='. $limit .'">2</a></li>';
            $template .= '<li class="disabled"><a href="#">...</a></li>';
            for ($counter = $tabs - (2 + ($adjacents * 2)); $counter <= $tabs; $counter++) {
              if ($counter == $page)
                $template .= '<li class="active"><a href="/admin/'. $module .'/?start='. ($counter - 1) * $limit .'">'. $counter .'</a></li>';
              else
                $template .= '<li><a href="/admin/'. $module .'/?start='. ($counter - 1) * $limit .'">'. $counter .'</a></li>';
            }
          }
        }

        // Next button
        if ($page < $counter - 1)
          $template .= '<li><a href="/admin/'. $module .'/?start='. $pagination['next'] .'">&rarr;</a></li>';
        else
          $template .= '<li class="disabled"><a href="#">&rarr;</a></li>';
          $template .= '</ul>';
      }

      $pagination['template'] = $template;

    return $pagination;
  }

  /**
   * Function to remove foreign keys from table view in cPanel
   * @static
   *
   * @param $dict
   *
   * @return mixed
   */
  public static function removeForeignkeys($dict) {
    $i = 0;
    foreach($dict as $key => $value) {
      foreach($value as $key => $value) {
        if (is_array($value)) { unset($dict[$i][$key]); }
        if ($key == 'region') { unset($dict[$i][$key]); }
      }
    $i++;
    }
    return $dict;
  }

  /**
   * Function to remove fields from table view in models when set to "table_hide" => true
   * @static
   *
   * @param $dict
   * @param $model
   * @param $class
   *
   * @return mixed
   */
  public static function removeHidden($dict, $model, $class) {
    $fields = App::getFields($model, $class);

    $i = 0;
    foreach($dict as $key => $value) {
      foreach($value as $key2 => $value2) {
        if ($key2 != 'id' && array_key_exists($key2, $fields)) {
          if ($fields[$key2]['hide'] === true) { unset($dict[$i][$key2]); }
        }
      }
    $i++;
    }
    return $dict;
  }

  /**
   * Trims admin table view fields to 100 characters and removes html
   * @static
   *
   * @param $dict
   *
   * @return mixed
   */public static function parseTabledata($dict) {
    $i = 0;
    foreach($dict as $key => $value) {
      foreach($value as $key2 => $value2) {
        if (strlen($value2) > 100) {
          $value2 = preg_replace('/\s+?(\S+)?$/', '', substr($value2, 0, 101)) . '&hellip;';
        }
        $dict[$i][$key2] = $value2;
      }
    $i++;
    }
    return $dict;
  }

  public static function parseImages($dict) {
    $replacement = '<a class="thumbnail"><img src="/${0}" /></a>
                    <div class="modal fade hide">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">×</button>
                        <h3>Preview</h3>
                      </div>
                      <div class="modal-body">
                        <center><img src="/${0}" style="max-width:500px;" /></center>
                      </div>
                    </div>';
    $i = 0;
    foreach($dict as $key => $value) {
      foreach($value as $key2 => $value2) {
        $test = preg_replace('/^.*.(?:jpe?g|gif|png)$/', $replacement, $value2);
        if ($test) {
          $dict[$i][$key2] = $test;
        }
      }
    $i++;
    }
    return $dict;
  }

  /**
   * Builds add item form
   * @static
   *
   * @param $fields
   *
   * @return array
   */
  public static function buildForm($fields) {
    $form = array();

    foreach($fields as $key => $field) {

      if ($key != 'add' && $key != 'edit' && $key != 'delete' && $key != 'run') {

        if ($field['type'] == 'foreignkey') {

            if ($field['relation'] == 'own') { // One to many

              $name = $field['relation'].ucfirst($field['model']);

              $form[$name]              = array();
              $form[$name]['type']      = $field['type'];
              $form[$name]['label']     = $field['label'];
              $form[$name]['relation']  = $field['relation'];
              $form[$name]['fields']    = array();
              $form[$name]['fields']    = App::getFields($field['model'], $field['class']);

            } elseif ($field['relation'] == 'shared') { // Many to many

              $name = $field['relation'].ucfirst($field['model']);

              $form[$name]              = array();
              $form[$name]['type']      = $field['type'];
              $form[$name]['label']     = $field['label'];
              $form[$name]['relation']  = $field['relation'];
              $form[$name]['fields']    = array();
              $form[$name]['fields']    = App::getShared($field['model'], $field['selecttitle']);
              $form[$name]['required']  = (isset($field['required']) && $field['required'] === true) ? true : false;
              $form[$name]['one']       = (isset($field['one']) && $field['one'] === true) ? true : false;
              $form[$name]['help']      = (isset($field['help'])) ? $field['help'] : null;

            }
        
        } elseif ($field['type'] == 'file') { // File fields

          $form[$key] = array();
          $form[$key]['type']       = $field['type'];
          $form[$key]['label']      = $field['label'];
          $form[$key]['path']       = $field['path'];
          $form[$key]['accept']     = (isset($field['accept'])) ? $field['accept'] : 'gif, jpg, jpeg, png';
          $form[$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
          $form[$key]['validate']   = (isset($field['accept'])) ? $field['accept'] : 'gif,jpg,jpeg,png';
          $form[$key]['hide']       = (isset($field['table_hide']) && $field['table_hide'] === true) ? true : false;
          $form[$key]['help']       = (isset($field['help'])) ? $field['help'] : null;

        } elseif ($field['type'] == 'select') { // File fields

          $form[$key] = array();
          $form[$key]['type']       = $field['type'];
          $form[$key]['label']      = $field['label'];
          $form[$key]['values']     = (is_array($field['values'])) ? $field['values'] : '';
          $form[$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
          $form[$key]['hide']       = (isset($field['table_hide']) && $field['table_hide'] === true) ? true : false;
          $form[$key]['help']       = (isset($field['help'])) ? $field['help'] : null;

        } elseif ($field['type'] == 'radio') { // Radio fields

          $form[$key] = array();
          $form[$key]['type']       = $field['type'];
          $form[$key]['label']      = $field['label'];
          $form[$key]['values']     = (is_array($field['values'])) ? $field['values'] : '';
          $form[$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
          $form[$key]['hide']       = (isset($field['table_hide']) && $field['table_hide'] === true) ? true : false;
          $form[$key]['help']       = (isset($field['help'])) ? $field['help'] : null;

        } elseif ($field['type'] == 'textarea') { // Textarea fields

          $form[$key] = array();
          $form[$key]['type']       = $field['type'];
          $form[$key]['label']      = $field['label'];
          $form[$key]['maxlength'] = (isset($field['maxlength'])) ? $field['maxlength'] : null;
          $form[$key]['readonly']   = (isset($field['readonly']) && $field['readonly'] == true) ? true : false;
          $form[$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
          $form[$key]['rich_editor']= true;
          if (isset($field['rich_editor']) && $field['rich_editor'] === true) {
            $form[$key]['rich_editor'] = true;
          } elseif (isset($field['rich_editor']) && $field['rich_editor'] === false) {
            $form[$key]['rich_editor'] = false;
          }
          $form[$key]['value']      = (isset($data[0][$key])) ? $data[0][$key] : null;
          $form[$key]['hide']       = (isset($field['table_hide']) && $field['table_hide'] === true) ? true : false;
          $form[$key]['help']       = (isset($field['help'])) ? $field['help'] : null;

        } else { // Own fields
        
          $form[$key] = array();
          $form[$key]['type']       = $field['type'];
          $form[$key]['label']      = $field['label'];
          $form[$key]['maxlength'] = (isset($field['maxlength'])) ? $field['maxlength'] : null;
          $form[$key]['readonly']   = (isset($field['readonly']) && $field['readonly'] === true) ? true : false;
          $form[$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
          $form[$key]['validate']   = (isset($field['validate'])) ? $field['validate'] : false;
          $form[$key]['equalto']    = (isset($field['equalto'])) ? $field['equalto'] : false;
          $form[$key]['hide']       = (isset($field['table_hide']) && $field['table_hide'] === true) ? true : false;
          $form[$key]['help']       = (isset($field['help'])) ? $field['help'] : null;
        
        }
      }
    }
    return $form;
  }

  /**
   * Gets fields from model - returns buildForm() with fields array
   * @static
   *
   * @param $model
   * @param $class
   *
   * @return array
   */
  public static function getFields($model, $class) {
    require_once realpath(dirname(__FILE__).'/..'). '/models/'.$model.'.php';
    $class = new $class;

    return App::buildForm($class->fields());
  }

  /**
   * @static
   *
   * @param $model
   * @param $select
   *
   * @return array|null
   */
  public static function getShared($model, $select) {

    $data = null;

    $tables = R::$writer->getTables();
    if (in_array($model, $tables)) {
      $columns = R::$writer->getColumns($model);
      if(array_key_exists($select, $columns)) {
        $data = R::getAll( 'SELECT id, '. $select .' AS selecttitle  FROM ' . $model );
      }
    }

    return $data;
  }

  /**
   * Builds edit form for existing item
   * @static
   *
   * @param $fields
   * @param $module
   * @param $id
   *
   * @return array
   */
  public static function buildEditform($fields, $module, $id) {
    $data = R::load($module, $id);
    $data = R::exportAll($data, true);

    $form = array();
    $form['id'] = $id;
    $form['start'] = (isset($_GET['start'])) ? $_GET['start'] : 0;

    //echo '<pre>' . print_r ($fields, true ) . '</pre.'; exit;

    foreach($fields as $key => $field) {

      if ($key != 'add' && $key != 'edit' && $key != 'delete' && $key != 'run') {

        if ($field['type'] == 'foreignkey') {

            if ($field['relation'] == 'own') { // One to many

              $name = $field['relation'].ucfirst($field['model']);

              $form[$name]              = array();
              $form[$name]['type']      = $field['type'];
              $form[$name]['label']     = $field['label'];
              $form[$name]['relation']  = $field['relation'];
              $form[$name]['fields']    = array();
              $form[$name]['fields']    = App::getEditfields($module, $field['model'], $field['class'], $id);

            } elseif ($field['relation'] == 'shared') { // Many to many

              $name = $field['relation'].ucfirst($field['model']);

              $form[$name]              = array();
              $form[$name]['type']      = $field['type'];
              $form[$name]['label']     = $field['label'];
              $form[$name]['relation']  = $field['relation'];
              $form[$name]['fields']    = array();
              $form[$name]['fields']    = App::getEditshared($module, $field['model'], $field['selecttitle'], $id);
              $form[$name]['required']  = (isset($field['required']) && $field['required'] === true) ? true : false;
              $form[$name]['one']       = (isset($field['one']) && $field['one'] === true) ? true : false;
              $form[$name]['help']      = (isset($field['help'])) ? $field['help'] : null;

            }
        
        } elseif ($field['type'] == 'file') { // File fields

          $form[$key] = array();
          $form[$key]['type']       = $field['type'];
          $form[$key]['label']      = $field['label'];
          $form[$key]['path']       = (isset($field['path'])) ? $field['path'] : false;
          $form[$key]['accept']     = (isset($field['accept'])) ? $field['accept'] : 'gif, jpg, jpeg, png';
          $form[$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
          $form[$key]['validate']   = (isset($field['accept'])) ? $field['accept'] : 'gif,jpg,jpeg,png';
          $form[$key]['value']      = (isset($data[0][$key])) ? $data[0][$key] : null;
          $form[$key]['isimage']    = (isset($data[0][$key]) && is_array(@getimagesize(LOCAL_PATH . $data[0][$key]))) ? true : false;
          $form[$key]['help']       = (isset($field['help'])) ? $field['help'] : null;

        } elseif ($field['type'] == 'select') { // File fields

          $form[$key] = array();
          $form[$key]['type']       = $field['type'];
          $form[$key]['label']      = $field['label'];
          $form[$key]['values']     = (is_array($field['values'])) ? $field['values'] : '';
          $form[$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
          $form[$key]['value']      = (isset($data[0][$key])) ? $data[0][$key] : null;
          $form[$key]['help']       = (isset($field['help'])) ? $field['help'] : null;

        } elseif ($field['type'] == 'radio') { // Radio fields

          $form[$key] = array();
          $form[$key]['type']       = $field['type'];
          $form[$key]['label']      = $field['label'];
          $form[$key]['values']     = (is_array($field['values'])) ? $field['values'] : '';
          $form[$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
          $form[$key]['value']      = (isset($data[0][$key])) ? $data[0][$key] : null;
          $form[$key]['help']       = (isset($field['help'])) ? $field['help'] : null;

        } elseif ($field['type'] == 'textarea') { // Textarea fields

          $form[$key] = array();
          $form[$key]['type']       = $field['type'];
          $form[$key]['label']      = $field['label'];
          $form[$key]['maxlength'] = (isset($field['maxlength'])) ? $field['maxlength'] : null;
          $form[$key]['readonly']   = (isset($field['readonly']) && $field['readonly'] == true) ? true : false;
          $form[$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
          $form[$key]['rich_editor']= true;
          if (isset($field['rich_editor']) && $field['rich_editor'] === true) {
            $form[$key]['rich_editor'] = true;
          } elseif (isset($field['rich_editor']) && $field['rich_editor'] === false) {
            $form[$key]['rich_editor'] = false;
          }
          $form[$key]['value']      = (isset($data[0][$key])) ? $data[0][$key] : null;
          $form[$key]['hide']       = (isset($field['table_hide']) && $field['table_hide'] === true) ? true : false;
          $form[$key]['help']       = (isset($field['help'])) ? $field['help'] : null;

        } else { // Own fields
        
          $form[$key] = array();
          $form[$key]['type']       = $field['type'];
          $form[$key]['label']      = $field['label'];
          $form[$key]['maxlength'] = (isset($field['maxlength'])) ? $field['maxlength'] : null;
          $form[$key]['readonly']   = (isset($field['readonly']) && $field['readonly'] == true) ? true : false;
          $form[$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
          $form[$key]['validate']   = (isset($field['validate'])) ? $field['validate'] : false;
          $form[$key]['equalto']    = (isset($field['equalto'])) ? $field['equalto'] : false;
          $form[$key]['value']      = (isset($data[0][$key])) ? $data[0][$key] : null;
          $form[$key]['help']       = (isset($field['help'])) ? $field['help'] : null;
        
        }
      }
    }
    return $form;
  }

  /**
   * Builds edit form for existing o2m fields data
   * @param $fields
   *
   * @return array
   */
  public static function buildEditformownfields($fields) {
    $form = array();

    foreach($fields as $key => $field) {

      if ($key != 'add' && $key != 'edit' && $key != 'delete' && $key != 'run') {

        if ($field['type'] == 'foreignkey') {

            if ($field['relation'] == 'own') { // One to many

              $name = $field['relation'].ucfirst($field['model']);

              $form[$name]              = array();
              $form[$name]['type']      = $field['type'];
              $form[$name]['label']     = $field['label'];
              $form[$name]['relation']  = $field['relation'];
              $form[$name]['fields']    = array();
              $form[$name]['fields']    = App::getFields($field['model'], $field['class']);

            }
        
        }
      }
    }
    return $form;
  }

  /**
   * Gets edit fields and existing data for showing items
   * @static
   *
   * @param $parent
   * @param $model
   * @param $class
   * @param $id
   *
   * @return array
   */
  public static function getEditfields($parent, $model, $class, $id) {
    require_once realpath(dirname(__FILE__).'/..'). '/models/'.$model.'.php';
    $class = new $class;
    $fields = $class->fields();

    $parent = R::load($parent, $id);
    $own    = 'own'.ucfirst($model);

    $data = R::exportAll($parent->$own);

    $array = array();
    $i = 0;
    foreach ($data as $key => $value) {
      foreach($fields as $key => $field) {

        if ($key != 'add' && $key != 'edit' && $key != 'delete' && $key != 'run') {
          if ($field['type'] != 'foreignkey' && $field['type'] != 'file' && $field['type'] != 'select' && $field['type'] != 'radio') {

            $array[$i][$key] = array();
            $array[$i][$key]['type']       = $field['type'];
            $array[$i][$key]['label']      = $field['label'];
            $array[$i][$key]['maxlength'] = (isset($field['maxlength'])) ? $field['maxlength'] : null;
            $array[$i][$key]['id']         = $data[$i]['id'];
            $array[$i][$key]['value']      = $data[$i][$key];
            $array[$i][$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
            $array[$i][$key]['validate']   = (isset($field['validate'])) ? $field['validate'] : false;
            $array[$i][$key]['help']       = (isset($field['help'])) ? $field['help'] : null;

          } elseif ($field['type'] == 'file') {

            $array[$i][$key] = array();
            $array[$i][$key]['type']       = $field['type'];
            $array[$i][$key]['label']      = $field['label'];
            $array[$i][$key]['maxlength'] = (isset($field['maxlength'])) ? $field['maxlength'] : null;
            $array[$i][$key]['id']         = $data[$i]['id'];
            $array[$i][$key]['value']      = $data[$i][$key];
            $array[$i][$key]['isimage']    = (isset($data[$i][$key]) && is_array(@getimagesize(LOCAL_PATH . $data[$i][$key]))) ? true : false;
            $array[$i][$key]['path']       = (isset($field['path'])) ? $field['path'] : false;
            $array[$i][$key]['accept']     = (isset($field['accept'])) ? $field['accept'] : 'gif, jpg, jpeg, png';
            $array[$i][$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
            $array[$i][$key]['validate']   = (isset($field['accept'])) ? $field['accept'] : 'gif,jpg,jpeg,png';
            $array[$i][$key]['help']       = (isset($field['help'])) ? $field['help'] : null;

          } elseif ($field['type'] == 'select') { // File fields

            $array[$i][$key] = array();
            $array[$i][$key]['type']       = $field['type'];
            $array[$i][$key]['label']      = $field['label'];
            $array[$i][$key]['id']         = $data[$i]['id'];
            $array[$i][$key]['value']      = $data[$i][$key];
            $array[$i][$key]['values']     = (is_array($field['values'])) ? $field['values'] : '';
            $array[$i][$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
            $array[$i][$key]['help']       = (isset($field['help'])) ? $field['help'] : null;

          } elseif ($field['type'] == 'radio') { // Radio fields

            $array[$i][$key] = array();
            $array[$i][$key]['type']       = $field['type'];
            $array[$i][$key]['label']      = $field['label'];
            $array[$i][$key]['id']         = $data[$i]['id'];
            $array[$i][$key]['value']      = $data[$i][$key];
            $array[$i][$key]['values']     = (is_array($field['values'])) ? $field['values'] : '';
            $array[$i][$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
            $array[$i][$key]['help']       = (isset($field['help'])) ? $field['help'] : null;

          }
        }
      }
    $i++;
    }

    return $array;
  }

  /**
   * Returns foreign key fields along with data - sets selected where applicable
   * @static
   *
   * @param $parent
   * @param $model
   * @param $select
   * @param $id
   *
   * @return array|null
   */
  public static function getEditshared($parent, $model, $select, $id) {

    $data = null;

    $parent = R::load($parent, $id);
    $shared = 'shared'.ucfirst($model);

    $data = R::exportAll($parent->$shared);

    $selected = array();
    foreach ($data as $shared) {
      $selected[] = $shared['id'];
    }

    $tables = R::$writer->getTables();
    if (in_array($model, $tables)) {
      $columns = R::$writer->getColumns($model);
      if(array_key_exists($select, $columns)) {
        $data = R::getAll( 'SELECT id, '. $select .' AS selecttitle  FROM ' . $model );
      }
    }

    foreach ($data as $key => $value) {
      if (in_array($data[$key]['id'], $selected)) {
        $data[$key]['id']           = $value['id'];
        $data[$key]['selecttitle']  = $value['selecttitle'];
        $data[$key]['selected']     = true;
      } else {
        $data[$key]['id']           = $value['id'];
        $data[$key]['selecttitle']  = $value['selecttitle'];
        $data[$key]['selected']     = false;
      }
    }

    return $data;
  }

  /**
   * Gets settings defined in model
   * @static
   *
   * @param $fields
   *
   * @return array
   */
  public static function getSettings($fields) {
      $dict = array();

      $dict['add']     = false;
      $dict['edit']    = false;
      $dict['delete']  = false;

      $dict['orderby'] = false;
      $dict['order']   = false;

      $dict['run']     = false;

      if (isset($fields['add']) && $fields['add'] === true) {
        $dict['add']   = 'true';
      } elseif (is_numeric($fields['add'])) {
        $dict['add']   = $fields['add'];
      }

      $dict['edit']    = (isset($fields['edit']) && $fields['edit'] == true) ? true : false;
      $dict['delete']  = (isset($fields['delete']) && $fields['delete'] == true) ? true : false;
      $dict['orderby'] = (isset($fields['orderby'])) ?  $fields['orderby'] : 'id';
      $dict['order']   = (isset($fields['order'])) ?  strtolower($fields['order']) : 'asc';
      $dict['run']['path'] = (isset($fields['run']['path'])) ?  $fields['run']['path'] : false;
      if ($dict['run']['path']) {
        $dict['run']['button']         = (isset($fields['run']['button'])) ?  $fields['run']['button'] : 'Run';
        $dict['run']['button_running'] = (isset($fields['run']['button_running'])) ?  $fields['run']['button_running'] : 'Running...';
      }
    return $dict;
  }

  /**
   * Save new item to database
   * @param $_POST
   */
  public function save($POST) {
    require_once realpath(dirname(__FILE__).'/../..'). '/includes/common/imageupload.php';
    $_POST = sanitize($POST);
    $_FILES = sanitize($_FILES);
    $module = $_POST['modulename'];
    $ownfields = null;
    $owninfo = null;
    // echo '<pre>' . print_r($_FILES, true) . '</pre>'; 
    // echo '<pre>' . print_r($_POST, true) . '</pre>';exit;

    require_once 'models/' . $module . '.php';

    if ($_FILES) {
      $_FILES = App::organiseFiles($_FILES, $module);
      $i = 0;
      foreach ($_FILES[$module] as $key => $value) {
        if ($_FILES[$module][$key]['tmp_name']) {

          $info = explode('|', $key); // First field is the key, second is the path, third is the accepted file types

          if (count($info) == 1 && substr($info[0], 0, 3) === 'own') {
            $owninfo[$i] = $info[0];
            $ownfields[$i] = $_FILES[$module][$info[0]]['tmp_name'];
          }

          if (!$ownfields[$i]) { // Continue if not foreign key (own) file
            $path = $info[1];
            $path = rtrim($path, '/');
            $path = ltrim($path, '/');
            $extensions = explode(',', $info[2]);
            $accept = array();
            foreach ($extensions as $ext) {
              $ext = str_replace("'", '', $ext);
              $ext = str_replace(" ", '', $ext);
              $accept[] = $ext;
            }

            $upload = new Upload();
            $upload->SetFileName($_FILES[$module][$key]['name']);
            $upload->SetTempName($_FILES[$module][$key]['tmp_name']);
            $upload->SetUploadDirectory( realpath(dirname(__FILE__).'/../..') . '/' . $path . '/');
            $upload->SetValidExtensions($accept);
            //$upload->SetMaximumFileSize(300000); // Maximum file size in bytes, if this is not set, the value in your php.ini file will be the maximum value
            $file = $upload->UploadFile();

            $_POST[$module][$info[0]] = $path . '/' . $file;
          }
        }
      $i++;
      }
    }
      if (!is_null($ownfields)) {
        foreach ($ownfields as $key1 => $value1) {
          foreach ($value1 as $key => $value) {
            if ($_FILES[$module][$owninfo[$key1]]['tmp_name']) {

              foreach ($_FILES[$module][$owninfo[$key1]]['tmp_name'] as $beanid => $images) {

                foreach ($images as $key => $image) {

                  if ($_FILES[$module][$owninfo[$key1]]['name'][$beanid][$key]) {

                    $info = explode('|', $key); // First field is the key, second is the path, third is the accepted file type, 4th is the field name for own fields
                    $path = $info[1];
                    $path = rtrim($path, '/');
                    $path = ltrim($path, '/');
                    $extensions = explode(',', $info[2]);
                    $accept = array();
                    foreach ($extensions as $ext) {
                      $ext = str_replace("'", '', $ext);
                      $ext = str_replace(" ", '', $ext);
                      $accept[] = $ext;
                    }

                    $upload = new Upload();
                    $upload->SetFileName($_FILES[$module][$owninfo[$key1]]['name'][$beanid][$key]);
                    $upload->SetTempName($_FILES[$module][$owninfo[$key1]]['tmp_name'][$beanid][$key]);
                    $upload->SetUploadDirectory( realpath(dirname(__FILE__).'/../..') . '/' . $path . '/');
                    $upload->SetValidExtensions($accept);
                    //$upload->SetMaximumFileSize(300000); // Maximum file size in bytes, if this is not set, the value in your php.ini file will be the maximum value
                    $file = $upload->UploadFile();

                    if (!isset($_POST[$module][$owninfo[$key1]][$beanid][$info[3]])) {
                      $_POST[$module][$owninfo[$key1]][$beanid][$info[3]] = $path . '/' . $file;
                    }
                  }
                }
              }
            }
          }
        }
      }

    $_POST = array_remove_empty($_POST);

    // echo '<pre>' . print_r($_POST, true) . '</pre>'; exit;

    $shared = null;

    foreach ($_POST as $key => $value) {
      if (strlen(strstr($key,'shared'))>0) {
        unset($_POST[$key]);
        $key = strtolower(str_replace('shared', '', $key));
        $shared[$key] = $value;
      }
    }
    
    try {
      $data = R::graph($_POST[$module], true);
      if ($shared) {
        foreach ($shared as $model => $items) {
          foreach ($items as $id) {
            if ($id > 0) {
              $item = R::load($model, $id);
              R::associate( $data, $item );
            }
          }
        }
      }
      R::store($data);
    } catch (RedBean_Exception_Security $e) { echo '<pre>' . $e . '</pre>'; }
    header('Location: /admin/' . $module ); exit;
  }

  /**
   * Updates existing item in database
   * @param $_POST
   */
  public function update($POST) {
    require_once realpath(dirname(__FILE__).'/../..'). '/includes/common/imageupload.php';
    $_POST = sanitize($POST);
    $_FILES = sanitize($_FILES);
    $module = $_POST['modulename'];
    $start = (isset($_POST['start'])) ? $_POST['start'] : 0;
    $ownfields = null;
    $owninfo = null;

    require_once 'models/' . $module . '.php';

//    echo '<pre>' . print_r($_POST, true) . '</pre>'; exit;

    if (isset($_POST['removeimages'])) {
      App::removeImages($_POST['removeimages']);
      unset($_POST['removeimages']);
    }

    if ($_FILES) {
      $_FILES = App::organiseFiles($_FILES, $module);

      $i = 0;
      foreach ($_FILES[$module] as $key => $value) {
        if ($_FILES[$module][$key]['tmp_name']) {

          $info = explode('|', $key); // First field is the key, second is the path, third is the accepted file types

          if (count($info) == 1 && substr($info[0], 0, 3) === 'own') {
            $owninfo[$i] = $info[0];
            $ownfields[$i] = $_FILES[$module][$info[0]]['tmp_name'];
          } else { // Continue if not foreign key (own) file
            $path = $info[1];
            $path = rtrim($path, '/');
            $path = ltrim($path, '/');
            $extensions = explode(',', $info[2]);
            $accept = array();
            foreach ($extensions as $ext) {
              $ext = str_replace("'", '', $ext);
              $ext = str_replace(" ", '', $ext);
              $accept[] = $ext;
            }

            $upload = new Upload();
            $upload->SetFileName($_FILES[$module][$key]['name']);
            $upload->SetTempName($_FILES[$module][$key]['tmp_name']);
            $upload->SetUploadDirectory( realpath(dirname(__FILE__).'/../..') . '/' . $path . '/');
            $upload->SetValidExtensions($accept);
            //$upload->SetMaximumFileSize(300000); // Maximum file size in bytes, if this is not set, the value in your php.ini file will be the maximum value
            $file = $upload->UploadFile();

            $_POST[$module][$info[0]] = $path . '/' . $file;
          }
        }
      $i++;
      }
    }

    //echo '<pre>' . print_r($_FILES, true) . '</pre>';
      if (!is_null($ownfields)) {
        foreach ($ownfields as $key1 => $value1) {
          foreach ($value1 as $key => $value) {
            if ($_FILES[$module][$owninfo[$key1]]['tmp_name']) {

              foreach ($_FILES[$module][$owninfo[$key1]]['tmp_name'] as $beanid => $images) {

                foreach ($images as $key => $image) {

                  if ($image) {

                    $info = explode('|', $key); // First field is the key, second is the path, third is the accepted file type, 4th is the field name for own fields
                    $path = $info[1];
                    $path = rtrim($path, '/');
                    $path = ltrim($path, '/');
                    $extensions = explode(',', $info[2]);
                    $accept = array();
                    foreach ($extensions as $ext) {
                      $ext = str_replace("'", '', $ext);
                      $ext = str_replace(" ", '', $ext);
                      $accept[] = $ext;
                    }

                    $upload = new Upload();
                    $upload->SetFileName($_FILES[$module][$owninfo[$key1]]['name'][$beanid][$key]);
                    $upload->SetTempName($_FILES[$module][$owninfo[$key1]]['tmp_name'][$beanid][$key]);
                    $upload->SetUploadDirectory( realpath(dirname(__FILE__).'/../..') . '/' . $path . '/');
                    $upload->SetValidExtensions($accept);
                    //$upload->SetMaximumFileSize(300000); // Maximum file size in bytes, if this is not set, the value in your php.ini file will be the maximum value
                    $file = $upload->UploadFile();

                    if (!isset($_POST[$module][$owninfo[$key1]][$beanid][$info[3]])) { // Make sure empty loops don't overwrite previously set images!
                      $_POST[$module][$owninfo[$key1]][$beanid][$info[3]] = $path . '/' . $file;
                    }
                  }
                }
              }
            }
          }
        }
      }

//    echo '<pre>' . print_r($_POST, true) . '</pre>'; exit;

    $_POST = array_remove_empty($_POST);

//    echo '<pre>' . print_r($_POST, true) . '</pre>';exit;

    $shared = null;

    foreach ($_POST as $key => $value) {
      if (strlen(strstr($key,'shared'))>0) {
        unset($_POST[$key]);
        $key = strtolower(str_replace('shared', '', $key));
        $shared[$key] = $value;
      }
    }

    try {
      $data = R::graph($_POST[$module], true);
      $id   = R::store($data);
      if ($shared) {
        $data = R::load($module, $id);
        foreach ($shared as $model => $items) {
          R::clearRelations( $data, $model );
          if ($items) {
            foreach ($items as $id) {
              if ($id > 0) {
                $item = R::load($model, $id);
                R::associate( $data, $item );
              }
            }
          }
        }
      }

    R::store($data);
    } catch (RedBean_Exception_Security $e) { echo '<pre>' . $e . '</pre>'; }
    if (!isset($_POST['apply'])) {
      if ($start > 0) {
        header('Location: /admin/' . $module . '/?start=' . $start ); exit;
      } else {
        header('Location: /admin/' . $module ); exit;
      }
    }
  }

  /**
   * Function to remove images and files from database and server when cleared from cPanel
   * Array format - [removeimages][table][id][field]
   * @static
   *
   * @param $array
   */
  private static function removeImages($array) {
    /** @var $array This is the "removeimages" array for the images selected to be removed */
    foreach ($array AS $type => $bean) {
      foreach ($bean AS $id => $fields) {
        /** @var $item Redbean object for the record containing the image to be removed */
        $item = R::load($type, $id);
        foreach ($fields AS $field => $value) {
          if (!is_null($item->$field) && !is_null($value)) {
            /** @var $value File name to be unlinked and unset from the database */
            if (file_exists(LOCAL_PATH . $item->$field)) {
              unlink(LOCAL_PATH . $item->$field);
            }
            $item->$field = null;
          }
        }
        R::store($item);
      }
    }
  }

  /**
   * Trashes item from database
   * @static
   *
   * @param $id
   * @param $module
   *
   * @return array
   */
  public static function trash($id, $module) {
    $data = R::load($module, $id);
    if (!$data->id) {
      $dict['title']    = 'Error!';
      $dict['message']  = 'Record not found...';
    } else {
      // Todo: add foreach to iterate through fields that could be files
      // Check if file exists
      // Unlink($file) if file_exists
      R::trash( $data );
      $dict['title']    = 'Success!';
      $dict['message']  = 'Record successfully deleted';
    }
    return $dict;
  }

  /**
   * @var array
   */
  static $IMGVARS = array('name' => 1, 'type' => 1, 'tmp_name' => 1, 'error' => 1, 'size' => 1);

  /**
   * Re-orders $_FILES array to be more sensible
   * @param $files
   * @param $module
   *
   * @return array
   */
    private function organiseFiles($files, $module) {
    foreach ($files[$module] as $key => $part) {
      $key = (string) $key;
      if (isset(App::$IMGVARS[$key]) && is_array($part)) { // Only deal with valid keys and multiple files
        foreach ($part as $position => $value) {
          $files[$position][$key] = $value;
        }
        unset($files[$module][$key]);
      }
    }
    unset($files[$module]);
    $array = array();
    $array[$module] = $files;
    return $array;
  }

  /**
   * Creates an installable MySQL backup file and saves to the server and database
   * @static
   * @return string
   */
  public static function backupDatabase() {

    if (!App::checkSession()) { return; }

    // Get all of the tables
    $date   = date("Y-m-d-H-i-s");
    $date2  = date("Y-m-d H:i:s");
    $tables = R::$writer->getTables();

    $return = "# Export via Chuck Norris at {$date2}\n\n";

    $return .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;";
    $return .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;";
    $return .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;";
    $return .= "/*!40101 SET NAMES utf8 */;";
    $return .= "/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;";
    $return .= "/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;";
    $return .= "/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;\n\n";

    // Cycle through
    foreach($tables as $table) {
      $result = R::getAll('SELECT * FROM '.$table);
      $count = count($result);

      $return .= '# Dump of table ' .$table. "\n";
      $return .= "# ------------------------------------------------------------\n\n";

      $return .= 'DROP TABLE IF EXISTS `'.$table.'`;';
      $row2 = R::getRow('SHOW CREATE TABLE '.$table);
      $return .= "\n\n".$row2['Create Table'].";";
      $return .= "\n\n".'LOCK TABLES `'.$table.'` WRITE;'."\n";
      $return .= '/*!40000 ALTER TABLE `'.$table.'` DISABLE KEYS */;' . "\n\n";

      foreach ($result AS $row => $val) {
        $i = 0;
        $num_fields = count($val);
        $return.= 'INSERT INTO `'.$table.'` VALUES(';
        foreach ($val AS $key2 => $val2) {
          $val2 = addslashes($val2);
          $val2 = preg_replace("/\n/","\\n",$val2);
          if (isset($val2)) {
            $return.= '"'.$val2.'"';
          } else {
            $return.= '""';
          }
          if ($i<($num_fields-1)) {
            $return.= ', ';
          }
        $i++;
        }
        $return .= ");\n";
      }
      if ($count > 0) {
        $return .= "\n";
      }

      $return .= '/*!40000 ALTER TABLE `'.$table.'` ENABLE KEYS */;' . "\n";
      $return .= "UNLOCK TABLES;\n\n";

     }
    $return .= "/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;";
    $return .= "/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;";
    $return .= "/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;";
    $return .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;";
    $return .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;";
    $return .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;";
    $return .= "\n\n\n";

    if (!is_null($return)) {
      // Save file
      $filename = 'admin/backups/db-backup-'.$date.'.sql';
      $handle = fopen(LOCAL_PATH . $filename, 'w+');
      fwrite($handle, $return);
      fclose($handle);
      $backup = R::dispense('backup');

      $backup->file = $filename;
      $backup->date = $date2;

      R::store($backup);

      return '<blockquote><p>Successful backup run at ' . $date2 . '</p>
                <small>' . $filename . '</small>
              </blockquote>';
    }
  }

  /**
   * Runs Redbean schema log for migrations
   * @return bool
   */
  public function log() {
    $log_file = LOCAL_PATH . 'admin/timeline/log.txt';
    if (!is_writable($log_file)) {
      chmod($log_file, 0755);
    }
    R::log($log_file);
    return true;
  }

  /**
   * Counts cache
   * @return int
   */
  public function getCache() {
    $caches = array('cache' , 'admin/cache', 'mobile/cache');
    $directories = 0;
    foreach ($caches as $cache) {
      $directories += (count(glob(LOCAL_PATH . $cache . '/*', GLOB_ONLYDIR)));
    }
    return (int) $directories;
  }


  /**
   * Clears the Twig template caches
   */
  public function clearCache() {
    $caches = array('cache' , 'admin/cache', 'mobile/cache');

    foreach ($caches as $cache) {
      // Get cache contents
      $folders = glob(LOCAL_PATH . $cache . '/*');
      // Iterate over cache folder
      foreach($folders as $folder) {
        if(is_dir($folder))
          $this->rrmdir($folder);
      }
    }
    exit;
  }

  /**
   * Recursively removes directories
   * @param $dir
   */
  public function rrmdir($dir) {
    foreach(glob($dir . '/*') as $file) {
      if(is_dir($file))
        $this->rrmdir($file);
      else
        unlink($file);
    }
    rmdir($dir);
  }

  /**
   * Sets a custom error handler to override PHP's default
   */
  function parseErrors() {
    set_error_handler(array(&$this, "process_error_backtrace"));
  }

  /**
   * Processes error backtrace and displays custom debug template
   * @param $errno
   * @param $errstr
   * @param $errfile
   * @param $errline
   * @param $errcontext
   *
   * @return bool
   */
  function process_error_backtrace($errno, $errstr, $errfile, $errline, $errcontext) {
    global $twig;
    if ( 0 == error_reporting () ) {
      // Error reporting is currently turned off or suppressed with @
      return;
    }
    $errorTypes = Array(
      E_ERROR => 'Fatal Error',
      E_WARNING => 'Warning',
      E_PARSE => 'Parse Error',
      E_NOTICE => 'Notice',
      E_CORE_ERROR => 'Fatal Core Error',
      E_CORE_WARNING => 'Core Warning',
      E_COMPILE_ERROR => 'Compilation Error',
      E_COMPILE_WARNING => 'Compilation Warning',
      E_USER_ERROR => 'Triggered Error',
      E_USER_WARNING => 'Triggered Warning',
      E_USER_NOTICE => 'Triggered Notice',
      E_STRICT => 'Deprecation Notice',
      E_RECOVERABLE_ERROR => 'Catchable Fatal Error'
    );

    $trace = array_reverse(debug_backtrace());
    array_pop($trace);

    $i = 0;
    foreach ($trace AS $traced){
      $file = $traced['file'];
      $lines = file($file); // File in to an array
      $trace[$i]['output'] = $lines[$traced['line']-1];
      $i++;
    }

    $ret = array(
      'number'  => $errno,
      'message' => $errstr,
      'file'    => $errfile,
      'line'    => $errline,
      'context' => $errcontext,
      'type'    => $errorTypes[$errno],
      'trace'   => $trace
    );

    $GLOBALS['bufferederrors'][] = $ret;
    if (error_get_last()) {
      $dict['backtrace'] = $GLOBALS['bufferederrors'];
      $dict['phpversion'] = phpversion();
      echo $twig->render('error-trace.html', $dict);
      //echo '<pre>' . print_r($dict, true) . '</pre>';
    } else {
      return false;
    }
  }

}