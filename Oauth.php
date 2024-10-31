<?php
/*
Plugin Name: OauthClient
Plugin URI: http://wordpress.org/extend/plugins/oauth-client-muloqot/
Author: Bahtiyor Mahsudov, Shukhrat Ermatov
Version: 1.6
Description: OauthClient for muloqot networking sites
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Functions



function oauthm_get_config() {
	global $wpdb;
	return unserialize($wpdb->get_var( "SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'muloqot_oauth_params'" ));
}
function oauthm_set_config($data) {
	global $wpdb;
	$wpdb->insert( $wpdb->prefix.'options', array('option_name' => 'muloqot_oauth_params', 'option_value' => serialize($data)) );
}

function oauthm_get_btn() {

	return sprintf('
		<div class="snow-btn-div">
			<div class="snow-btn">
				<a href="/wp-load.php?action=authenticate&service=muloqot&comment=1">
					<div class="snow-btn-l"></div>
					<div class="snow-btn-c">%s</div>
					<div class="snow-btn-r"></div>
				</a>
			</div>
			<div class="snow-btn-or">
				<div>%s</div>
			</div>
			<div class="snow-btn">
				<a href="/wp-load.php?action=authenticate&service=muloqot&reg=1&comment=1">
					<div class="snow-btn-l"></div>
					<div class="snow-btn-c">%s</div>
					<div class="snow-btn-r"></div>
				</a>
			</div>
			<div class="snow-btn-subtext">%s</div>
		</div>
		',
		__( 'Log In', 'oauthm' ),
		__( ' or ', 'oauthm' ),
		__( 'Register', 'oauthm' ),
		__( 'Right after signing in you will be redirected to publication', 'oauthm' )
	);
}

function oauthm_plugin_menu() {
	add_options_page( 'OAuth2', 'OAuth2', 'manage_options', 'oauth2', 'oauthm_plugin_options' );
}

function oauthm_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	$config = oauthm_get_config();
	if ($config) {
		$client_id = $config['client_id'];
		$client_secret = $config['client_secret'];
	}

	if (isset($_POST['client_id']) && isset($_POST['client_secret'])) {
		$client_id = preg_replace("/[^\w\d]+/", "", $_POST['client_id']);
		$client_secret = preg_replace("/[^\w\d]+/", "", $_POST['client_secret']);

		oauthm_set_config(array('client_id' => $client_id, 'client_secret' => $client_secret));
	}
	?>
	<div class="wrap">
		<h2>OAuth2 Configuration</h2>
		<br/>
		<form action="" method="post">
			<label>
				Client ID: &nbsp;&nbsp;
				<input type="text" name="client_id" value="<?php echo empty($client_id) ? $_SERVER['SERVER_NAME'] : $client_id; ?>" size="20" />
			</label>
			<br/>
			<br/>
			<label>
				Client Secret: &nbsp;&nbsp;
				<input type="text" name="client_secret" value="<?php echo empty($client_secret) ? '' : $client_secret; ?>" size="40" />
			</label>
			<br/>		
			<br/>
			<input type="submit" value="Save Changes" />		
		</form>
	</div>
	<?php
}

function oauthm_admin_bar($content) {
	return ( current_user_can( 'administrator' ) ) ? $content : false;
}
function oauthm_login_url() {
	return '/wp-load.php?action=authenticate&service=muloqot';
}
function oauthm_profile_url() {
	return wp_get_current_user()->user_url;
}

function oauthm_lang_init() {
	load_plugin_textdomain('oauthm', false, 'oauth-client-muloqot');
}




function oauthm_must_login($a) {
	$a['must_log_in'] = oauthm_get_btn();
	return $a;
}

function oauthm_head() {
	echo '<link rel="stylesheet" type="text/css" media="all" href="'.plugin_dir_url( __FILE__ ).'style.css" />';
}



require_once(plugin_dir_path( __FILE__ ).'authenticate.php');


add_filter( 'show_admin_bar' , 'oauthm_admin_bar');
add_filter( 'get_avatar', 'show_avatar', 1, 3);
add_action( 'init', 'oauthm_init' );
add_action( 'init', 'oauthm_lang_init' );

if (strpos($_SERVER['REQUEST_URI'], '/wp-admin') !== 0) {
	add_filter('login_url', 'oauthm_login_url');
	add_filter('get_edit_user_link', 'oauthm_profile_url');
}


add_action( 'admin_menu', 'oauthm_plugin_menu' );
add_filter('comment_form_defaults', 'oauthm_must_login');
add_action( 'wp_head', 'oauthm_head');

