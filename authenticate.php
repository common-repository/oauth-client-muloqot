<?php

require_once(plugin_dir_path( __FILE__ ).'Oauth_Client.php');

function oauthm_init()
{
	$server = $server_url = 'http://muloqot.uz';
	if (class_exists('Mobile_Detect'))
		$detect = new Mobile_Detect();
	elseif (class_exists('_Mobile_Detect'))
		$detect = new _Mobile_Detect();
	else {
		require_once("Mobile_Detect.php");
		$detect = new _Mobile_Detect();
	}
		
		
		
	if (!empty($detect)) {
		if ($detect->isMobile() && (!$detect->is('AndroidOS') || $detect->is('Opera')) && (!$detect->is('iOS') || $detect->is('Opera'))) {
			$server_url = 'http://m.muloqot.uz'; 
		}
	}
	
	if (isset($_GET['service']) && isset($_GET['action']) && $_GET['service']!='' && $_GET['action']!='')
	{
		$config = oauthm_get_config();
		
		if (!is_array($config)) throw new Exception('OAuth2 configuration is not found');

		$options=array(
			'client_id' => $config['client_id'],
			'client_secret' => $config['client_secret'],
			'base_uri' => $server_url.'/sso/oauth2',
			'authorize_path' => 'authorize',
			'token_path' => 'token',
			'service_path' => 'service'
	  	);
		
		if ($_GET['action']=='authenticate')
		{
			$to_redirect = wp_get_referer();
			if (strpos($to_redirect, 'loggedout')!==false) $to_redirect = '';
			if (!session_id()){
				session_start();
			}
			$_SESSION['redir_url'] = $to_redirect;
			// user data
		  	$params=array(
				'scope'=>'email',
				//'login'=>'1'
		  	);
		  	if (!isset($_GET['reg']))
				$params['login'] = 1;

			if (isset($_GET['comment'])) {
				$_SESSION['redir_url'] = ( ( ( $p = strpos($_SESSION['redir_url'], '#') ) !== false ) ? substr($_SESSION['redir_url'], 0, strpos($_SESSION['redir_url'], '#')) : $_SESSION['redir_url'] ).'#respond';
			}
				
		  	$client=new Oauth2_Client($options);
		  	$params['state']=md5('csrf-protection-'.time());
		  	$client->requestAuthCode($params);
		}
		else if ($_GET['action']=='done') 
		{
			require_once(plugin_dir_path( __FILE__ ).'User.php');
			//OAuth_User
			$params=array(
				'scope'=>'email'
			);
			//create Oauth client object		  
			$client=new Oauth2_Client($options);
			$code = $_GET['code'];
			$params['state'] = $state = $_GET['state'];
			//request token
			$obj = $client->requestAccessToken($code,$params);
			//request user datas
			$json = $client->api($obj->access_token,$params);
			//print_r($json);
			//exit();
			//check email has
			if ($json && isset($json->login) && $json->login!='')
			{
			  	//$userid = $json->username;
			  	$display_name = $json->username;
			  	$name = $json->login;
			  	if ($json->{'avatar.icon'}!='')
			  		$avatar = $server.$json->{'avatar.icon'};
			  	else
			  		$avatar = $server.'/application/modules/User/externals/images/nophoto_user_thumb_icon.png';
			  	$user_url = $server.'/profile/'.$json->login;
				$email = $json->login.'@muloqot.uz';
			  	$create_user = new OAuth_User();
			    if( !$create_user->check_user($email, false, $display_name, $avatar) ){
			        $create_user->oauth_create_user($name, $email, false, $display_name, $avatar,$user_url);
			    } else {
			        $create_user->login_user();
			    }

			    if( is_user_logged_in() ){
			        //$tracknew = '';
			        if (!session_id()){
						session_start();
					}
			        $create_user->finish_login($_SESSION['redir_url']);
			        
			        return true;
			   	} else {
			        echo "There was a problem while logging in. Please close this window and try it again.";
			        return false;
			    }
			}
		}
	}
}

function show_avatar($avatar, $id_or_email, $size)
{
	//print_r($comment);
	//print_r($size);
	//global $comment;
	if(!empty ($id_or_email)) {
	        if ( is_numeric($id_or_email) ) {
	                $user_id = (int) $id_or_email;
	        }
	        elseif ( is_string( $id_or_email ) && ( $user = get_user_by( 'email', $id_or_email ) ) ) {
	                $user_id = $user->ID;
	        }
	        elseif ( is_object( $id_or_email ) && ! empty( $id_or_email->user_id ) ) {
	                $user_id = (int) $id_or_email->user_id;
	        }
	}
	//Check if we are in a comment
	//if (!is_null ($comment) && !empty ($comment->user_id)) {
	     //   $user_id = $comment->user_id;
	
		// Get the thumbnail provided by WordPress Social Login
		if ($user_id) {
		        if (($user_thumbnail = get_user_meta($user_id, 'avatar', true)) !== false) {
		                if (strlen (trim ($user_thumbnail)) > 0) {
		                		$user = get_user_by('id',$user_id);
		                        $user_thumbnail = '<a href="'.$user->user_url.'"><img src="'. $user_thumbnail . '" /></a>';
		                        return $user_thumbnail;
		                }
		        }
		}
	//}
}
?>