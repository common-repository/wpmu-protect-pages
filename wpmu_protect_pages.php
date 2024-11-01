<?php
/*
Plugin Name: WPMU Protect Pages
Plugin URI: http://www.cagintranet.com/archive/wpmu-protect-pages/
Description: Protect against deletion of page ids in WPMU setup
Version: 1.0
Author: Chris Cagle
Author URI: http://www.cagintranet.com/
*/

/*

Activation steps:
1. Drop in wp-content/mu-plugins/
2. Configure inside 'Site Admin->Protect Pages' screen

*/

add_action('admin_menu','wpmupp_add_admin_page');

function wpmupp_add_admin_page() {
	if (function_exists('add_submenu_page')) {
		add_submenu_page('wpmu-admin.php', 'Protect Pages', 'Protect Pages', 9, 'protectpages', 'wpmupp_options');
	}
}




function wpmupp_options() {
	if ($_REQUEST['submit']) {
     wpmupp_update_options();
	}

	echo '<div class="wrap"><h2>WPMU Protected Pages</h2>';
	wpmupp_form();
	echo '</div>';	
}

function wpmupp_update_options() {
	//http://codex.wordpress.org/Creating_Options_Pages
	$updated = false;

    $good_pp = pp_ec($_REQUEST['pids']);
    $good_pt = pp_ec($_REQUEST['ptext']);
    
    update_site_option('protected_page_ids', $good_pp);
    update_site_option('protected_page_text', $good_pt);
    
    //print_r($good_pc);
		/* process events page */
    $updated = true;

	if ($updated) {
		echo '<div id="message" class="updated fade">';
		echo '<p>'. __('Options successfully saved...'). '</p>';
		echo '</div>';
	} else {
		echo '<div id="message" class="error fade">';
		echo '<p>'. __('Unable to successfully save options...'). '</p>';
		echo '</div>';
	}
}


function wpmupp_form() {

	$pp = get_site_option('protected_page_ids');
	$pt = get_site_option('protected_page_text');
	
	//echo '<pre>';
	//print_r($pp);
	//echo '</pre>';
	
	
	echo '
<style>
	form#wpmupp {margin:0 0 20px 0;}
	form#wpmupp p {margin:0 0 12px 0;}
	p span.hint {display:block;margin:5px 0;font-size:11px;color:#555;width:600px;}
	form#wpmupp label {display:block;font-weight:bold;color:#333;}
	form#wpmupp label span {font-size:11px;color:#666;font-weight:normal;}
</style>
<p>'. _e('Enter a comma-separated list of page ids to protect against deletion across all blogs. This works great with the <a href="http://wpmudev.org/project/blog-templates">WPMU Blog Templates</a> plugin.'). '</p>

<form method="post" id="wpmupp" action="'. $_SERVER ['REQUEST_URI'] .'">
	<p><label for="pids" >'. __('Page IDs: <span>(comma-separated)</span>') .'</label><input name="pids" type="text" id="pids" value="'.pp_cl($pp).'" class="regular-text" style="width:350px;" /></p>
	<p><label for="ptext">'. __('Error Message Text:') .'</label><textarea name="ptext" rows="10" cols="80" >'.pp_cl($pt).'</textarea>
	<span class="hint">'. __('<b>Basic HTML allowed.</b> If this is left blank, it will default to:<span></p>
<pre><code>&lt;h1>&lt;b>Action Denied:&lt;/b> This page is protected...&lt;/h1>
&lt;p>If you want to remove it from your site, change it\'s visibility to &lt;b>PRIVATE&lt;/b> instead.&lt;/p></code></pre>'). '
	</p>
	<p class="submitp" ><input type="submit" class="button-primary" name="submit" value="'. __('Save Options') .'" /></p>
</form>

<p>'. __('Plugin created by <a href="http://www.cagintranet.com/archive/wpmu-protect-pages/" >Cagintranet Web Design</a>.') .'</p>

';

}

function pp_cl( $v ) {
	return stripslashes($v);
}

function pp_ec( $v ) {
  return htmlentities( $v, ENT_QUOTES );
}

/*
 * The meat of the plugin. 
 * Adds the action to protect any page or post via it's ID.
 */
add_action('delete_post', 'pp_protect_page', 1000);

function pp_protect_page($post_ID) {
	$id = $post_ID;
	$pp = get_site_option('protected_page_ids');
	$pt = html_entity_decode(get_site_option('protected_page_text'));
	
	if ($pt == '' || empty($pt)) {
		$pt = '<h1><b>Action Denied:</b> This page is protected...</h1><p>If you want to remove it from your site, change it\'s visibility to <b>PRIVATE</b> instead.</p>';
	}
	$page_ids_to_keep = explode(",",$pp);;
	if ( in_array($id, $page_ids_to_keep) ) {
		wp_die($pt, 'Denied: Protected Page');
	}
}




	