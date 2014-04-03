<?php 
/**
 * This is an Avocado pagecontroller.
 *
 */
// Include the essential config-file which also creates the $avocado variable with its defaults.
include(__DIR__.'/config.php'); 

// Do it and store it all in variables in the Avocado container.
$avocado['title'] = "404 Error: Not found";

// Get current URL
$url = getCurrentUrl();

$avocado['main'] = <<<EOD
<article class='main-box'>
<h1>{$avocado['title']}</h1>
<p>This is an Avocado 404. Document <blockquote>{$url}</blockquote> doesn't exist.</p>
</article>
EOD;


// Finally, leave it all to the rendering phase of Avocado.
include(AVOCADO_THEME_PATH);