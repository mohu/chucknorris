<?php
// error_reporting(0);
ini_set('html_errors',0);
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
  }

  /**
   * Runs core functions
   * @return mixed
   */
  public function coreFunctions() {

    if (isset($_POST['save'])) $this->save($_POST);
    if (isset($_POST['update']) || isset($_POST['apply'])) $this->update($_POST);
    if (isset($_POST['clearcache'])) $this->clearCache();
    if (isset($_POST['dragdropordering'])) $this->dragdropOrdering();

    $this->checkIP();
//    $this->parseErrors();
//    $this->log();
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
    $dict['cms']      = array('name'=>'Chuck',
                              'version'=>'0.6.0',
                              'dependencies'=>array('Bootstrap, from Twitter'=>'2.1.1',
                                                    'Glyphicons'=>'1.6',
                                                    'RedBeanPHP'=>R::getVersion(),
                                                    'Select2'=>'3.2',
                                                    'Swift Mailer'=>'4.2.1',
                                                    'Twig'=>'1.10.3',)
                              );

    $sitename         = R::getCell('SELECT sitename FROM settings');
    $dict['sitename'] = ($sitename) ? $sitename : $dict['cms']['name'];
    $dict['appletouchicon']  = (is_array(@getimagesize(LOCAL_PATH . 'apple-touch-icon-57x57-precomposed.png'))) ? true : false;
    $dict['cache']['status'] = $this->getCache();
    if ((R::getCell('SELECT debug FROM settings')) == 1) {
      // Add extra server and environment debug information to dict
      $dict['debugger']['server']= $_SERVER;
      $dict['debugger']['session']= $_SESSION;
      $dict['debugger']['env']= $_ENV;
      $dict['debugger']['request']= $_REQUEST;
      $dict['debugger']['cookies']= $_COOKIE;
    }

    return $dict;
  }

  /**
   * Checks remote client IP address and compares to database white list
   */
  public function checkIP() {
    global $twig;
    if (!defined('FRONTEND')) {
      $allowed_ips = R::getCol( 'SELECT ip FROM allowedips' );

      $allowed = false;

      if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] != '')
        $ip = $_SERVER['HTTP_CLIENT_IP'];
      elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '')
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != '')
        $ip = $_SERVER['REMOTE_ADDR'];
      if (($commapos = strpos($ip, ',')) > 0)
        $ip = substr($Ip, 0, ($commapos - 1));

      if ($this->wildcardMatch($ip, $allowed_ips) != true) {
        // The banned display page
        header('HTTP/1.1 401 Unauthorized');
        include_once realpath(dirname(__FILE__).'/../..'). '/admin/views/forbidden.php';
      }
    }
  }

  /**
   * Search arrays with wildcards
   * This function is now available on Windows platforms (PHP v5.3.0 +)
   * @param $needle
   * @param $haystack
   *
   * @return bool
   */
  function wildcardMatch($needle, $haystack) {
    foreach ($haystack as $value) {
      if (fnmatch($value, $needle) === true) {
        return true;
      }
    }
    return false;
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
   * Displays global dashboard search page
   * @static
   * @return array
   */
  public static function globalSearch() {
    $dict = array();
    if (isset($_POST["search"]) && ($_POST["search"] != "")) {
      $query = $_POST["search"];
      $dict['query'] = $query;
      $dict['results'] = array();

      $tables = R::$writer->getTables();

      $i = 0;
      foreach ($tables as $table) {
        $columns = R::$writer->getColumns($table);
        if($columns) {
          $sql = 'SELECT * FROM `' . $table . '` WHERE ';
          $j = 0;
          foreach ($columns as $column => $type) {
            $sql .= '`' . $column . '` LIKE :query';
            if ($j + 1 != count($columns)) {
              $sql .= ' OR ';
            } else {
              $sql .= '';
            }
            $j++;
          }
          $results = R::getAll($sql, array(':query'=>"%$query%"));
          if ($results) {
            $dict['results'][$table] = $results;
          }
        }
        $i++;
      }
    }

    if (!empty($dict['results'])) {

      $parsedresults = array();

      foreach ($dict['results'] as $key => $resultblock) {
        // Parse data to trim text for display
        $parsedresults[$key] = App::parseTabledata($resultblock);
      }

      $dict['results'] = $parsedresults;
    }

    return $dict;
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
   * Custom Twig template rendered for admin area
   * @static
   *
   * @param $template
   * @param $dict
   */
  public static function renderTwig($template, $dict) {
    global $twig;
    if (App::checkAccess($dict['session']['userid'], $template)) {
       if (empty($GLOBALS['bufferederrors'])) {
         echo $twig->render($template, $dict);
       }
    } else {
     echo $twig->render('not-allowed.twig', $dict);
    }
  }

	public static function checkAccess($id, $template) {
		global $module;
		$usergroup = R::getCell('SELECT usergroup_id FROM user_usergroup WHERE user_id = ' . $id);
		if ($usergroup == 1) return true; // Return true for super administrators

		$access_paths = R::getAll('SELECT a.path FROM access a INNER JOIN access_usergroup ug ON a.id = ug.access_id AND ug.usergroup_id = ' . $usergroup);

		$paths = array();
		foreach ($access_paths as $path) {
			$paths[] = $path['path'];
		}
		if (in_array($module, $paths)) return true;
		return false;
	}

  /**
   * Custom include function for view files
   * @static
   *
   * @param      $view_path
   * @param      $function
   * @param bool $admin
   */
  public static function includeView($view_path, $function, $admin = false) {
    global $dict, $view, $twig;

    if (!file_exists($view_path)) {
      App::createView($view_path, $view, $function, $admin);
    } else {
      include_once($view_path);
    }
  }

  /**
   * Creates basic views for admin and frontend if they do not exist
   * @static
   *
   * @param $view_path
   * @param $view
   * @param $admin
   */
  public static function createView($view_path, $view, $function, $admin) {
    // Strip all but letters and numbers and make lower case then upper case first letter in module name
    $module_lower = strtolower(preg_replace('/[^a-z0-9]/i','', $view));
    $module_upper = ucfirst($module_lower);

    if ($admin) {

      // Basic admin view file
      $file  = '<?php' . "\n";
      $file .= 'class View_' . $module_upper . ' {' . "\n\n";
      $file .= "\t\t" . 'function admin() {' . "\n";
      $file .= "\t\t\t\t" . 'global $dict;' . "\n";
      $file .= "\t\t\t\t" . '## Include model' . "\n";
      $file .= "\t\t\t\t" . 'App::includeModel(\'models/' . $module_lower . '.php\', \'user\', true);' . "\n";
      $file .= "\t\t\t\t" . '$model = App::initAdminModel(\'' . $module_lower . '\');' . "\n\n";
      $file .= "\t\t\t\t" . 'include_once \'common.php\';' . "\n";
      $file .= "\t\t" . '}' . "\n\n";
      $file .= '}';

    } else {

      // Basic frontend view file
      $file  = '<?php' . "\n";
      $file  .= 'class View_' . $module_upper . ' {' . "\n\n";
      $file  .= "\t\t" . 'function ' . $function . '() {' . "\n";
      $file  .= "\t\t\t\t" . 'global $twig, $dict, $request;' . "\n";
      $file  .= "\t\t\t\t" . '## Include models' . "\n";
      $file  .= "\t\t\t\t" . 'App::includeModel(\'models/example.php\', \'example\');' . "\n\n";
      $file  .= "\t\t\t\t" . '## Add to dictionary' . "\n";
      $file  .= "\t\t\t\t" . '$dict[\'example\'] = App::initModel(\'example\');' . "\n\n";
      $file  .= "\t\t\t\t" . '## Render template' . "\n";
      $file  .= "\t\t\t\t" . 'echo $twig->render(\'' . $view . '.twig\', $dict);' . "\n";
      $file  .= "\t\t" . '}' . "\n\n";
      $file  .= '}';

    }

    $fp = fopen($view_path, 'w');
    fwrite($fp, $file);
    fclose($fp);
    chmod($view_path, 0664);

    App::includeView('views/' . $view . '.php', $function, $admin);
  }

  /**
   * Custom require_once function for model files
   * @static
   *
   * @param      $model_path
   * @param      $model
   * @param bool $admin
   */
  public static function includeModel($model_path, $model, $admin = false) {
    global $view;

    if (!file_exists($model_path)) {
      App::createModel($model_path, $model, $view, $admin);
    } else {
      include_once($model_path);
    }
  }

  /**
   * Creates basic models for admin and frontend if they do not exist
   * @static
   *
   * @param $model_path
   * @param $view
   * @param $admin
   */
  public static function createModel($model_path, $model, $view, $admin) {
    // Strip all but letters and numbers and make lower case then upper case first letter in module name
    $model_lower = strtolower(preg_replace('/[^a-z0-9]/i','', $model));
    $model_upper = ucfirst($model_lower);

    if ($admin) {

      // Basic admin model file
      $file  = '<?php' ."\n\n";
      $file .= 'class Model_' . $model_upper . ' extends RedBean_SimpleModel {' . "\n\n";
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

      // Basic frontend model file
      $file  = '<?php' . "\n";
      $file .= 'class Model_' . $model_upper . ' {' . "\n\n";
      $file .= "\t\t" . 'function ' . $model_lower . '($request) {' . "\n";
      $file .= "\t\t\t\t" . '## Start model dictionary' . "\n";
      $file .= "\t\t\t\t" . '$dict = array();' . "\n";
      $file .= "\t\t\t\t" . '## Add database calls here' . "\n";
      $file .= "\t\t\t\t" . 'return $dict;' . "\n";
      $file .= "\t\t" . '}' . "\n\n";
      $file .= '}';

    }

    $fp = fopen($model_path, 'w');
    fwrite($fp, $file);
    fclose($fp);
    chmod($model_path, 0664);

    App::includeView('views/' . $view . '.php', $admin);
  }

  /**
   * Initialise models and return data
   * @static
   *
   * @param      $model
   * @param null $args
   *
   * @return mixed
   */
  public static function initModel($model, $args = null) {
    $class = 'Model_' . ucfirst($model);
    if (class_exists($class)) {
      $data = new $class;
      return $data->$model($args);
    }
  }

  /**
   * Initialise admin models
   * @static
   *
   * @param $model
   *
   * @return mixed
   */
  public static function initAdminModel($model) {
    $class = 'Model_' . ucfirst($model);
    if (class_exists($class)) {
      return new $class;
    }
  }

  /**
   * Initialise view
   * @static
   *
   * @param $view
   * @param $function
   *
   * @return mixed
   */
  public static function initView($view, $function) {
    $class = 'View_' . ucfirst($view);
    if (class_exists($class)) {
      $data = new $class;
      return $data->$function();
    }
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
    $settings = App::getSettings($class->settings());

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
    $dict = App::parseHexcodes($dict);
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
   */
  public static function parseTabledata($dict) {
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

  /**
   * Find image paths in admin data display tables and convert to thumbnail with modal preview
   * @static
   *
   * @param $dict
   *
   * @return mixed
   */
  public static function parseImages($dict) {
    $replacement = '<a class="thumbnail"><img src="/${0}" style="max-width: 75px; height: auto !important;" /></a>
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
   * Find hex codes in admin data display tables and convert to colour preview
   * @static
   *
   * @param $dict
   *
   * @return mixed
   */
  public static function parseHexcodes($dict) {
    $replacement = '<div class="pull-right" style="background-color: ${0}; width: 25px; height: 25px;"></div> ${0}';
    $i = 0;
    foreach($dict as $key => $value) {
      foreach($value as $key2 => $value2) {
        $test = preg_replace('/^#[a-f0-9]{6}$/i', $replacement, $value2);
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

    if ($fields) {

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

                $where = (isset($field['where'])) ? $field['where'] : null;

                $form[$name]              = array();
                $form[$name]['type']      = $field['type'];
                $form[$name]['label']     = $field['label'];
                $form[$name]['relation']  = $field['relation'];
                $form[$name]['fields']    = array();
                $form[$name]['fields']    = App::getShared($field['model'], $field['selecttitle'], $field['selectimage'], $where);
                $form[$name]['required']  = (isset($field['required']) && $field['required'] === true) ? true : false;
                $form[$name]['one']       = (isset($field['one']) && $field['one'] === true) ? true : false;
                $form[$name]['help']      = (isset($field['help'])) ? $field['help'] : null;
                $form[$name]['onload']    = (isset($field['onload'])) ? $field['onload'] : null;

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
            $form[$key]['onload']     = (isset($field['onload'])) ? $field['onload'] : null;

          } elseif ($field['type'] == 'select') { // File fields

            $form[$key] = array();
            $form[$key]['type']       = $field['type'];
            $form[$key]['label']      = $field['label'];
            $form[$key]['values']     = (is_array($field['values'])) ? $field['values'] : '';
            $form[$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
            $form[$key]['hide']       = (isset($field['table_hide']) && $field['table_hide'] === true) ? true : false;
            $form[$key]['help']       = (isset($field['help'])) ? $field['help'] : null;
            $form[$key]['onload']     = (isset($field['onload'])) ? $field['onload'] : null;

          } elseif ($field['type'] == 'radio') { // Radio fields

            $form[$key] = array();
            $form[$key]['type']       = $field['type'];
            $form[$key]['label']      = $field['label'];
            $form[$key]['values']     = (is_array($field['values'])) ? $field['values'] : '';
            $form[$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
            $form[$key]['hide']       = (isset($field['table_hide']) && $field['table_hide'] === true) ? true : false;
            $form[$key]['help']       = (isset($field['help'])) ? $field['help'] : null;
            $form[$key]['inline']     = (isset($field['inline']) && $field['inline'] === true) ? true : false;
           $form[$key]['readonly']   = (isset($field['readonly']) && $field['readonly'] === true) ? true : false;
            $form[$key]['onload']     = (isset($field['onload'])) ? $field['onload'] : null;

          } elseif ($field['type'] == 'order') { // Order field

            $form[$key] = array();
            $form[$key]['type']       = $field['type'];
            $form[$key]['label']      = $field['label'];
            $form[$key]['values']     = App::orderValues();
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
            $form[$key]['onload']     = (isset($field['onload'])) ? $field['onload'] : null;

          } elseif ($field['type'] == 'separator') { // Separator field

            $form[$key] = array();
            $form[$key]['type']       = $field['type'];
            $form[$key]['label']      = $field['label'];
            $form[$key]['text']       = $field['text'];

          } else { // Own fields

            $form[$key] = array();
            $form[$key]['type']       = $field['type'];
            $form[$key]['label']      = $field['label'];
            $form[$key]['append']     = (isset($field['append'])) ? $field['append'] : null;
            $form[$key]['prepend']    = (isset($field['prepend'])) ? $field['prepend'] : null;
            $form[$key]['maxlength']  = (isset($field['maxlength'])) ? $field['maxlength'] : null;
            $form[$key]['readonly']   = (isset($field['readonly']) && $field['readonly'] === true) ? true : false;
            $form[$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
            $form[$key]['validate']   = (isset($field['validate'])) ? $field['validate'] : false;
            $form[$key]['equalto']    = (isset($field['equalto'])) ? $field['equalto'] : false;
            $form[$key]['hide']       = (isset($field['table_hide']) && $field['table_hide'] === true) ? true : false;
            $form[$key]['help']       = (isset($field['help'])) ? $field['help'] : null;
            $form[$key]['onload']     = (isset($field['onload'])) ? $field['onload'] : null;

          }
        }
      }
    }
    return $form;
  }

  public static function orderValues() {
    global $module;
    require_once realpath(dirname(__FILE__).'/..'). '/models/'.$module.'.php';
    $class = 'Model_' . ucfirst($module);
    $class = new $class;

    $settings = App::getSettings($class->settings());
    $orderby = $settings['orderby'];

    if ($orderby === 'ordering') {
      // Check if item exists and is being edited
      $id = (isset($_GET['id'])) ? $_GET['id'] : 0;

      // Total records. Adds 1 if in "add" view to account for the item being added
      $total = R::getCell('SELECT COUNT(*) FROM ' . $module);
      if (!$total) {
        $total = 0;
      }
      $total = ($id == 0) ? $total + 1 : $total;

      $values = array();
      for ($i = 1; $i <= $total; $i++) {
        $values[$i] = $i;
      }
      return $values;

    } else {
      return;
    }
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
 	 * Takes text with column names embedded between percent-signs for creating dynamic foreign key select titles
 	 * @static
 	 *
 	 * @param      $model
	 * @param      $select
 	 *
 	 * @param null $where
 	 *
 	 * @return array|null
 	 */
  public static function getShared($model, $select, $image = null, $where = null) {

    $data = array();

    $tables = R::$writer->getTables();
    if (in_array($model, $tables)) {
      // Alias the parent table to do sub-queries in condition
      // Alias = first letter of table/model e.g. SELECT * user AS u...
      $alias = ' AS ' . substr($model, 0, 1);
      // Build condition into query if exists
      $condition = ($where) ? ' WHERE '. $where : null;
      $rows = R::getAll( 'SELECT * FROM ' . $model . $alias . $condition );
      $i = 0;
      foreach ($rows AS $row) {
        $data[$i]['id'] = $row['id'];
        if ($image) {
          $data[$i]['selectimage'] = App::createSprintf($image, $row);
        }
        $data[$i]['selecttitle'] = App::createSprintf($select, $row);
        $i++;
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

    if ($fields) {

      $form = array();
      $form['id'] = $id;
      $form['start'] = (isset($_GET['start'])) ? $_GET['start'] : 0;

      //echo '<pre>' . print_r ($fields, true ) . '</pre.'; exit;

      foreach($fields as $key => $field) {

        if ($key != 'add' && $key != 'edit' && $key != 'delete' && $key != 'run' && $key != 'orderby' && $key != 'order') {

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
                $where = (isset($field['where'])) ? $field['where'] : null;

                $form[$name]              = array();
                $form[$name]['type']      = $field['type'];
                $form[$name]['label']     = $field['label'];
                $form[$name]['relation']  = $field['relation'];
                $form[$name]['fields']    = array();
                $form[$name]['fields']    = App::getEditshared($module, $field['model'], $field['selecttitle'], $field['selectimage'], $where, $id);
                $form[$name]['required']  = (isset($field['required']) && $field['required'] === true) ? true : false;
                $form[$name]['one']       = (isset($field['one']) && $field['one'] === true) ? true : false;
                $form[$name]['help']      = (isset($field['help'])) ? $field['help'] : null;
                $form[$name]['onload']    = (isset($field['onload'])) ? $field['onload'] : null;

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
            $form[$key]['onload']     = (isset($field['onload'])) ? $field['onload'] : null;

          } elseif ($field['type'] == 'select') { // File fields

            $form[$key] = array();
            $form[$key]['type']       = $field['type'];
            $form[$key]['label']      = $field['label'];
            $form[$key]['values']     = (is_array($field['values'])) ? $field['values'] : '';
            $form[$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
            $form[$key]['value']      = (isset($data[0][$key])) ? $data[0][$key] : null;
            $form[$key]['help']       = (isset($field['help'])) ? $field['help'] : null;
            $form[$key]['onload']     = (isset($field['onload'])) ? $field['onload'] : null;

          } elseif ($field['type'] == 'radio') { // Radio fields

            $form[$key] = array();
            $form[$key]['type']       = $field['type'];
            $form[$key]['label']      = $field['label'];
            $form[$key]['values']     = (is_array($field['values'])) ? $field['values'] : '';
            $form[$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
            $form[$key]['value']      = (isset($data[0][$key])) ? $data[0][$key] : null;
            $form[$key]['help']       = (isset($field['help'])) ? $field['help'] : null;
            $form[$key]['readonly']   = (isset($field['readonly']) && $field['readonly'] === true) ? true : false;
            $form[$key]['inline']     = (isset($field['inline']) && $field['inline'] === true) ? true : false;
            $form[$key]['onload']     = (isset($field['onload'])) ? $field['onload'] : null;

          } elseif ($field['type'] == 'order') { // Order field

            $form[$key] = array();
            $form[$key]['type']       = $field['type'];
            $form[$key]['label']      = $field['label'];
            $form[$key]['values']     = App::orderValues();
            $form[$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
            $form[$key]['value']      = (isset($data[0][$key])) ? $data[0][$key] : null;
            $form[$key]['help']       = (isset($field['help'])) ? $field['help'] : null;

          } elseif ($field['type'] == 'textarea') { // Textarea fields

            $form[$key] = array();
            $form[$key]['type']       = $field['type'];
            $form[$key]['label']      = $field['label'];
            $form[$key]['maxlength']  = (isset($field['maxlength'])) ? $field['maxlength'] : null;
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
            $form[$key]['onload']     = (isset($field['onload'])) ? $field['onload'] : null;

          } elseif ($field['type'] == 'separator') { // Separator field

            $form[$key] = array();
            $form[$key]['type']       = $field['type'];
            $form[$key]['label']      = $field['label'];
            $form[$key]['text']       = $field['text'];

          } else { // Own fields

            $form[$key] = array();
            $form[$key]['type']       = $field['type'];
            $form[$key]['label']      = $field['label'];
            $form[$key]['append']     = (isset($field['append'])) ? $field['append'] : null;
            $form[$key]['prepend']    = (isset($field['prepend'])) ? $field['prepend'] : null;
            $form[$key]['maxlength']  = (isset($field['maxlength'])) ? $field['maxlength'] : null;
            $form[$key]['readonly']   = (isset($field['readonly']) && $field['readonly'] == true) ? true : false;
            $form[$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
            $form[$key]['validate']   = (isset($field['validate'])) ? $field['validate'] : false;
            $form[$key]['equalto']    = (isset($field['equalto'])) ? $field['equalto'] : false;
            $form[$key]['value']      = (isset($data[0][$key])) ? $data[0][$key] : null;
            $form[$key]['help']       = (isset($field['help'])) ? $field['help'] : null;
            $form[$key]['onload']     = (isset($field['onload'])) ? $field['onload'] : null;

          }
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

    if ($fields) {

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

//    echo '<pre>'. print_r($data, true) . '</pre>';

    $array = array();
    $i = 0;
    foreach ($data as $key => $value) {
      foreach($fields as $key => $field) {

        // echo '<pre>'.print_r($field, true) . '</pre>';

        if ($key != 'add' && $key != 'edit' && $key != 'delete' && $key != 'run' && $key != 'orderby' && $key != 'order') {
          if ($field['type'] != 'foreignkey' && $field['type'] != 'file' && $field['type'] != 'select'&&
              $field['type'] != 'radio' && $field['type'] != 'textarea' && $field['type'] != 'separator') {

            $array[$i][$key] = array();
            $array[$i][$key]['type']       = $field['type'];
            $array[$i][$key]['label']      = $field['label'];
            $array[$i][$key]['append']     = (isset($field['append'])) ? $field['append'] : null;
            $array[$i][$key]['prepend']    = (isset($field['prepend'])) ? $field['prepend'] : null;
            $array[$i][$key]['maxlength']  = (isset($field['maxlength'])) ? $field['maxlength'] : null;
            $array[$i][$key]['readonly']   = (isset($field['readonly']) && $field['readonly'] == true) ? true : false;
            $array[$i][$key]['id']         = $data[$i]['id'];
            $array[$i][$key]['value']      = $data[$i][$key];
            $array[$i][$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
            $array[$i][$key]['validate']   = (isset($field['validate'])) ? $field['validate'] : false;
            $array[$i][$key]['help']       = (isset($field['help'])) ? $field['help'] : null;
            $array[$i][$key]['onload']     = (isset($field['onload'])) ? $field['onload'] : null;

          } elseif ($field['type'] == 'file') {

            $array[$i][$key] = array();
            $array[$i][$key]['type']       = $field['type'];
            $array[$i][$key]['label']      = $field['label'];
            $array[$i][$key]['maxlength']  = (isset($field['maxlength'])) ? $field['maxlength'] : null;
            $array[$i][$key]['id']         = $data[$i]['id'];
            $array[$i][$key]['value']      = (isset($data[$i][$key])) ? $data[$i][$key] : null;
            $array[$i][$key]['isimage']    = (isset($data[$i][$key]) && is_array(@getimagesize(LOCAL_PATH . $data[$i][$key]))) ? true : false;
            $array[$i][$key]['path']       = (isset($field['path'])) ? $field['path'] : false;
            $array[$i][$key]['accept']     = (isset($field['accept'])) ? $field['accept'] : 'gif, jpg, jpeg, png';
            $array[$i][$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
            $array[$i][$key]['validate']   = (isset($field['accept'])) ? $field['accept'] : 'gif,jpg,jpeg,png';
            $array[$i][$key]['help']       = (isset($field['help'])) ? $field['help'] : null;
            $array[$i][$key]['onload']     = (isset($field['onload'])) ? $field['onload'] : null;

          } elseif ($field['type'] == 'select') { // File fields

            $array[$i][$key] = array();
            $array[$i][$key]['type']       = $field['type'];
            $array[$i][$key]['label']      = $field['label'];
            $array[$i][$key]['id']         = $data[$i]['id'];
            $array[$i][$key]['value']      = $data[$i][$key];
            $array[$i][$key]['values']     = (is_array($field['values'])) ? $field['values'] : '';
            $array[$i][$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
            $array[$i][$key]['help']       = (isset($field['help'])) ? $field['help'] : null;
            $array[$i][$key]['onload']     = (isset($field['onload'])) ? $field['onload'] : null;

          } elseif ($field['type'] == 'radio') { // Radio fields

            $array[$i][$key] = array();
            $array[$i][$key]['type']       = $field['type'];
            $array[$i][$key]['label']      = $field['label'];
            $array[$i][$key]['id']         = $data[$i]['id'];
            $array[$i][$key]['value']      = $data[$i][$key];
            $array[$i][$key]['values']     = (is_array($field['values'])) ? $field['values'] : '';
            $array[$i][$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
            $array[$i][$key]['help']       = (isset($field['help'])) ? $field['help'] : null;
						      $array[$i][$key]['readonly']   = (isset($field['readonly']) && $field['readonly'] === true) ? true : false;
            $array[$i][$key]['inline']     = (isset($field['inline']) && $field['inline'] === true) ? true : false;
            $array[$i][$key]['onload']     = (isset($field['onload'])) ? $field['onload'] : null;

          } elseif ($field['type'] == 'textarea') { // Textarea fields

            $array[$i][$key] = array();
            $array[$i][$key]['type']       = $field['type'];
            $array[$i][$key]['label']      = $field['label'];
            $array[$i][$key]['id']         = $data[$i]['id'];

            $array[$i][$key]['maxlength']  = (isset($field['maxlength'])) ? $field['maxlength'] : null;
            $array[$i][$key]['readonly']   = (isset($field['readonly']) && $field['readonly'] == true) ? true : false;
            $array[$i][$key]['required']   = (isset($field['required']) && $field['required'] === true) ? true : false;
            $array[$i][$key]['rich_editor']= true;
            if (isset($field['rich_editor']) && $field['rich_editor'] === true) {
              $array[$i][$key]['rich_editor'] = true;
            } elseif (isset($field['rich_editor']) && $field['rich_editor'] === false) {
              $array[$i][$key]['rich_editor'] = false;
            }
            $array[$i][$key]['value']      = (isset($data[$i][$key])) ? $data[$i][$key] : null;
            $array[$i][$key]['hide']       = (isset($field['table_hide']) && $field['table_hide'] === true) ? true : false;
            $array[$i][$key]['help']       = (isset($field['help'])) ? $field['help'] : null;
            $array[$i][$key]['onload']     = (isset($field['onload'])) ? $field['onload'] : null;

          } elseif ($field['type'] == 'separator') { // Separator field

            $array[$i][$key] = array();
            $array[$i][$key]['type']       = $field['type'];
            $array[$i][$key]['label']      = $field['label'];
            $array[$i][$key]['text']       = $field['text'];

          } elseif ($field['relation'] == 'shared') { // Build into own, shared selection...
            $key = $field['relation'].ucfirst($field['model']);
            $where = (isset($field['where'])) ? $field['where'] : null;

            $array[$i][$key]              = array();
            $array[$i][$key]['type']      = $field['type'];
            $array[$i][$key]['label']     = $field['label'];
            $array[$i][$key]['relation']  = $field['relation'];
            $array[$i][$key]['id']        = $data[$i]['id'];
            $array[$i][$key]['value']     = (isset($data[$i][$key])) ? $data[$i][$key] : null ;
            $array[$i][$key]['fields']    = array();
            $array[$i][$key]['fields']    = App::getEditshared($model, $field['model'], $field['selecttitle'], $field['selectimage'], $where, $data[$i]['id']);
            $array[$i][$key]['required']  = (isset($field['required']) && $field['required'] === true) ? true : false;
            $array[$i][$key]['one']       = (isset($field['one']) && $field['one'] === true) ? true : false;
            $array[$i][$key]['help']      = (isset($field['help'])) ? $field['help'] : null;
            $array[$i][$key]['onload']    = (isset($field['onload'])) ? $field['onload'] : null;
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
  public static function getEditshared($parent, $model, $select, $image = null, $where = null, $id) {

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
      // Alias the parent table to do sub-queries in condition
      // Alias = first letter of table/model e.g. SELECT * user AS u...
      $alias = ' AS ' . substr($model, 0, 1);
      // Build condition into query if exists
      $condition = ($where) ? ' WHERE '. $where : null;
      $rows = R::getAll( 'SELECT * FROM ' . $model . $alias . $condition );
      $i = 0;
      foreach ($rows AS $row) {
        $data[$i]['id'] = $row['id'];
        $data[$i]['selecttitle'] = App::createSprintf($select, $row);
        if ($image) {
          $data[$i]['selectimage'] = App::createSprintf($image, $row);
        }
        $data[$i]['selected'] = (in_array($row['id'], $selected)) ? true : false;
        $i++;
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
  public static function getSettings($settings) {
      $dict = array();

      $dict['add']     = false;
      $dict['edit']    = false;
      $dict['delete']  = false;

      $dict['orderby'] = false;
      $dict['order']   = false;

      $dict['run']     = false;

      if (isset($settings['add']) && $settings['add'] === true) {
        $dict['add']   = 'true';
      } elseif (is_numeric($settings['add'])) {
        $dict['add']   = $settings['add'];
      }

      $dict['edit']    = (isset($settings['edit']) && $settings['edit'] == true) ? true : false;
      $dict['delete']  = (isset($settings['delete']) && $settings['delete'] == true) ? true : false;
      $dict['orderby'] = (isset($settings['orderby'])) ?  $settings['orderby'] : 'id';
      $dict['order']   = (isset($settings['order'])) ?  strtolower($settings['order']) : 'asc';
      $dict['run']['path'] = (isset($settings['run']['path'])) ?  $settings['run']['path'] : false;
      if ($dict['run']['path']) {
        $dict['run']['button']         = (isset($settings['run']['button'])) ?  $settings['run']['button'] : 'Run';
        $dict['run']['button_running'] = (isset($settings['run']['button_running'])) ?  $settings['run']['button_running'] : 'Running...';
      }
    return $dict;
  }

  public static function getGroups() {
    $allclasses = get_declared_classes();

    $classes = preg_grep('/^Model_.*/', $allclasses);
    $classes = array_values($classes);

    $dict = array();

    foreach ($classes AS $class) {
      $new = new $class;
      $module = strtolower(substr($class, 6));
      $dict[$module] = (method_exists($class, 'groups')) ? $new->groups() : null;
    }
    return $dict;
  }

  /**
   * Saves ordering on save
   * @static
   *
   * @param $module
   * @param $order
   */
  public static function saveOrdering($module, $order) {
    // Total records. Adds 1 if in "add" save view to account for the item being added
    $total = R::getCell('SELECT COUNT(*) FROM ' . $module);
    if (!$total) {
      $total = 0;
    }

    if ($total + 1 == $order) {
      // No need to reshuffle if selected order is the new maximum
      return;
    } else {
      // Increment the existing records for the new item to slot into, order-wise
      R::exec('UPDATE `'. $module .'` SET `ordering` = `ordering` + 1 WHERE `ordering` BETWEEN ' . $order . ' AND ' . $total);
    }
  }

  /**
   * Updates ordering on update
   * @static
   *
   * @param $module
   * @param $new_ordering
   */
  public static function updateOrdering($module, $new_ordering) {
    // Total records
    $total = R::getCell('SELECT COUNT(*) FROM ' . $module);
    $id     = (isset($_GET['id'])) ? $_GET['id'] : null;

    $current_ordering = R::getCell('SELECT `ordering` FROM ' . $module . ' WHERE id = ' . $id);

    // Check if the 'ordering' column exists, if not, bail
    if (!empty($current_ordering)) {

      if ($current_ordering == $new_ordering) {
        // No need to reshuffle if selected order is the unchanged
        return;
      } else {
        if ($new_ordering - $current_ordering < 0) {
          // Moved up (negative change)
          R::exec('UPDATE `' . $module . '` SET `ordering` = CASE `id`
                     WHEN ' . $id . ' THEN ' . $new_ordering . '
                     ELSE `ordering` + 1
                     END
                   WHERE `ordering` BETWEEN ' . $new_ordering . ' AND ' . $current_ordering);
        } else {
          // Moved down (positive change)
          R::exec('UPDATE `' . $module . '` SET `ordering` = CASE `id`
                     WHEN ' . $id . ' THEN ' . $new_ordering . '
                     ELSE `ordering` - 1
                     END
                   WHERE `ordering` BETWEEN ' . $current_ordering . ' AND ' . $new_ordering);
        }
      }
    }
  }

  /**
   * Updates the module data view table ordering on drag/drop
   * @static
   *
   */
  public static function dragdropOrdering() {
    $module = $_POST['module'];
    $ordering_array = $_POST['dragdropordering'];

    $when = null;
    $in   = null;

    foreach ($ordering_array AS $id => $ordering) {
      $when .= 'WHEN ' . $id . ' THEN ' . $ordering . ' ';
      $in   .= $id . ',';
    }

    $in = substr($in, 0 , -1);

    R::exec('UPDATE `' . $module . '` SET `ordering` = CASE `id`
                 '. $when .'
                 END
             WHERE id IN (' . $in . ')');
    exit;
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
//		 echo '<pre>' . print_r($_POST, true) . '</pre>';exit;

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

    if (isset($_POST[$module]['ordering'])) {
      App::saveOrdering($module, $_POST[$module]['ordering']);
    }

//    echo '<pre>' . print_r($_POST, true) . '</pre>'; exit;
//    R::debug(1);

    $shared = null;

    foreach ($_POST as $key => $value) {
      if (strlen(strstr($key,'shared'))>0) {
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

//		 echo '<pre>' . print_r($_POST, true) . '</pre>'; exit;

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

    if (isset($_POST[$module]['ordering'])) {
      App::updateOrdering($module, $_POST[$module]['ordering']);
    }

//    echo '<pre>' . print_r($_POST, true) . '</pre>';exit;

    $shared = null;

    foreach ($_POST as $key => $value) {
      if (strlen(strstr($key,'shared'))>0) {
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
      $result = R::getAll('SELECT * FROM `'.$table.'`');
      $count = count($result);

      $return .= '# Dump of table ' .$table. "\n";
      $return .= "# ------------------------------------------------------------\n\n";

      $return .= 'DROP TABLE IF EXISTS `'.$table.'`;';
      $row2 = R::getRow('SHOW CREATE TABLE `'.$table.'`');
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
      echo $twig->render('error-trace.twig', $dict);
      //echo '<pre>' . print_r($dict, true) . '</pre>';
    } else {
      return false;
    }
  }

  /**
   * Function for creating dynamic selecttitles for o2m and m2m relationships in forms
   * @static
   *
   * @param        $str
   * @param        $vars
   * @param string $char
   *
   * @return mixed
   */
  public static function createSprintf($str, $vars, $char = '%') {
    $tmp = array();
    foreach($vars as $k => $v) {
      $tmp[$char . $k . $char] = $v;
    }

    return str_replace(array_keys($tmp), array_values($tmp), $str);
  }

  /**
   * TwigMailer using SwiftMailer - to template html/text emails with Twig!
   * @static
   *
   * @param $to_email
   * @param $to_name
   * @param $from_email
   * @param $from_name
   * @param $identifier
   * @param $parameters
   */
  public static function twigEmail($to_email, $to_name, $from_email, $from_name, $identifier, $parameters) {
    global $twig;
    require_once LOCAL_PATH. 'includes/swiftmailer/swift_required.php';

    // Create the Transport
    $transport = Swift_MailTransport::newInstance();
    // Create the Mailer using your created Transport
    $mailer = Swift_Mailer::newInstance($transport);

    $template = $twig->loadTemplate('email/' . $identifier . '.twig');

    $subject  = $template->renderBlock('subject',  $parameters);
    $bodyhtml = $template->renderBlock('bodyhtml', $parameters);
    $bodytext = $template->renderBlock('bodytext', $parameters);

    // Create the message
    $message = Swift_Message::newInstance()
      ->setSubject($subject)
      ->setFrom(array($from_email => $from_name))
      ->setSender($from_email)
      ->setReplyTo(array($from_email => $from_name))
      ->setTo(array($to_email => $to_name))
      ->setBody($bodytext, 'text/plain')
      ->addPart($bodyhtml, 'text/html');

    $mailer->send($message);
  }

}