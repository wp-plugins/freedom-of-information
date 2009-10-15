<?php
	/*
	 Plugin Name: Freedom of Information
	 Plugin URI:  http://www.bbqiguana.com/tag/wordpress-plugins/
	 Version: 0.1
	 Description: A fun plugin that redacts random parts of your content before it is displayed, just like a government document.
	 Author: Randall Hunt
	 Author URI: http://www.bbqiguana.com/
	 */

function foia_filter ($content) {
	$foia = '<span style="color:#000;background-color:#000" title="$1">$1</span>';
	$pattern = '/\b(government|ufo|defense|homeland|security|secret service|kennedy|george bush|george w\. bush|bush|karl rove|rove|swine flu|mad cow|h1n1|virus|guantanamo [^ <\.]+|terrorist|terrorism|osama|bin laden|president [^ <\.]+|president|citizens|testing [^ <\.]+|socialis[mt]|communis[mt]|fascis[mt]|nazi|soviet union)\b/i';
	$content = preg_replace($pattern, $foia, $content);
	return $content;
}

function foia_init () {
	//
}

function foia_install () {
	//
}

add_filter ('the_content', 'foia_filter');

?>