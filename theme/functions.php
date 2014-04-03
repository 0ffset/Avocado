<?php
/**
 * Theme related functions. 
 *
 */
 
/**
 * Get title for the webpage by concatenating page specific title with site-wide title.
 *
 * @param string $title for this page.
 * @return string/null whether the favicon is defined or not.
 */
function get_title($title) {
  global $avocado;
  return $title . (isset($avocado['title_append']) ? $avocado['title_append'] : null);
}

/**
 * Get main navigation bar
 *
 * @param array $navbar contains tabs to be displayed
 * @return string of HTML of the nav bar
 */
function get_navbar($navbar) {
	$html = "<div id='site-navbar'>\n<nav id='site-nav-right' class='{$navbar['class']}'>\n";
	foreach ($navbar['tabs-right'] as $tab) {
		$selected = call_user_func($navbar['callback_selected'], $tab['url']) ? " class='selected'" : "";
		$html .= "<a href='{$tab['url']}'{$selected}><span>{$tab['text']}</span></a>\n";
	}
	$html .= "</nav>\n<nav id='site-nav-left' class='{$navbar['class']}'>\n";
	foreach ($navbar['tabs-left'] as $tab) {
		$selected = call_user_func($navbar['callback_selected'], $tab['url']) ? " class='selected'" : "";
		$html .= "<a href='{$tab['url']}'{$selected}><span>{$tab['text']}</span></a>\n";
	}
	$html .= "</nav>\n</div>\n";
	
	return $html;
}