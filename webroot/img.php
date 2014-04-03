<?php
/**
 * This is a PHP script to process images using PHP GD.
 *
 */
 
// Include the CImage class
include_once('../src/CImage/CImage.php');
 
// Set default time zone
date_default_timezone_set('Europe/Stockholm');

// Ensure error reporting is on
error_reporting(-1);              // Report all type of errors
ini_set('display_errors', 1);     // Display all errors 
ini_set('output_buffering', 0);   // Do not buffer outputs, write directly

// Define img and cache paths
define('IMG_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR);
define('CACHE_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR);

// Instantiate the CImage object
$image = new CImage(IMG_PATH, CACHE_PATH);

//
// Process and output the resulting image
//
$image->processAndOutputImage();