<?php
/**
 * Config-file for Avocado. Change settings here to affect installation.
 */
 
/**
 * Set the error reporting.
 */
error_reporting(-1);              // Report all type of errors
ini_set('display_errors', 1);     // Display all errors 
ini_set('output_buffering', 0);   // Do not buffer outputs, write directly
 
 
/**
 * Define Avocado paths.
 */
define('AVOCADO_INSTALL_PATH', __DIR__ . '/..');
define('AVOCADO_THEME_PATH', AVOCADO_INSTALL_PATH . '/theme/render.php');


/**
 * Include bootstrapping functions.
 */
include(AVOCADO_INSTALL_PATH . '/src/bootstrap.php');
 
 
/**
 * Start the session.
 */
session_name(preg_replace('/[^a-z\d]/i', '', __DIR__));
session_start();


/**
 * Set default time zone
 */
date_default_timezone_set('Europe/Stockholm');

/**
 * Default encoding for mb functions
 */
mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");

/**
 * Create the Avocado variable.
 */
$avocado = array();

/**
 * Site wide settings.
 */
$avocado['lang']         = 'en';
$avocado['title_append'] = ' | Avocado';

/**
 * Theme related settings.
 */
$avocado['stylesheets'] = array('css/style.css');
$avocado['favicon']    = 'favicon.png';

/**
 * Database settings
 */

// Connection settings
$avocado['database'] = array (
	'host' => '',
	'dbname' => '',
	'username' => '',
	'password' => '',
	'driver_options' => array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'")
);

$avocado['database']['dsn'] = "mysql:host={$avocado['database']['host']};dbname={$avocado['database']['dbname']};";

// Instantiate main CDatabase object
$db = new CDatabase($avocado['database']);


/**
 * Settings for JavaScript.
 */
$avocado['javascript_include'] = array();
$avocado['modernizr'] = 'js/modernizr.js';
//$avocado['modernizr'] = null; // To disable Modernizr
$avocado['jquery'] = '//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js';
//$avocado['jquery'] = null; // To disable jQuery

/**
 * Google analytics.
 */
$avocado['google_analytics'] = 'UA-22093351-1'; // Set to null to disable google analytics

/**
 * Recurring content: header, footer, asides
 */
$avocado['header'] = <<<EOD
<img src='img/banner.png' alt='' />
EOD;

$year = date('Y');
$avocado['footer'] = <<<EOD
<footer id='site-footer'>
<p>Footer stuff here...</p>
</footer>
<footer id='copyright-footer'>
	<p>Copyright &copy; {$year} [Your name]</p>
</footer>
EOD;

$avocado['asides'] = <<<EOD
<aside class='aside-box'>
<p>Defaut aside 1</p>
</aside>
<aside class='aside-box'>
<p>Defaut aside 2</p>
</aside>
EOD;

/**
 * The navbar and its tabs
 */

// The navbar
$avocado['navbar'] = array(
  'class' => 'sitenavbar',
  'tabs-left' => array(
    'home'     => array('text' => "Home",     'url' => 'home.php'),
		'tab2'     => array('text' => "Tab 2",    'url' => 'tab2.php'),
		'tab3'     => array('text' => "Tab 3",    'url' => 'tab3.php'),
		'tab4'     => array('text' => "Tab 4",    'url' => 'tab4.php')
  ),
  'tabs-right' => array(
    'tab5'     => array('text' => "Tab 5",    'url' => 'tab5.php'),
		'tab6'     => array('text' => "Tab 6",    'url' => 'tab6.php')
  ),
  'callback_selected' => function($url) {
    if(basename($_SERVER['SCRIPT_FILENAME']) == $url) {
      return true;
    }
  }
);