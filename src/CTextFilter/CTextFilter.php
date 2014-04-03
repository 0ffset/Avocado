<?php

/**
 * Class with methods for filtering content.
 */
class CTextFilter
{
	/*
	 * Valid filters
	 */
	private static $validFilters = array(
		'bbcode'   => 'bbcode2html',
		'link'     => 'make_clickable',
		'markdown' => 'markdown',
		'nl2br'    => 'nl2br'
	);
	
	/**
	 * Call each filter.
	 *
	 * @param string $text the text to filter.
	 * @param string $filter as comma separated list of filter.
	 * @return string the formatted text.
	 */
	public static function filter($text, $filter) {
		if (!empty($filter)) {
			// Make an array of the comma separated string $filter
			$filters = preg_replace('/\s/', '', explode(',', $filter));

			$valid = self::$validFilters;
			// For each filter, call its function with the $text as parameter.
			foreach($filters as $f) {
				if(isset($valid[$f])) {
					$text = self::$valid[$f]($text);
				} 
				else {
					throw new Exception("The filter '{$filter}' is not a valid filter string.");
				}
			}
		}		
		return $text;
	}
	
	/**
	 * Insert HTML line breaks before all newlines in a string
	 *
	 * @return string with '<br />' inserted before all newlines (\r\n, \n\r, \n and \r)
	 */
	public static function nl2br($text) {
		return nl2br($text);
	}

	/**
	 * BBCode formatting converting to HTML.
	 *
	 * @param string text The text to be converted.
	 * @return string the formatted text.
	 * @link http://dbwebb.se/coachen/reguljara-uttryck-i-php-ger-bbcode-formattering
	 */
	public static function bbcode2html($text) {
		$search = array( 
			'/\[b\](.*?)\[\/b\]/is', 
			'/\[i\](.*?)\[\/i\]/is', 
			'/\[u\](.*?)\[\/u\]/is', 
			'/\[img\](https?.*?)\[\/img\]/is', 
			'/\[url\](https?.*?)\[\/url\]/is', 
			'/\[url=(https?.*?)\](.*?)\[\/url\]/is' 
			);   
		$replace = array( 
			'<strong>$1</strong>', 
			'<em>$1</em>', 
			'<u>$1</u>', 
			'<img src="$1" />', 
			'<a href="$1">$1</a>', 
			'<a href="$1">$2</a>' 
			);     
		return preg_replace($search, $replace, $text);
	}

	/**
	 * Make clickable links from URLs in text.
	 *
	 * @param string $text the text that should be formatted.
	 * @return string with formatted anchors.
	 * @link http://dbwebb.se/coachen/lat-php-funktion-make-clickable-automatiskt-skapa-klickbara-lankar
	 */
	public static function make_clickable($text) {
		return preg_replace_callback(
			'#\b(?<![href|src]=[\'"])https?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#',
			create_function(
				'$matches',
				'return "<a href=\'{$matches[0]}\'>{$matches[0]}</a>";'
			),
			$text
		);
	}

	/**
	 * Format text according to Markdown syntax.
	 *
	 * @link http://dbwebb.se/coachen/skriv-for-webben-med-markdown-och-formattera-till-html-med-php
	 * @param string $text the text that should be formatted.
	 * @return string as the formatted html-text.
	 */
	public static function markdown($text) {
		require_once(__DIR__ . '/php-markdown/Michelf/Markdown.inc.php'); 
		require_once(__DIR__ . '/php-markdown/Michelf/MarkdownExtra.inc.php'); 
		return \Michelf\MarkdownExtra::defaultTransform($text); 
	}
}