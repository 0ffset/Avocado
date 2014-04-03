<?php
/**
 * Bootstrapping functions, essential and needed for Avocado to work together with some common helpers. 
 *
 */
 
/**
 * Default exception handler.
 *
 */
function myExceptionHandler($exception) {
  echo "Avocado: Uncaught exception: <p>" . $exception->getMessage() . "</p><pre>" . $exception->getTraceAsString(), "</pre>";
}
set_exception_handler('myExceptionHandler');
 
 
/**
 * Autoloader for classes.
 *
 */
function myAutoloader($class) {
  $path = AVOCADO_INSTALL_PATH . "/src/{$class}/{$class}.php";
  if(is_file($path)) {
    include($path);
  }
  else {
    throw new Exception("Classfile '{$class}' does not exists.");
  }
}
spl_autoload_register('myAutoloader');

/**
 * Dump all contents of a variable
 *
 * @param mixed $a as the variable/array/object to dump.
 */
function dump($a) {
	echo '<pre>' . print_r($a, 1) . '</pre>';
}

/*
 * Get current URL
 *
 * @return string containing current URL
 */
function getCurrentUrl() {
  $url = "http";
  $url .= (@$_SERVER["HTTPS"] == "on") ? 's' : '';
  $url .= "://";
  $serverPort = ($_SERVER["SERVER_PORT"] == "80") ? '' :
    (($_SERVER["SERVER_PORT"] == 443 && @$_SERVER["HTTPS"] == "on") ? '' : ":{$_SERVER['SERVER_PORT']}");
  $url .= $_SERVER["SERVER_NAME"] . $serverPort . htmlspecialchars($_SERVER["REQUEST_URI"]);
  return $url;
}

/*
 * Get current query string, and possibly append new variable(s)
 *
 * @return string containing a query string
 */
function generateQueryString($values = array(), $prepend = "?") {
  // parse query string into array
  $queryString = array();
  parse_str($_SERVER['QUERY_STRING'], $queryString);
 
  // Modify the existing query string with new options
  $queryString = array_merge($queryString, $values);
 
  // Return the modified querystring
  return htmlentities($prepend . http_build_query($queryString));
}

/*
 * Generate a "slug" from a string
 *
 * @return string with generated "slug"
 */
function generateSlug($str) {
	$str = mb_strtolower(trim($str));
	$str = str_replace(array('å','ä','ö'), array('ao','ae','oe'), $str);
	$str = preg_replace('/[^a-z0-9-]/', '-', $str);
	$str = trim(preg_replace('/-+/', '-', $str), '-');
	return $str;
}
/**
 * Redirect to another page
 */
function redirect($extra) {
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	header("Location: http://$host$uri/$extra");
	exit;
}