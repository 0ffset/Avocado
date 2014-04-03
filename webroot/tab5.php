<?php 
/**
 * This is an Avocado pagecontroller.
 *
 */
// Include the essential config-file which also creates the $avocado variable with its defaults.
include(__DIR__.'/config.php');

// Add tab specific aside
$avocado['asides'] = <<<EOD
<aside class='aside-box'>
<p>Tab specific aside</p>
</aside>
{$avocado['asides']}
EOD;

// Do it and store it all in variables in the Avocado container.
$avocado['title'] = "Tab 5";

$avocado['main'] = <<<EOD
<article class='main-box'>
<h1>Tab 5</h1>
<p>Content here...</p>
</article>
EOD;

// Finally, leave it all to the rendering phase of Avocado.
include(AVOCADO_THEME_PATH);