<?php
	/*
	 Plugin Name: Freedom of Information
	 Plugin URI:  http://www.bbqiguana.com/tag/wordpress-plugins/
	 Version: 0.2
	 Description: A fun plugin that redacts random parts of your content before it is displayed, just like a government document.
	 Author: Randall Hunt
	 Author URI: http://www.bbqiguana.com/
	 */

$foia_default_find = '/\b(government|ufo|defense|homeland|security|secret service|kennedy|george bush|george w\. bush|bush|karl rove|rove|swine flu|mad cow|h1n1|virus|guantanamo [^ <\.]+|terrorist|terrorism|osama|bin laden|president [^ <\.]+|president|citizens|testing [^ <\.]+|socialis[mt]|communis[mt]|fascis[mt]|nazi|soviet union)\b/i';
$foia_default_replace = '<span style="color:#000;background-color:#000" title="$1">$1</span>';

function foia_filter ($content) {
	$which = get_option('foia_which');
	if ('All'!=$which) {
		$foia_tag = get_option('foia_tagname');
		$tags = get_post_custom_values($foia_tag);
		if('Exclude'==$which)
			if(count($tags)>0) return $content;
		else
			if(count($tags)==0) return $content;
	}
	if ($c=get_option('foia_where')) {
		$catfound = false;
		$catlist = get_the_category();
		foreach ($catlist as $category) {
			if (in_array($category->cat_ID, explode(',', $c)))
				$catfound = true;
		}
		if (!$catfound) return $content;
	}
	$find = foia_getfilter();
	$replace = foia_getreplace();
	return preg_replace($find, $replace, $content);
}

function foia_getfilter () {
	global $foia_default_find;
	$find = get_option('foia_find');
	return ($find ? $find : $foia_default_find);
}

function foia_getreplace () {
	global $foia_default_replace;
	$replace = get_option('foia_replace');
	return ($replace ? $replace : $foia_default_replace);
}

function foia_menu () {
	if ( function_exists('add_options_page') ) {
		add_options_page('Freedom of Information', 'Redacted!', 8, 'foia', 'foia_options');
	}
}

function foia_init () {
	register_setting('foia', 'foia_find');
	register_setting('foia', 'foia_replace');
	register_setting('foia', 'foia_tagname');
	register_setting('foia', 'foia_which');
	register_setting('foia', 'foia_where');
}
	
function foia_install () {
	//add default options
	$find    = get_option('foia_find');
	$replace = get_option('foia_replace');
	$tagname = get_option('foia_tagname');
	$which   = get_option('foia_which');
	$where   = get_option('foia_where');
		
	if(!$find)    update_option('foia_find',    '');
	if(!$replace) update_option('foia_replace', '');
	if(!$tagname) update_option('foia_tagname', 'redact');
	if(!$which)   update_option('foia_which',   'All');
	if(!$where)   update_option('foia_where',   '');
}
	
function foia_options () {
	global $foia_default_find, $foia_default_replace;
	$_cats  = '';
	echo '<div class="wrap">';
	echo '<h2>Freedom of Information!</h2>';
	if ( ($_POST['action']=='update') ) {
		//check_admin_referer('foia_update-action');
		update_option('foia_find',    $_POST['foia_find']);
		update_option('foia_replace', $_POST['foia_replace']);
		update_option('foia_tagname', $_POST['foia_tagname']);
		update_option('foia_which',   $_POST['foia_which']);
		update_option('foia_where',  ($_POST['foia_where'] ) ? implode(',', $_POST['foia_cats'] ) : '');
		echo '<div id="message" class="updated fade" style="background-color:rgb(255,251,204);"><p>Settings updated.</p></div>';
	}
	$find    = get_option('foia_find');
	$replace = get_option('foia_replace');
	$tagname = get_option('foia_tagname');
	$which   = get_option('foia_which');
	$where   = get_option('foia_where');
	echo '<big>Options</big>';
	echo '<form name="foia-options" method="post" action="">';
	settings_fields('foia');
	echo '<table class="form-table"><tbody>';
	echo '<tr valign="top"><td scope="row"><strong>Terms to redact:</strong></th><td>Regular expression syntax<br/><em>Default: '.htmlentities($foia_default_find).'</em><br/>';
	echo '<textarea rows="10" cols="60" name="foia_find">'.htmlentities($find).'</textarea>';
	echo '<p>This is the <em>find</em> part of our <em>find-replace</em>.</p></td></tr>';
	echo '<tr valign="top"><td scope="row"><strong>Redacted STYLE tag:</strong></th><td>HTML syntax<br><em>Default: '.htmlentities($foia_default_replace).'</em><br/>';
	echo '<textarea rows="2" cols="60" name="foia_replace">'.htmlentities($replace).'</textarea><br/>';
	echo '<p>The <em>replace</em> part of our little <em>find-replace</em> scenario.</p></td></tr>';
	echo '<tr valign="top"><th scope="row"><strong>Which content should be filtered?:</th>';
	echo '<td><label for="myradio3"><input id="myradio3" type="radio" name="foia_which" value="All" '.($which!='Exclude'&&$which!='Include'?'checked="checked"':'').' /> All content should be filtered</label><br/>';
	echo '<label for="myradio4"><input id="myradio4" type="radio" name="foia_which" value="Exclude" '.($which=='Exclude'?'checked="checked"':'').' /> Exclude posts with the custom tag:</label><br/> ';
	echo '<label for="myradio5"><input id="myradio5" type="radio" name="foia_which" value="Include" '.($which=='Include'?'checked="checked"':'').' /> Only filter posts marked with the custom tag:</label><br/> ';
	echo 'Custom tag name: <input type="text" size="20" name="foia_tagname" value="'.$tagname.'" /><br/>';
	echo '<p>By default, this plugin will filter all posts.  If that is too heavy-handed for you, however, you can choose to apply it based on a custom tag.</p></td></tr>';
	echo '<tr align="top"><th scope="row"><strong>Apply to these categories:</strong></th>';
	echo '<td><label for="myradio6"><input type="radio" id="myradio6" name="foia_where" value="" '.($where==''?'checked="checked"':'') . ' /> All categories</label><br/>';
	echo '<label for="myradio7"><input type="radio" id="myradio7" name="foia_where" value="Y" ' . ($where!=''?'checked="checked"':'') . ' /> Selected categories</label><br/>';

	$_cats = explode(',', $where);
	$chcount = 0;
	$cats = get_categories();
	foreach ($cats as $cat) {
		$chcount++;
		echo '<label for="mycheck'.$chcount.'"><input type="checkbox" id="mycheck'.$chcount.'" name="foia_cats[]" value="' . $cat->cat_ID . '" '.(in_array($cat->cat_ID, $_cats)?'checked="checked"':'').' /> ' . $cat->cat_name . '</label><br/>';
	}
	echo '</td></tr>';
	echo '</tbody></table>';
	echo '<div class="submit">';
	//echo '<input type="hidden" name="foia_update" value="action" />';
	echo '<input type="submit" name="submit" class="button-primary" value="' . __('Save Changes') . '" />';
	echo '</div>';
	echo '</form>';
	echo '<div class="wrap">';
	echo '<big>Donate</big>';
	echo '<p>If you like this plugin consider donating a small amount to the author using PayPal to support further plugin development.</p>';
	echo '<div align="center"><form name="_xclick" action="https://www.paypal.com/us/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_xclick"><input type="hidden" name="business" value="bbqiguana@gmail.com"><input type="hidden" name="item_name" value="Donations for WP-Externimage Plugin"><input type="hidden" name="currency_code" value="USD"><input type="image" src="http://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="Make payments with PayPal - it\'s fast, free and secure!"></form></div>';
	echo '<p>If you think donating money is somehow impersonal you could also choose items from my <a href="http://www.amazon.com/registry/wishlist/18LMHOMRM49P8/ref=cm_wl_act_vv">Amazon.com wishlist</a>.</p>';
	echo '</div>';
	echo '';
	echo '</div>';
}
	
if ( is_admin() ) { // admin actions
	add_action('admin_menu', 'foia_menu');
	add_action('admin_init', 'foia_init');
}

register_activation_hook(__FILE__, 'foia_install');
add_filter ('the_content', 'foia_filter');

?>