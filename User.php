<?php
/**
 * Class to create or login a user
 * 
 */
Class OAuth_User {

	public $user_id;
	public $user_login;
	public $user_email;
	public $is_spammer = false;
	public $is_new_user;

	/**
	 * Function to create a User
	 * @param string $username Username
	 * @param string $email Email
	 * @param string $password Password
	 * @return bol whether the user is created successfully or not
	 */
	function oauth_create_user($username, $email = '', $password = false, $display_name = false, $avatar = false, $user_url = false){

		// check if email is alredy in the system
		if( $email != '' && get_user_by('email', $email) )
			return false;
		// fix for empty e-mail
		if( $email == '' && ! defined( 'WP_IMPORTING' ) )
			define('WP_IMPORTING', true);	
		// if the username is alredy take generate a new one
		if ( username_exists( sanitize_user($username, true ) ) ) {
			do {
				$username = $username . '-' . rand();
			}
			while ( username_exists( sanitize_user($username, true ) ) );
		}
		// generate a new password
		if( !$password )
			$password = wp_generate_password ();
		$new_user_id = wp_create_user( sanitize_user($username, true), $password, $email);
		// if the new user is created set the new user as loggedin
		if( $new_user_id ){
			
			$this->user_id = $new_user_id;
			$this->user_email = $email;
			$this->is_new_user = true;

			//future
			/*if($firsname)		update_user_meta($new_user_id, 'first_name', $firsname);
			if($lastname)		update_user_meta($new_user_id, 'last_name', $lastname);
			if($nickname)		update_user_meta($new_user_id, 'nickname', $nickname);
			if($nicename)		update_user_meta($new_user_id, 'user_nicename', $nicename);*/
			if($avatar) 		update_user_meta($new_user_id, 'avatar', $avatar);
			//if($avatar) 		update_user_meta($new_user_id, 'user_url', $user_url);
			wp_update_user(array('ID' => $new_user_id, 'user_url' => $user_url));
			if($display_name)	{
				update_user_meta($new_user_id, 'display_name', $display_name);
				wp_update_user(array('ID' => $new_user_id, 'display_name' => $display_name));
			}
			//echo $display_name; exit();
			if(function_exists('wpmu_welcome_user_notification') && $this->user_email != '')
				wpmu_welcome_user_notification($this->user_id, $password);
			
			if( $this->login_user() )
				return true;
			return false;
		 }

		 return false;
		
	}

	function check_user($email = false, $custom_value = false, $display_name = false, $avatar = false){
		if(!$email && !$custom_value)
			return false;
		
		if($email)
			$user = get_user_by('email', $email);

		if( $custom_value && is_array($custom_value) ){
			foreach ($custom_value as $key => $value) {
				$user = $this->get_user_by_cutom_meta_value($key,$value);
			}
		}			
			
		if($user){
			$this->user_id = $user->ID;
			$this->user_email = $user->user_email;
			$this->user_login = $user->user_login;
			if ($user->display_name != $display_name)
			{
				global $wpdb;
				update_user_meta($user->ID, 'display_name', $display_name);
				wp_update_user(array('ID' => $user->ID, 'display_name' => $display_name));
				$wpdb->query( 
					$wpdb->prepare("UPDATE $wpdb->comments SET comment_author = %s WHERE comment_author_email = %s", $display_name, $email)
					);
			}
			if($avatar)	update_user_meta($user->ID, 'avatar', $avatar);
			// ckeck if user is spammer
			if ( 1 == $user->spam){
				$this->is_spammer = true;
				return new WP_Error('invalid_username', __('<strong>ERROR</strong>: Your account has been marked as a spammer.'));
			}
			
			return true;
		}

		return false;
	}

	function login_user(){
		
		if( $this->is_spammer == true )
			return new WP_Error('invalid_username', __('<strong>ERROR</strong>: Your account has been marked as a spammer.'));
			
		if( !$this->user_email && !$this->user_id )
			return false;
			
		wp_set_current_user( $this->user_id );
		wp_set_auth_cookie( $this->user_id, true );
		//do_action( 'wp_login', $this->user_login );
		return true;
	}

	function finish_login($param = false){
		if ($param)
			wp_redirect($param);
		else
			wp_redirect(site_url());
		exit();
	}

	function get_user_by_cutom_meta_value($key, $value){
		global $wpdb;
		$id = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->usermeta} WHERE meta_key = '{$key}' AND meta_value = '{$value}';") );
		if(!$id[0])
			return false;
		$user = get_user_by('id', $id[0]->user_id);
		if($user)
			return $user;
		return false;
	}
}
?>
