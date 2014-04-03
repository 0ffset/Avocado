<?php

/**
 * A class for processing images on the server side
 *
 */
class CImage
{
	/**
	 * Properties
	 */
	private $imgDirPath;               // Path to the directory where images are stored that are allowed to be processed
	private $cacheDirPath;             // Path to the cache directory (where to save processed images)
	private $imageSourcePath;          // Path to the source image file
	private $imageCachePath;           // Path to the yet to be or already processed image file
	private $imageResource    = null;  // The image resource where image is stored during processing (will thus only have a value if any processing will take place)
	private $imageSourceInfo;          // Meta data about the source image
	private $args;                     // Arguments determining how to process the image (are set by the query string)
	private $verbose          = false; // Determines whether to run the processing in "verbose mode"
	private static $maxWidth  = 2000;  // The maximum width of a processed image (pixels)
	private static $maxHeight = 2000;  // The maximum height of a processed image (pixels)
	
	/**
	 * Constructor, sets all essential property values
   */	 
	public function __construct($imgDirPath, $cacheDirPath) {
		is_dir($imgDirPath) or self::errorMessage('The image dir is not a valid directory.');
		is_writable($cacheDirPath) or self::errorMessage('The cache dir is not a writable directory or is not a valid directory.');
		
		// Set image directory and cache paths
		$this->imgDirPath = $imgDirPath;
		$this->cacheDirPath = $cacheDirPath;
		
		// Get arguments from query string
		$this->args = self::getAndValidateArgs();
		
		// Set absolute path to source image
		$this->imageSourcePath = realpath($this->imgDirPath . $this->args['src']);
		
		// Check if set source image is below the img folder
		substr_compare($this->imgDirPath, $this->imageSourcePath, 0, strlen($this->imgDirPath)) == 0 or self::errorMessage('Security constraint: Source image is not directly below the set image directory.');
	
		// If verbose argument is set, initiate verbose mode
		if ($this->args['verbose']) {
			$this->verbose = true;
			self::initVerbose();
		}
		
		// Get information about the image
		$this->imageSourceInfo = $this->getImageSourceInfo();
		
		// Calculate new dimensions based on the arguments
		$this->calcNewDimensions();
		
		// Set file name for cache image
		$this->imageCachePath = $this->createFileNameForCacheImage();
	}
	

	/**
	 * Process the cache image file (unless there already is one that hasn't been modified) and output it together with a last modified header.
	 *
	 * @param boolean $verbose if verbose mode is on or off.
	 */
	public function processAndOutputImage() {
		$sourceModifiedTime = filemtime($this->imageSourcePath);
		$cacheModifiedTime = is_file($this->imageCachePath) ? filemtime($this->imageCachePath) : null;

		// If there already is a valid image in the cache directory, use that one and don't create a new one (no processing will take place)
		if (!$this->args['ignoreCache'] && is_file($this->imageCachePath) && $sourceModifiedTime < $cacheModifiedTime) {
			if ($this->verbose) { self::verbose("Cache file is valid, output it."); }
		}
		// If there is no valid cache image, process the source image and create a new image in the cache directory
		else {
			if ($this->verbose) { self::verbose("Cache is not valid, process image and create a cached version of it."); }
			
			// Open up the image from file
			$this->imageResource = $this->openImageResource();
			
			// Resize the image (if arguments are true)
			if ($this->args['cropToFit']) {
				if ($this->verbose) { self::verbose("Resizing, crop to fit."); }
				$cropX = round(($this->imageSourceInfo['width'] - $this->args['cropWidth']) / 2);  
				$cropY = round(($this->imageSourceInfo['height'] - $this->args['cropHeight']) / 2);    
				$imageResized = self::createImageKeepTransparency($this->args['newWidth'], $this->args['newHeight']);
				imagecopyresampled($imageResized, $this->imageResource, 0, 0, $cropX, $cropY, $this->args['newWidth'], $this->args['newHeight'], $this->args['cropWidth'], $this->args['cropHeight']);
				$this->imageResource = $imageResized;
			}
			else if (!($this->args['newWidth'] == $this->imageSourceInfo['width'] && $this->args['newHeight'] == $this->imageSourceInfo['height'])) {
				if ($this->verbose) { self::verbose("Resizing, new height and/or width."); }
				$imageResized = self::createImageKeepTransparency($this->args['newWidth'], $this->args['newHeight']);
				imagecopyresampled($imageResized, $this->imageResource, 0, 0, 0, 0, $this->args['newWidth'], $this->args['newHeight'], $this->imageSourceInfo['width'], $this->imageSourceInfo['height']);
				$this->imageResource  = $imageResized;
			}
			
			// Apply sharpen filter (if argument is true)
			if ($this->args['sharpen']) {
				if ($this->verbose) { self::verbose("Making image sharper using imageconvolution()."); }
				$this->imageResource = $this->sharpenImage($this->imageResource);
			}
			
			// Apply darken filter (if argument is true)
			if ($this->args['darken']) {
				if ($this->verbose) { self::verbose("Making image darker using imagefilter()."); }
				$this->imageResource = $this->darkenImage($this->imageResource);
			}

			// Save the cache image
			$this->saveCacheImage();
		}	
	
		$lastModified = filemtime($this->imageCachePath);  
		$gmdate = gmdate("D, d M Y H:i:s", $lastModified);

		if ($this->verbose) {
			self::verbose("Memory peak: " . round(memory_get_peak_usage() /1024/1024) . "M");
			self::verbose("Memory limit: " . ini_get('memory_limit'));
			self::verbose("Time is {$gmdate} GMT.");
		}

		if (!$this->verbose) { header('Last-Modified: ' . $gmdate . ' GMT'); }
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified){
			if ($this->verbose) { self::verbose("Would send header 304 Not Modified, but its verbose mode."); exit; }
			header('HTTP/1.0 304 Not Modified');
		} else {  
			if ($this->verbose) { self::verbose("Would send header to deliver image with modified time: {$gmdate} GMT, but its verbose mode."); exit; }
			header('Content-type: ' . $this->imageSourceInfo['mime']);  
			readfile($this->imageCachePath);
		}
		exit;
	}
	
	/**
	 * Get and validate arguments from $_GET variable
	 *
	 * @return array containing all arguments
	 */
	 private static function getAndValidateArgs() {
		$args['src']         = isset($_GET['src'])         ? $_GET['src']                  : null;
		$args['verbose']     = isset($_GET['verbose'])     ? true                          : null;
		$args['saveAs']      = isset($_GET['save-as'])     ? strtolower($_GET['save-as'])  : null;
		$args['quality']     = isset($_GET['quality'])     ? $_GET['quality']              : 60;
		$args['ignoreCache'] = isset($_GET['no-cache'])    ? true                          : null;
		$args['newWidth']    = isset($_GET['width'])       ? $_GET['width']                : null;
		$args['newHeight']   = isset($_GET['height'])      ? $_GET['height']               : null;
		$args['cropToFit']   = isset($_GET['crop-to-fit']) ? true                          : null;
		$args['sharpen']     = isset($_GET['sharpen'])     ? true                          : null;
		$args['darken']      = isset($_GET['darken'])      ? true                          : null;
		
		// Validate arguments
		isset($args['src']) or self::errorMessage('Must set src-attribute.');
		preg_match('#^[a-z0-9A-Z-_\.\/]+$#', $args['src']) or self::errorMessage('Filename contains invalid characters.');
		is_null($args['saveAs']) or in_array($args['saveAs'], array('png', 'jpg', 'jpeg')) or self::errorMessage('Not a valid extension to save image as');
		is_null($args['quality']) or (is_numeric($args['quality']) and $args['quality'] > 0 and $args['quality'] <= 100) or self::errorMessage('Quality out of range');
		is_null($args['newWidth']) or (is_numeric($args['newWidth']) and $args['newWidth'] > 0 and $args['newWidth'] <= self::$maxWidth) or self::errorMessage('Width out of range');
		is_null($args['newHeight']) or (is_numeric($args['newHeight']) and $args['newHeight'] > 0 and $args['newHeight'] <= self::$maxHeight) or self::errorMessage('Height out of range');
		is_null($args['cropToFit']) or ($args['cropToFit'] and $args['newWidth'] and $args['newHeight']) or self::errorMessage('Crop to fit needs both width and height to work');
		
		return $args;
	 }
	 
	 /**
	  * Get info about the source image
		*
		* @return array containing info about source image
		*/
	private function getImageSourceInfo() {
		$imageSourceInfo = list($width, $height, $type, $attr) = getimagesize($this->imageSourcePath);
		!empty($imageSourceInfo) or self::errorMessage("The file doesn't seem to be an image.");
		
		$imageSourceInfo['width']  = $width;
		$imageSourceInfo['height'] = $height;
		$imageSourceInfo['type']   = $type;
		$imageSourceInfo['attr']   = $attr;
		
		for ($n = 0; $n < 4; $n++) { unset($imageSourceInfo[$n]); }
		
		$parts = pathinfo($this->imageSourcePath);
		$imageSourceInfo['fileExtension'] = strtolower($parts['extension']);
		$imageSourceInfo['fileName'] = $parts['filename'];
		
		$imageSourceInfo['fileSize'] = filesize($this->imageSourcePath);
		
		if ($this->verbose) {
			self::verbose("Image file: {$this->imageSourcePath}");
			self::verbose("Image information: " . print_r($imageSourceInfo, true));
			self::verbose("Image width x height (type): {$imageSourceInfo['width']} x {$imageSourceInfo['height']} ({$imageSourceInfo['type']}).");
			self::verbose("Image file size: {$imageSourceInfo['fileSize']} bytes.");
			self::verbose("Image mime type: {$imageSourceInfo['mime']}.");
		}
		
		return $imageSourceInfo;
	}
	
	/**
	 * Open up image from path
	 *
	 * @return resource of image source file
	 */
	private function openImageResource() {
		if ($this->verbose) { self::verbose("File extension is: {$this->imageSourceInfo['fileExtension']}"); }
		
		switch ($this->imageSourceInfo['fileExtension']) {  
			case 'jpg':
			case 'jpeg': 
				$imageResource = imagecreatefromjpeg($this->imageSourcePath);
				if ($this->verbose) { self::verbose("Opened the image as a JPEG image."); }
				break;  
			
			case 'png':  
				$imageResource = imagecreatefrompng($this->imageSourcePath); 
				if ($this->verbose) { self::verbose("Opened the image as a PNG image."); }
				break;  

			default: self::errorMessage("No support for this file extension ({$this->imageSourceInfo['fileExtension']}).");
		}
		
		return $imageResource;
	}
	 
	/**
	 * Creating a file name for the cache image
	 */
	private function createFileNameForCacheImage() {
		$this->args['saveAs'] = is_null($this->args['saveAs'])    ? $this->imageSourceInfo['fileExtension']: $this->args['saveAs'];
		$quality_             = is_null($this->args['quality'])   ? null : "_q{$this->args['quality']}";
		$cropToFit_           = is_null($this->args['cropToFit']) ? null : "_cf";
		$sharpen_             = is_null($this->args['sharpen'])   ? null : "_s";
		$darken_              = is_null($this->args['darken'])    ? null : "_d";
		$dirName              = preg_replace('/\//', '-', dirname($this->args['src']));
		$cacheFileName        = $this->cacheDirPath . "-{$dirName}-{$this->imageSourceInfo['fileName']}_{$this->args['newWidth']}_{$this->args['newHeight']}{$quality_}{$cropToFit_}{$sharpen_}{$darken_}.{$this->args['saveAs']}";
		$cacheFileName        = preg_replace('/^a-zA-Z0-9\.-_/', '', $cacheFileName);

		if ($this->verbose) { self::verbose("Cache file is: {$cacheFileName}"); }
		
		return $cacheFileName;
	}
	
	/**
	 * Save the processed image in cache directory
	 */
	private function saveCacheImage() {
		switch ($this->args['saveAs']) {
			case 'jpeg':
			case 'jpg':
				if ($this->verbose) { self::verbose("Saving image as JPEG to cache using quality = {$this->args['quality']}."); }
				imagejpeg($this->imageResource, $this->imageCachePath, $this->args['quality']);
			break;  
				case 'png':  
				if ($this->verbose) { self::verbose("Saving image as PNG to cache."); }
				imagealphablending($this->imageResource, false);
				imagesavealpha($this->imageResource, true);
				imagepng($this->imageResource, $this->imageCachePath);  
			break;  
				default:
				self::errorMessage("No support to save as file extension '{$this->args['saveAs']}'.");
			break;
		}

		if ($this->verbose) { 
			clearstatcache();
			$cacheFileSize = filesize($this->imageCachePath);
			self::verbose("File size of cached file: {$cacheFileSize} bytes."); 
			self::verbose("Cache file has a file size of " . round($cacheFileSize/$this->imageSourceInfo['fileSize']*100) . "% of the original size.");
		}
	}
	 
	/**
	 * Calculate new width and height for the image (and modifies args['width/height'] accordingly)
	 */
	private function calcNewDimensions() {
		$aspectRatio = $this->imageSourceInfo['width'] / $this->imageSourceInfo['height'];
		if ($this->args['cropToFit'] && $this->args['newWidth'] && $this->args['newHeight']) {
			$targetRatio = $this->args['newWidth'] / $this->args['newHeight'];
			$this->args['cropWidth']   = $targetRatio > $aspectRatio ? $this->imageSourceInfo['width'] : round($this->imageSourceInfo['height'] * $targetRatio);
			$this->args['cropHeight']  = $targetRatio > $aspectRatio ? round($this->imageSourceInfo['width'] / $targetRatio) : $this->imageSourceInfo['height'];
			if ($this->verbose) { self::verbose("Crop to fit into box of {$this->args['newWidth']}x{$this->args['newHeight']}. Cropping dimensions: {$this->args['cropWidth']}x{$this->args['cropHeight']}."); }
		}
		else if ($this->args['newWidth'] && !$this->args['newHeight']) {
			$this->args['newHeight'] = round($this->args['newWidth'] / $aspectRatio);
			if ($this->verbose) { self::verbose("New width is known {$this->args['newWidth']}, height is calculated to {$this->args['newHeight']}."); }
		}
		else if (!$this->args['newWidth'] && $this->args['newHeight']) {
			$this->args['newWidth'] = round($this->args['newHeight'] * $aspectRatio);
			if ($this->verbose) { self::verbose("New height is known {$this->args['newHeight']}, width is calculated to {$this->args['newWidth']}."); }
		}
		else if ($this->args['newWidth'] && $this->args['newHeight']) {
			$ratioWidth  = $this->imageSourceInfo['width']  / $this->args['newWidth'];
			$ratioHeight = $this->imageSourceInfo['height'] / $this->args['newHeight'];
			$ratio = ($ratioWidth > $ratioHeight) ? $ratioWidth : $ratioHeight;
			$this->args['newWidth']  = round($this->imageSourceInfo['width']  / $ratio);
			$this->args['newHeight'] = round($this->imageSourceInfo['height'] / $ratio);
			if ($this->verbose) { self::verbose("New width & height is requested, keeping aspect ratio results in {$this->args['newWidth']}x{$this->args['newHeight']}."); }
		}
		else {
			$this->args['newWidth'] = $this->imageSourceInfo['width'];
			$this->args['newHeight'] = $this->imageSourceInfo['height'];
			if ($this->verbose) { self::verbose("Keeping original width & heigth."); }
		}
	}
	
	/**
	 * Sharpen image as http://php.net/manual/en/ref.image.php#56144
	 * http://loriweb.pair.com/8udf-sharpen.html
	 *
	 * @return resource $image as the processed image.
	 */
	 public static function sharpenImage($image) {
		$matrix = array(
			array(-1,-1,-1),
			array(-1,16,-1),
			array(-1,-1,-1)
		);
		$divisor = 8;
		$offset = 0;
		imageconvolution($image, $matrix, $divisor, $offset);

		return $image;
	}
	
	/**
	 * Make image darker
	 *
	 * @return resource $image as the processed image.
	 */
	 public static function darkenImage($image) {
		imagefilter($image, IMG_FILTER_CONTRAST, 50);
		imagefilter($image, IMG_FILTER_BRIGHTNESS, -100);

		return $image;
	}
	
	/**
	 * Create new image and keep transparency (basically just an extension to imagecreatetruecolor() with alpha channel support)
	 *
	 * @param resource $image the image to apply this filter on.
	 * @return resource $image as the processed image.
	 */
	public static function createImageKeepTransparency($width, $height) {
		$img = imagecreatetruecolor($width, $height);
		imagealphablending($img, false);
		imagesavealpha($img, true);  
		return $img;
	}

	/**
	 * Display error message.
	 *
	 * @param string $message the error message to display.
	 */
	private static function errorMessage($message) {
		header("Status: 404 Not Found");
		die('img.php says 404 - ' . htmlentities($message));
	}
	
	/**
	 * Initialize verbose mode by putting out essential HTML elements
	 */
	private static function initVerbose() {
		$query = array();
		parse_str($_SERVER['QUERY_STRING'], $query);
		unset($query['verbose']);
		$url = '?' . http_build_query($query);

		echo <<<EOD
<html lang='en' style='background-color:#afa'>
<meta charset='UTF-8'/>
<title>img.php verbose mode</title>
<h1>Verbose mode</h1>
<p><a href='{$url}'><code>{$url}</code></a><br>
<img src='{$url}' /></p>
EOD;
	}
	
	/**
	 * Display log message.
	 *
	 * @param string $message the log message to display.
	 */
	public static function verbose($message) {
		echo "<p>{$message}</p>";
	}
}