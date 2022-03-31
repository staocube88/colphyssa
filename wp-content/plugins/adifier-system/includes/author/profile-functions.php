<?php
/*
* Update account profile data
*/
if( !function_exists('adifier_update_account') ){
function adifier_update_account( $user_sent_id = '' ){
	if( !is_user_logged_in() ){
		return false;
	}
	$enable_phone_verification = adifier_get_option( 'enable_phone_verification' );

	$email 			= isset( $_POST['email'] )			? sanitize_email( $_POST['email'] ) 				: '';
	$first_name 	= isset( $_POST['first_name'] ) 	? sanitize_text_field( $_POST['first_name'] ) 		: '';
	$last_name 		= isset( $_POST['last_name'] ) 		? sanitize_text_field( $_POST['last_name'] ) 		: '';
	$phone 			= isset( $_POST['phone'] ) 			? sanitize_text_field( $_POST['phone'] ) 			: '';
	$hide_phone 	= isset( $_POST['hide_phone'] ) 	? sanitize_text_field( $_POST['hide_phone'] ) 		: '';
	$facebook 		= isset( $_POST['facebook'] ) 		? esc_url_raw( $_POST['facebook'] ) 				: '';
	$twitter 		= isset( $_POST['twitter'] ) 		? esc_url_raw( $_POST['twitter'] ) 					: '';
	$youtube 		= isset( $_POST['youtube'] ) 		? esc_url_raw( $_POST['youtube'] ) 					: '';
	$linkedin 		= isset( $_POST['linkedin'] ) 		? esc_url_raw( $_POST['linkedin'] ) 				: '';
	$instagram 		= isset( $_POST['instagram'] ) 		? esc_url_raw( $_POST['instagram'] ) 				: '';
	$website 		= isset( $_POST['website'] ) 		? esc_url_raw( $_POST['website'] ) 					: '';
	$avatar_id 		= isset( $_POST['avatar_id'] ) 		? sanitize_text_field( $_POST['avatar_id'] ) 		: '';
	$description 	= isset( $_POST['description'] ) 	? sanitize_textarea_field( $_POST['description'] ) 	: '';
	$location_id 	= isset( $_POST['location_id'] ) 	? sanitize_text_field( $_POST['location_id'] ) 		: '';

	$location = array(
		'lat' 			=> isset( $_POST['lat'] ) 			? sanitize_text_field( $_POST['lat'] ) 			: '',
		'long' 			=> isset( $_POST['long'] ) 			? sanitize_text_field( $_POST['long'] ) 		: '',
		'country' 		=> isset( $_POST['country'] ) 		? sanitize_text_field( $_POST['country'] ) 		: '',
		'state' 		=> isset( $_POST['state'] ) 		? sanitize_text_field( $_POST['state'] ) 		: '',
		'city' 			=> isset( $_POST['city'] ) 			? sanitize_text_field( $_POST['city'] ) 		: '',
		'street' 		=> isset( $_POST['street'] ) 		? sanitize_text_field( $_POST['street'] ) 		: ''		
	);

	$use_google_location = adifier_get_option( 'use_google_location' );
	$use_predefined_locations = adifier_get_option( 'use_predefined_locations' );
	$mandatory_fields = adifier_get_option( 'mandatory_fields' );

	if( ( ( empty( $email ) || !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) || ( !empty( $mandatory_fields['first_name'] ) && empty( $first_name ) ) || ( !empty( $mandatory_fields['last_name'] ) && empty( $last_name ) ) || ( !empty( $mandatory_fields['phone'] ) && empty( $phone ) ) || ( $use_predefined_locations == 'yes' && empty( $location_id ) ) ||( $use_google_location == 'yes' && empty( $location['lat'] ) ) ) && empty( $user_sent_id ) ){
		$error = array();
		if( empty( $email ) || !filter_var( $email, FILTER_VALIDATE_EMAIL ) ){
			$error[] = esc_html__( 'Email is invalid', 'adifier' );
		}
		if( !empty( $mandatory_fields['first_name'] ) && empty( $first_name ) ){
			$error[] = esc_html__( 'First name is required', 'adifier' );
		}
		if( !empty( $mandatory_fields['last_name'] ) && empty( $last_name ) ){
			$error[] = esc_html__( 'Last name is required', 'adifier' );
		}
		if( !empty( $mandatory_fields['phone'] ) && empty( $phone ) ){
			$error[] = esc_html__( 'Phone is required', 'adifier' );
		}
		if( $use_google_location == 'yes' && empty( $location['lat'] ) ){
			$error[] = esc_html__( 'Location is required', 'adifier' );
		}
		if( $use_predefined_locations == 'yes' && empty( $location_id ) ){
			$error[] = esc_html__( 'Location from dropdown is required', 'adifier' );	
		}

		$response['message'] = '<div class="alert-error">'.implode( '<br/>', $error ).'</div>';
	}
	else{
		$user_id = !empty( $user_sent_id ) ? $user_sent_id : get_current_user_id();
		wp_update_user(array( 
			'ID' 			=> $user_id,
			'first_name'	=> $first_name,
			'last_name'		=> $last_name,
			'display_name'	=> $first_name.( !empty( $last_name ) ? ' '.$last_name : '' )
		));

		if( !empty( $_FILES['avatar'] ) && !empty( $_FILES['avatar'] ) && $_FILES['avatar']['error'] == 0 ){
			$avatar_id = adifier_handle_image_upload( $_FILES['avatar'] );
			update_user_meta( $user_id, 'avatar_id', $avatar_id);
		}
		else if( !empty( $avatar_id ) ){
			update_user_meta( $user_id, 'avatar_id', $avatar_id);
		}

		$old_phone = get_user_meta( $user_id, 'phone', true );
		if( $old_phone !== $phone && $enable_phone_verification == 'yes'){
			delete_user_meta( $user_id, 'af_phone_verified' );
			$verification_phone_message = '<div class="alert-info">'.sprintf( esc_html__( 'Phone number is changed, you need to verify it in order to be displayed. Click %s', 'adifier' ), '<a href="'.esc_url( add_query_arg( 'screen', 'new', get_author_posts_url( $user_id ) )).'">'.esc_html__( 'here', 'adifier' ).'</a>').'</div>';
		}

		update_user_meta( $user_id, 'phone', $phone );		
		update_user_meta( $user_id, 'hide_phone', $hide_phone );		
		update_user_meta( $user_id, 'facebook', $facebook );
		update_user_meta( $user_id, 'twitter', $twitter );
		update_user_meta( $user_id, 'youtube', $youtube );
		update_user_meta( $user_id, 'linkedin', $linkedin );
		update_user_meta( $user_id, 'instagram', $instagram );
		update_user_meta( $user_id, 'website', $website );
		update_user_meta( $user_id, 'description', $description );

		if( $use_google_location == 'yes' ){
			update_user_meta( $user_id, 'location', $location );
		}

		if( $use_predefined_locations == 'yes' ){
			$location_ids = array();
			if( !empty( $location_id ) ){
				$location_ids = get_ancestors( absint( $location_id ), 'advert-location' );
				$location_ids[] = absint( $location_id );
			}

			update_user_meta( $user_id, 'af_location_id', $location_ids );		
		}
		$user = wp_get_current_user();
		if( $user->user_email != $email && empty( $user_sent_id ) ){
			$hash = md5( time() );
			update_user_meta( $user_id, 'temp_email', $email );
			update_user_meta( $user_id, 'temp_email_hash', $hash );

			$link = add_query_arg( array( 'verification_hash' => $hash, 'login' => $user->user_login ), home_url( '/' ) );
			ob_start();
			include( get_theme_file_path( 'includes/emails/verification.php' ) );
			$message = ob_get_contents();
			ob_end_clean();
		
			adifier_send_mail( $email, esc_html__( 'Email Verification', 'adifier' ), $message );	

			$verification_email_message = '<div class="alert-info">'.esc_html__( 'Verification email is sent to your new email address', 'adifier' ).'</div>';
		}

		$response['message'] = '<div class="alert-success">'.esc_html__( 'Your profile is updated', 'adifier' ).'</div>';

		if( !empty( $verification_phone_message ) ){
			$response['message'] .= $verification_phone_message;
		}

		if( !empty( $verification_email_message ) ){
			$response['message'] .= $verification_email_message;
		}		
		
	}
	if( empty( $user_sent_id ) ){
		echo json_encode( $response );
		die();
	}
}  
add_action( 'wp_ajax_adifier_update_account', 'adifier_update_account' );
}

if( !function_exists('adifier_update_account_ajax') ){
function adifier_update_account_ajax(){
	if( !isset( $_REQUEST['adifier_nonce'] ) || !wp_verify_nonce( $_REQUEST['adifier_nonce'], 'adifier_nonce' ) ) {
		die();
	}
	else{
		adifier_update_account();
	}
}
add_action( 'wp_ajax_adifier_update_account', 'adifier_update_account_ajax' );
}



/*
* Change account password
*/
if( !function_exists('adifier_change_password') ){
function adifier_change_password(){
	if( !isset( $_REQUEST['adifier_nonce'] ) || !wp_verify_nonce( $_REQUEST['adifier_nonce'], 'adifier_nonce' ) ){
		die();
	}
	$old_password 			= isset( $_POST['old_password'] ) 			? sanitize_text_field( $_POST['old_password'] ) 		: '';
	$new_password 			= isset( $_POST['new_password'] ) 			? sanitize_text_field( $_POST['new_password'] ) 		: '';
	$new_password_repeat 	= isset( $_POST['new_password_repeat'] ) 	? sanitize_text_field( $_POST['new_password_repeat'] ) 	: '';
	$password_length 		= adifier_get_option( 'password_length' );
	$password_length 		= empty( $password_length ) ? 6 : $password_length;	

	if( empty( $new_password ) || empty( $new_password_repeat ) || empty( $old_password ) ){
		$error = array();
		if( empty( $new_password ) ){
			$error[] = esc_html__( 'New password is empty', 'adifier' );
		}
		if( empty( $new_password_repeat ) ){
			$error[] = esc_html__( 'New password repeat is empty', 'adifier' );
		}
		if( empty( $old_password ) ){
			$error[] = esc_html__( 'Old password is empty', 'adifier' );
		}

		$message = '<div class="alert-error">'.implode( '<br>', $error ).'</div>';
	}
	else{
		$user = get_user_by( 'ID', get_current_user_id() );
		if( $new_password != $new_password_repeat ){
			$message = '<div class="alert-error">'.esc_html__( 'New password and password repeat do not match', 'adifier' ).'</div>';
		}
		else if( strlen( $new_password ) < $password_length ){
			$message = '<div class="alert-error">'.sprintf( esc_html__( 'Password must be at least %s characters', 'adifier' ), $password_length ).'</div>';
		}	
		else if( adifier_get_option( 'password_strength_test' ) == 'yes' && !preg_match("/^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$/", $new_password) && !preg_match("/^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$/", $new_password) ){
			$message = '<div class="alert-error">'.esc_html__( 'Password is weak', 'adifier' ).'</div>';
		}
		else if( wp_check_password( $old_password, $user->data->user_pass, $user->ID ) === false ){
			$message = '<div class="alert-error">'.esc_html__( 'Old password does not match', 'adifier' ).'</div>';	
		}
		else{
			wp_update_user(array(
				'ID'		=> $user->ID,
				'user_pass'	=> $new_password
			));
			$message = '<div class="alert-success">'.esc_html__( 'Password changed successfully', 'adifier' ).'</div>';	
		}
	}

	echo json_encode(array(
		'message' => $message
	));

	die();
}  
add_action( 'wp_ajax_adifier_change_password', 'adifier_change_password' );
}

/*
* Serve custom avatar or fallback to gravatar
*/
if( !function_exists('adifier_get_avatar') ){
function adifier_get_avatar( $avatar, $id_or_email, $size, $default, $alt ){
	// Determine if we recive an ID or string
	if ( is_numeric( $id_or_email ) ){
		$user_id = (int) $id_or_email;
	}
	elseif ( is_string( $id_or_email ) && ( $user = get_user_by( 'email', $id_or_email ) ) ){
		$user_id = $user->ID;
	}
	elseif ( is_object( $id_or_email ) && ! empty( $id_or_email->user_id ) ){
		$user_id = (int) $id_or_email->user_id;
	}

	if ( empty( $user_id ) ){
		return $avatar;
	}

	$avatar_id = get_user_meta( $user_id, 'avatar_id', true );
	if( !empty( $avatar_id ) ){
		return wp_get_attachment_image( $avatar_id, 'thumbnail' );	
	}
	else{
		$avatar_url = get_user_meta( $user_id, 'adifier_avatar_url', true );
		if( !empty( $avatar_url ) ){
			return '<img src="'.esc_url( $avatar_url ).'" alt="avatar" width="100" height="100">';
		}
		else{
			return $avatar;
		}
	}
}
add_filter( 'get_avatar', 'adifier_get_avatar', 10, 5 );
}

/*
* Get author name
*/
if( !function_exists('adifier_author_name') ){
function adifier_author_name( $author ){
	$show_username_only = adifier_get_option( 'show_username_only' );
	if( $show_username_only == 'yes' || empty( $author->display_name ) ){
		return $author->user_login;
	}
	else{
		return $author->display_name;
	}
}
}

/*
* Update onlinne status
*/ 
if( !function_exists('adifier_online_beacon') ){
function adifier_online_beacon(){
	if( !isset( $_REQUEST['adifier_nonce'] ) || !wp_verify_nonce( $_REQUEST['adifier_nonce'], 'adifier_nonce' ) ){
		die();
	}
	if( is_user_logged_in() ){
		update_user_meta( get_current_user_id(), 'online_status', current_time('timestamp') + 900 );
	}	
}
add_action('wp_ajax_adifier_online_beacon', 'adifier_online_beacon');
}

/*
* Update onlinne status to offline once it is logged out
*/ 
if( !function_exists('adifier_offline_beacon') ){
function adifier_offline_beacon(){
	update_user_meta( get_current_user_id(), 'online_status', 0 );
}
add_action('clear_auth_cookie', 'adifier_offline_beacon');
}


/*
* Get value for online status
*/
if( !function_exists('adifier_is_online') ){
function adifier_is_online( $author_id ){
	$online_status = get_user_meta( $author_id, 'online_status', true );
	return !empty( $online_status ) && $online_status >= current_time('timestamp') ? true : false;
}
}

/*
* Get seller online status
*/
if( !function_exists('adifier_seller_online_status') ){
function adifier_seller_online_status( $author_id ){
	$is_online = adifier_is_online( $author_id );
	return '<div class="online-status '.(  $is_online ? esc_attr( 'online' ) : esc_attr( 'offline' ) ).' flex-wrap flex-start-h"><span></span>'.( $is_online ? esc_html__( 'Online', 'adifier' ) : esc_html__( 'Offline', 'adifier' ) ).'</div>';
}
}

/*
* Add to favorites
*/
if( !function_exists('adifier_add_to_favorites') ){
function adifier_process_favorites(){
	if( !isset( $_REQUEST['adifier_nonce'] ) || !wp_verify_nonce( $_REQUEST['adifier_nonce'], 'adifier_nonce' ) ){
		die();
	}
	if( is_user_logged_in() ){
		$advert_id = !empty( $_POST['advert_id'] ) ? sanitize_text_field( $_POST['advert_id'] ) : '';
		if( !empty( $advert_id ) ){
			$favorites_ads = get_user_meta( get_current_user_id(), 'favorites_ads', true);
			$favorites_ads = empty( $favorites_ads ) ? array() : $favorites_ads;
			if( is_array( $favorites_ads ) && in_array( $advert_id, $favorites_ads ) ){
				$key = array_search( $advert_id, $favorites_ads );
				unset( $favorites_ads[$key] );
				$response['success'] = 'aficon-heart-o';
			}
			else{
				$favorites_ads[] = $advert_id;
				$response['success'] = 'aficon-heart';
			}
			update_user_meta( get_current_user_id(), 'favorites_ads', $favorites_ads );
		}
		else{
			$response['error'] = esc_html__( 'No ad assigned', 'adifier' );	
		}
	}
	else{
		$response['error'] = esc_html__( 'You are not logged in', 'adifier' );
	}

	echo json_encode( $response );
	die();
}
add_action('wp_ajax_adifier_process_favorites', 'adifier_process_favorites');
add_action('wp_ajax_nopriv_adifier_process_favorites', 'adifier_process_favorites');
}

/*
* Get favorites HTML
*/
if( !function_exists('adifier_get_favorites_html') ){
function adifier_get_favorites_html( $extra_class = '' ){
	?>
	<a title="<?php esc_html_e( 'Favorite', 'adifier' ) ?>" href="<?php echo is_user_logged_in() ? esc_attr( 'javascript:void(0);' ) : esc_attr('') ?>" <?php echo is_user_logged_in() ? 'class="process-favorites '.$extra_class.'" data-id="'.esc_attr( get_the_ID() ).'"' : 'class="af-favs '.$extra_class.'" data-toggle="modal" data-target="#login"' ?> >
		<i class="aficon-heart<?php echo adifier_is_favorite() ? esc_attr( '' ) : esc_attr( '-o' ) ?>"></i>
		<span><?php esc_html_e( 'Favorite', 'adifier' ) ?></span>
		<span class="small-icon"><?php esc_html_e( 'Favorite', 'adifier' ) ?></span>
	</a>
	<?php
}
}

/*
* Limit login attempts
*/
if( !function_exists('adifier_is_too_many_logins') ){
function adifier_is_too_many_logins(){
	$max_login_attempts = adifier_get_option( 'max_login_attempts' );
	if( !empty( $max_login_attempts ) ){		
		$login_attempt_block_time = adifier_get_option( 'login_attempt_block_time' );
		$login_attempt_block_time *= 3600;

		$login_blocked = get_transient( 'adifier_login_blocked'.$_SERVER['REMOTE_ADDR'] );
		if( !empty( $login_blocked ) ){
			return sprintf( esc_html__( 'You have tried to many times. Try again in %s', 'adifier' ),  date("H:i:s", $login_blocked - current_time( 'timestamp' )) );
		}
		else{
			$login_attempts = get_transient( 'adifier_login_attempts'.$_SERVER['REMOTE_ADDR'] );
			$login_attempts = !empty( $login_attempts ) ? $login_attempts : 0;
			$login_attempts++;
			set_transient( 'adifier_login_attempts'.$_SERVER['REMOTE_ADDR'], $login_attempts, $login_attempt_block_time );
			if( $login_attempts > $max_login_attempts ){
				set_transient( 'adifier_login_blocked'.$_SERVER['REMOTE_ADDR'], current_time( 'timestamp' ) + $login_attempt_block_time, $login_attempt_block_time );

				return sprintf( esc_html__( 'You have tried to many times. Try again in %s', 'adifier' ), date("H:i:s", $login_attempt_block_time ) );
			}
			else{
				return false;
			}
		}
	}
	else{
		return false;
	}
}
}

/*
* Login 
*/
if( !function_exists('adifier_login') ){
function adifier_login(){
	if( !isset( $_REQUEST['adifier_nonce'] ) || !wp_verify_nonce( $_REQUEST['adifier_nonce'], 'adifier_nonce' ) ){
		die();
	}
	$is_too_many_logins = adifier_is_too_many_logins();
	if( $is_too_many_logins ){
		$response['message'] = '<div class="alert-error">'.$is_too_many_logins.'</div>';
	}
	else{
		$username = !empty( $_POST['log_username'] ) ? trim( $_POST['log_username'] ) : '';	
		$password = !empty( $_POST['log_password'] ) ? trim( $_POST['log_password'] ) : '';
		$redirect = !empty( $_POST['redirect'] ) ? $_POST['redirect'] : '';
	
		if( empty( $username ) || empty( $password ) ){
			$response['message'] = '<div class="alert-error">'.esc_html__( 'Fields marked with * are required.', 'adifier' ).'</div>';
		}
		else{
			if( filter_var( $username, FILTER_VALIDATE_EMAIL ) ){
				$user = get_user_by( 'email', sanitize_email( $username ) );	
			}
			else{
				$user = get_user_by( 'login', sanitize_text_field( $username ) );
			}
			if( $user ){
				$user_active_status = get_user_meta( $user->ID, 'user_active_status', true );
				if( ( !empty( $user->allcaps['edit_posts'] ) && $user->allcaps['edit_posts'] === true ) || $user_active_status == 'active' ){
					$user = wp_signon(array(
						'user_login' 		=> $user->user_login,
						'user_password'		=> $password,
						'remember'			=> true
					));
	
					if( !is_wp_error( $user ) ){
						$response = array(
							'message' 	=> '<div class="alert-success">'.esc_html__( 'You are logged in, wait a second.', 'adifier' ).'</div>'
						);
						if( !empty( $redirect ) ){
							$response['url'] = add_query_arg( 'screen', 'new', get_author_posts_url( $user->ID ) );
						}
						else{
							$response['reload'] = true;
						}
					}
					else{
						$response['message'] = '<div class="alert-error">'.esc_html__( 'Credentials are invalid.', 'adifier' ).'</div>';
					}				
				}
				else{
					$response['message'] = '<div class="alert-error">'.esc_html__( 'Account is not activated yet, check your mail inbox.', 'adifier' ).'</div>';
				}
			}
			else{
				$response['message'] = '<div class="alert-error">'.esc_html__( 'Credentials are invalid.', 'adifier' ).'</div>';
			}
		}		
	}
	
	echo json_encode( $response );
	die();	
}
add_action('wp_ajax_adifier_login', 'adifier_login');
add_action('wp_ajax_nopriv_adifier_login', 'adifier_login');
}

if( !function_exists( 'adifier_logout_inactive' ) ){
function adifier_logout_inactive( $username, $user ){
	if( !is_wp_error( $user ) ){
		$logout = true;
		if( !empty( $user->allcaps['edit_posts'] ) && $user->allcaps['edit_posts'] === true ){
			$logout = false;
		}
		else if( get_user_meta( $user->ID, 'user_active_status', true ) == 'active' ){
			$logout = false;
		}
		if( $logout === true  ){
			wp_logout();
		}
	}
}
add_filter( 'wp_login', 'adifier_logout_inactive', 10, 2 );
}

/*
Send activation  email 
separate function since there is activation  as well
*/
if( !function_exists('adifier_send_activation_email') ){
function adifier_send_activation_email( $hash, $user_login, $email ){
	$link = add_query_arg( array( 'activation_hash' => $hash, 'login' => $user_login ), home_url( '/' ) );
	ob_start();
	include( get_theme_file_path( 'includes/emails/register.php' ) );
	$message = ob_get_contents();
	ob_end_clean();

	return adifier_send_mail( $email, esc_html__( 'Account Registration', 'adifier' ), $message );	
}	
}

/*
Generate lost instructions
*/
if( !function_exists('adifier_register') ){
function adifier_register( $return_response = false ){
	if( !isset( $_REQUEST['adifier_nonce'] ) || !wp_verify_nonce( $_REQUEST['adifier_nonce'], 'adifier_nonce' ) ){
		die();
	}	
	$username 					= !empty( $_POST['reg_username'] ) ? sanitize_text_field( trim( $_POST['reg_username'] ) ) : '';
	$email 						= !empty( $_POST['reg_email'] ) ? sanitize_email( trim( $_POST['reg_email'] ) ) : '';
	$password 					= !empty( $_POST['reg_password'] ) ? trim( $_POST['reg_password'] ) : '';
	$password_confirm 			= !empty( $_POST['reg_r_password'] ) ? trim( $_POST['reg_r_password'] ) : '';
	$limited_email_domains 		= get_site_option( 'limited_email_domains' );
	if( !empty( $limited_email_domains ) ){
		$limited_email_domains 		= array_map( 'strtolower', $limited_email_domains );
	}
	$emaildomain           		= strtolower( substr( $email, 1 + strpos( $email, '@' ) ) );
	$password_length 			= adifier_get_option( 'password_length' );
	$password_length 			= empty( $password_length ) ? 6 : $password_length;
	$resend_activation			= !empty( $_POST['resend_activation'] ) ? true : false;

	$register_terms = adifier_get_option( 'register_terms' );
	if( !empty( $register_terms ) && empty( $_POST['terms'] ) ){
		$response['message'] = '<div class="alert-error">'.esc_html__( 'You must accept terms & conditions.', 'adifier' ).'</div>';
	}
	else if( !adifier_gdpr_given_consent() ){
		$response['message'] = '<div class="alert-error">'.esc_html__( 'You have not given consent.', 'adifier' ).'</div>';
	}
	else if( empty( $username ) || empty( $email ) || empty( $password ) || empty( $password_confirm ) ){
		$response['message'] = '<div class="alert-error">'.esc_html__( 'Fields marked with * are required.', 'adifier' ).'</div>';
	}
	else if( preg_match('/\s/',$username) ){
		$response['message'] = '<div class="alert-error">'.esc_html__( 'Username can not have empty spaces.', 'adifier' ).'</div>';	
	}	
	else if( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
		$response['message'] = '<div class="alert-error">'.esc_html__( 'E-mail address is not valid.', 'adifier' ).'</div>';
	}
    else if ( ( is_multisite() && is_email_address_unsafe( $email ) ) || ( is_array( $limited_email_domains ) && ! empty( $limited_email_domains ) && !in_array( $emaildomain, $limited_email_domains, true ) ) ) {
		$response['message'] = '<div class="alert-error">'.esc_html__( 'Sorry, that email address is not allowed!', 'adifier' ).'</div>';
    }	
	else if( $password !== $password_confirm ){
		$response['message'] = '<div class="alert-error">'.esc_html__( 'Passwords do not match.', 'adifier' ).'</div>';
	}
	else if( strlen( $password ) < $password_length ){
		$response['message'] = '<div class="alert-error">'.sprintf( esc_html__( 'Password must be at least %s characters', 'adifier' ), $password_length ).'</div>';
	}	
	else if( adifier_get_option( 'password_strength_test' ) == 'yes' && !preg_match("/^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$/", $password) && !preg_match("/^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$/", $password) ){
		$response['message'] = '<div class="alert-error">'.esc_html__( 'Password is weak', 'adifier' ).'</div>';
	}
	else if( $resend_activation && email_exists( $email ) ){
		$user = get_user_by( 'email', $email );
		if( $user ){
			$activation_hash = get_user_meta( $user->ID, 'activation_hash', true );
			if( !empty( $activation_hash ) ){
				adifier_send_activation_email( $activation_hash, $user->user_login, $user->user_email );
				$response['message'] = '<div class="alert-success">'.esc_html__( 'Activation email is resent. To resend activation email submit form again.', 'adifier' ).'</div>';
			}
			else{
				$response['message'] = '<div class="alert-error">'.esc_html__( 'Account is activated', 'adifier' ).'</div>';
			}
		}
	}	
	else if( email_exists( $email ) ){
		$response['message'] = '<div class="alert-error">'.esc_html__( 'Email is already registered.', 'adifier' ).'</div>';
	}
	else if( username_exists( $username ) ){
		$response['message'] = '<div class="alert-error">'.esc_html__( 'Username is already registered.', 'adifier' ).'</div>';
	}	
	else{
		$user = wp_insert_user(array(
		    'user_login'  =>  $username,
		    'user_email'  =>  $email,
		    'user_pass'   =>  $password
		));
		if( !is_wp_error( $user ) ){
			$user = get_user_by( 'id', $user );
			$hash = md5( time() );
			update_user_meta( $user->ID, 'activation_hash', $hash );
			update_user_meta( $user->ID, 'user_active_status', 'inactive' );

			adifier_send_activation_email( $hash, $user->user_login, $user->user_email );

			$response['addition_field'] = '<input type="hidden" name="resend_activation" value="1">';
			$response['message'] = '<div class="alert-success">'.esc_html__( 'Account is created, check your mail. To resend activation email submit form again.', 'adifier' ).'</div>';
			$response['user_id'] = $user->ID;
		}
		else{
			$response['message'] = '<div class="alert-error">'.esc_html__( 'Could not create account, try again.', 'adifier' ).'</div>';
		}
	}
	

	if( $return_response ){
		return $response;
	}
	else{
		echo json_encode( $response );	
		die();	
	}
}
add_action('wp_ajax_adifier_register', 'adifier_register');
add_action('wp_ajax_nopriv_adifier_register', 'adifier_register');
}


/*
* Bypass password for loging with activation link
*/
if( !function_exists( 'adifier_allow_activation_login' ) ){
function adifier_allow_activation_login(  $user, $username, $password ){
	return get_user_by( 'login', $username );
}
}

/*
* Confirm account
*/
if( !function_exists('adifier_confirm_email') ){
function adifier_confirm_email(){
	if( !empty( $_GET['activation_hash'] ) && !empty( $_GET['login'] ) && !is_user_logged_in() ){
		$activation_hash = $_GET['activation_hash'];
		$username = $_GET['login'];
		$user = get_user_by( 'login', sanitize_text_field( $username ) );
		if( $user ){
			$activation_hash_stored = get_user_meta( $user->ID, 'activation_hash', true );
			if( $activation_hash == $activation_hash_stored ){
				delete_user_meta( $user->ID, 'activation_hash' );
				update_user_meta( $user->ID, 'user_active_status', 'active' );
				wp_clear_auth_cookie();
				wp_set_current_user( $user->ID );
				wp_set_auth_cookie( $user->ID , true, false);
		
				update_user_caches($user);				
				add_action( 'adifier_activation', 'adifier_activation_info' );
			}
		}
	}
	else if( !empty( $_GET['verification_hash'] ) && !empty( $_GET['login'] ) ){
		$verification_hash = $_GET['verification_hash'];
		$username = $_GET['login'];
		$user = get_user_by( 'login', sanitize_text_field( $username ) );
		if( $user ){
			$temp_email = get_user_meta( $user->ID, 'temp_email', true );
			$temp_email_hash = get_user_meta( $user->ID, 'temp_email_hash', true );
			if( $temp_email_hash == $verification_hash ){
				wp_update_user(array( 
					'ID' 			=> $user->ID,
					'user_email'	=> $temp_email
				));

				add_action( 'adifier_activation', 'adifier_activation_info' );

				delete_user_meta( $user->ID, 'temp_email' );
				delete_user_meta( $user->ID, 'temp_email_hash' );
			}
		}		
	}
}

add_action( 'init', 'adifier_confirm_email' );
}

if( !function_exists( 'adifier_activation_info' ) ){
function adifier_activation_info(){
	?>
	<div class="alert-success alert-header"><?php esc_html_e( 'Thank you for confirming your mail', 'adifier' ) ?><a href="javascript:;" class="dismiss-alert"><i class="aficon-times"></i></a></div>
	<?php
}
}

/*
* Generate lost instructions
*/
if( !function_exists('adifier_recover') ){
function adifier_recover(){
	if( !isset( $_REQUEST['adifier_nonce'] ) || !wp_verify_nonce( $_REQUEST['adifier_nonce'], 'adifier_nonce' ) ){
		die();
	}
	$password = !empty( $_POST['rec_password'] ) ? trim( $_POST['rec_password'] ) : '';
	$password_confirm = !empty( $_POST['rec_r_password'] ) ? trim( $_POST['rec_r_password'] ) : '';
	$username = !empty( $_POST['rec_username'] ) ? sanitize_text_field( trim( $_POST['rec_username'] ) ) : '';
	$hash = !empty( $_POST['rec_hash'] ) ? trim( $_POST['rec_hash'] ) : '';
	$email = !empty( $_POST['rec_email'] ) ? sanitize_email( trim( $_POST['rec_email'] ) ) : '';
	$password_length 			= adifier_get_option( 'password_length' );
	$password_length 			= empty( $password_length ) ? 6 : $password_length;

	if( !empty( $email ) ){
		if( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			$response['message'] = '<div class="alert-error">'.esc_html__( 'E-mail address is not valid.', 'adifier' ).'</div>';
		}
		else{
			$user = get_user_by( 'email', $email );
			if( $user ) {
				$hash = md5( time() );
				update_user_meta( $user->ID, 'recover_hash', $hash );
				$link = add_query_arg( array( 'rec_hash' => $hash, 'login' => $user->user_login ), home_url( '/' ) ).'#recover';
				ob_start();
				include( get_theme_file_path( 'includes/emails/recover.php' ) );
				$message = ob_get_contents();
				ob_end_clean();

				adifier_send_mail( $email, esc_html__( 'Password Recovery', 'adifier' ), $message );

				$response['message'] = '<div class="alert-success">'.esc_html__( 'Check your email for further instructions.', 'adifier' ).'</div>';
			}
			else{
				$response['message'] = '<div class="alert-error">'.esc_html__( 'Email is not registered.', 'adifier' ).'</div>';
			}
		}
	}
	else{
		if( empty( $password ) || empty( $password_confirm ) ){
			$response['message'] = '<div class="alert-error">'.esc_html__( 'Fields marked with * are required.', 'adifier' ).'</div>';
		}
		else if( $password !== $password_confirm ){
			$response['message'] = '<div class="alert-error">'.esc_html__( 'Passwords do not match.', 'adifier' ).'</div>';
		}
		else if( strlen( $password ) < $password_length ){
			$response['message'] = '<div class="alert-error">'.sprintf( esc_html__( 'Password must be at least %s characters', 'adifier' ), $password_length ).'</div>';
		}	
		else if( adifier_get_option( 'password_strength_test' ) == 'yes' && !preg_match("/^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$/", $password) && !preg_match("/^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$/", $password) ){
			$response['message'] = '<div class="alert-error">'.esc_html__( 'Password is weak', 'adifier' ).'</div>';
		}		
		else{
			$user = get_user_by( 'login', $username );
			if( $user ){
				$rec_hash = get_user_meta( $user->ID, 'recover_hash', true );
				if( $rec_hash == $hash ){
					wp_set_password( $password, $user->ID );
					delete_user_meta( $user->ID, 'recover_hash' );
					$response['message'] = '<div class="alert-success">'.esc_html__( 'Password changed successfully.', 'adifier' ).'</div>';
				}
				else{
					$response['message'] = '<div class="alert-error">'.esc_html__( 'Hash does not match.', 'adifier' ).'</div>';
				}
			}
			else{
				$response['message'] = '<div class="alert-error">'.esc_html__( 'Could not update password, try again.', 'adifier' ).'</div>';	
			}
		}
	}
	
	echo json_encode( $response );
	die();	
}
add_action('wp_ajax_nopriv_adifier_recover', 'adifier_recover');
}

/*
* If is own account
*/
if( !function_exists('adifier_is_own_account') ){
function adifier_is_own_account( $author_id = 0 ){
	if( !is_user_logged_in() ){
		return false;
	}
	if( !empty( $author_id ) ){
		return $author_id == get_current_user_id() && empty( $_GET['preview'] ) ? true : false;
	}
	else if( is_author() ){
		$author = adifier_get_author();
		return $author->ID == get_current_user_id() && empty( $_GET['preview'] ) ? true : false;
	}
	else{
		return false;
	}
}
}


/*
* Get data for social profiles
*/
if( !function_exists('adifier_profile_social_links') ){
function adifier_profile_social_links( $author_id ){
	$social = array(
		'facebook'	=> '',
		'twitter'	=> '',
		'youtube'	=> '',
		'linkedin'	=> '',
		'instagram'	=> '',
		'website'	=> ''
	);


	foreach( $social as $key => &$soc ){
		$data = get_user_meta( $author_id, $key, true );
		if( !empty( $data ) ){
			$soc = $data;
		}
		else{
			unset( $social[$key] );
		}
	}

	return $social;
}
}

/*
* Clean location for displaying on public profile or display taxonomy location
*/
if( !function_exists('adifier_show_profile_address') ){
function adifier_show_profile_address( $location, $author_id ){
	$source = adifier_get_location_source( 'profile_location_display' );
	if( $source == 'geo_value' ){
		$address_order = adifier_get_option( 'address_order' );
		if( !empty( $location ) ){
			unset( $location['lat'] );
			unset( $location['long'] );
			if( $address_order == 'back' ){
				$location = array_reverse( $location );
			}
			return array_filter( $location );
		}
	}
	else{
		$list = array();
		$location_ids = get_user_meta( $author_id, 'af_location_id', true );
		if( !empty( $location_ids ) ){
			$locations = get_terms(array(
				'taxonomy'	=> 'advert-location',
				'include'	=> $location_ids,
				'hide_empty'=> false
			));	
			if( !empty( $locations ) ){
				$locations = adifier_taxonomy_hierarchy( $locations );
				$location_ids = adifier_taxonomy_id_name_hierarchy( $locations );
				foreach( $location_ids as $data ){
					$list[] = '<a href="'.esc_url( get_term_link( $data['term_id'] ) ).'">'.$data['name'].'</a>';
				}
			}			
		}
		return $list;
	}
}
}

/*
Get account object
*/
if( !function_exists('adifier_get_author') ){
function adifier_get_author(){
	if( !empty( get_query_var( 'author_name' ) ) ){
		$author = get_user_by( 'slug', get_query_var( 'author_name' ) );
	}
	else{
		$author = get_user_by( 'ID', sanitize_text_field( $_GET['author'] ) );
	}

	return $author;
}
}

/*
* Free ads/time for users after registration
*/
if( !function_exists('adifier_free_adverts_registration') ){
function adifier_free_adverts_registration( $user_id ){
	$account_payment = adifier_get_option( 'account_payment' );
	if( $account_payment == 'packages' ){
		$package_free_ads = adifier_get_option( 'package_free_ads' );
		if( !empty( $package_free_ads ) ){
			update_user_meta( $user_id, 'af_adverts', $package_free_ads );
		}
	}
	else if( $account_payment == 'subscriptions' ){
		$subscription_free_time = adifier_get_option( 'subscription_free_time' );
		if( !empty( $subscription_free_time ) ){
			$temp = explode( '+', $subscription_free_time );
			if( !empty( $temp[1] ) ){
				$time = current_time( 'timestamp' ) + $temp[1]*3600;
			}
			else{
				$time = current_time( 'timestamp' ) + $subscription_free_time*86400;
			}
			update_user_meta( $user_id, 'af_subscribe', $time );
		}
	}
	else if( $account_payment == 'hybrids' ){
		$hybrid_free_stuff = adifier_get_option( 'hybrid_free_stuff' );
			if( !empty( $hybrid_free_stuff ) ){
			$content = explode( '|', $hybrid_free_stuff );
			update_user_meta( $user_id, 'af_adverts', $content[0] );

			$temp = explode( '+', $content[1] );
			if( !empty( $temp[1] ) ){
				$time = current_time( 'timestamp' ) + $temp[1]*3600;
			}
			else{
				$time = current_time( 'timestamp' ) + $content[1]*86400;
			}
			update_user_meta( $user_id, 'af_subscribe', $time );
		}
	}
}
add_action( 'user_register', 'adifier_free_adverts_registration', 10 );
}

/*
* Send logout contact and create account if possible
* If account is created make chat message instead
*/
if( !function_exists('adifier_logout_contact') ){
function adifier_logout_contact(){
	if( !isset( $_REQUEST['adifier_nonce'] ) || !wp_verify_nonce( $_REQUEST['adifier_nonce'], 'adifier_nonce' ) ){
		die();
	}
	$email 			= isset( $_POST['lcf_email'] ) 			? sanitize_email( $_POST['lcf_email'] ) 			: '';
	$advert_id		= isset( $_POST['lcf_ad_id'] ) 			? sanitize_text_field( $_POST['lcf_ad_id'] ) 		: '';
	$name 			= isset( $_POST['lcf_name'] ) 			? sanitize_text_field( $_POST['lcf_name'] )			: '';
	$message 		= isset( $_POST['lcf_message'] ) 		? sanitize_textarea_field( $_POST['lcf_message'] ) 	: '';
	$register 		= isset( $_POST['lcf_register'] ) 		? true 												: false;
	$username 		= isset( $_POST['lcf_username'] ) 		? sanitize_text_field( $_POST['lcf_username'] ) 	: '';
	$password 		= isset( $_POST['lcf_password'] ) 		? $_POST['lcf_password'] 							: '';
	$password_r 	= isset( $_POST['lcf_r_password'] ) 	? $_POST['lcf_r_password'] 							: '';

	$response = array();

	$ad = get_post( $advert_id );
	if( !empty( $ad ) ){
		
		if( !adifier_gdpr_given_consent() ){
			$response['message'] = '<div class="alert-error">'.esc_html__( 'You have not given consent.', 'adifier' ).'</div>';
		}
		else if( (empty( $email ) || !filter_var( $email, FILTER_VALIDATE_EMAIL )) || empty( $name ) || empty( $message ) ){
			$error = array();
			if( empty( $email ) || !filter_var( $email, FILTER_VALIDATE_EMAIL ) ){
				$error[] = esc_html__( 'Email is invalid', 'adifier' );
			}
			if( empty( $name ) ){
				$error[] = esc_html__( 'Name is required', 'adifier' );
			}
			if( empty( $message ) ){
				$error[] = esc_html__( 'Message is required', 'adifier' );	
			}

			$response['message'] = '<div class="alert-error">'.implode( '<br/>', $error ).'</div>';
		}
		else{
			if( $register ){
				$_POST['reg_email'] = $email;
				$_POST['reg_username'] = $username;
				$_POST['reg_password'] = $password;
				$_POST['reg_r_password'] = $password_r;
				$response = adifier_register( true );

				if( !empty( $response['user_id'] ) ){
					$temp = explode( ' ', $name );
					wp_update_user(array(
						'ID'			=> $response['user_id'],
						'first_name'	=> $temp[0],
						'last_name'		=> !empty( $temp[1] ) ? $temp[1] : '',
						'display_name'	=> $name
					));
					Adifier_Conversations::initiate_conversation_write_message(array(
						'advert_id'		=> $advert_id,
						'sender_id'		=> $response['user_id'],
						'recipient_id'	=> $ad->post_author
					), $message);
					$response = str_replace( '</div>', ' '.esc_html__( 'Message sent', 'adifier' ).'</div>', $response );
				}
			}
			else if( email_exists( $email ) ){
				$response['message'] = '<div class="alert-error">'.esc_html__( 'This email is registered, login to send message', 'adifier' ).'</div>';
			}
			else{
				$author_email = get_the_author_meta( 'user_email', $ad->post_author );
				if( $author_email ){
				    $headers = array( "Reply-To: ".esc_attr( $name )." <".esc_attr( $email ).">" );
				    $message .= '<br /><br />-----------<br/>'.esc_html__( 'Question about: ', 'adifier' ).'<a href="'.get_the_permalink( $advert_id ).'" target="_blank">'.get_the_title( $advert_id ).'</a>';
				    $message .= '<br />-----------<br/>'.esc_html__( 'You can reply directly to this email to respond to ', 'adifier' ).$name;
					adifier_send_mail( $author_email, esc_html__( 'New Message', 'adifier' ), $message, $headers );
					$response['message'] = '<div class="alert-success">'.esc_html__( 'Message sent', 'adifier' ).'</div>';
				}
				else{
					$response['message'] = '<div class="alert-error">'.esc_html__( 'Author did not register an email address', 'adifier' ).'</div>';
				}
			}
		}
	}

	echo json_encode( $response );
	die();
}
add_action( 'wp_ajax_adifier_logout_contact', 'adifier_logout_contact' );
add_action( 'wp_ajax_nopriv_adifier_logout_contact', 'adifier_logout_contact' );
}

/*
* Delete account
*/
if( !function_exists('adifier_delete_account') ){
function adifier_delete_account(){
	if( !empty( $_GET['screen'] ) && $_GET['screen'] == 'delete-acc' ){
		$keep_order_on_delete = adifier_get_option( 'keep_order_on_delete' );
		$deactivate_account = adifier_get_option( 'deactivate_account' );
		if( $deactivate_account == 'yes' ){
			if( $keep_order_on_delete == 'yes' ){
			    $args = array (
			        'numberposts' 	=> -1,
			        'post_type' 	=> 'ad-order',
			        'author' 		=> get_current_user_id()
			    );

			    $buyer_location = get_user_meta( get_current_user_id(), 'location', true );
			    $buyer_name = get_the_author_meta( 'display_name', get_current_user_id() );

			    $user_posts = get_posts( $args );
				if( !empty( $user_posts ) ){    
				    foreach ( $user_posts as $user_post ) {
				        update_post_meta( $user_post->ID, 'buyer_location', $buyer_location );
				        update_post_meta( $user_post->ID, 'buyer_name', $buyer_name );
				    }
				}
			}

		    $args = array (
		        'numberposts' 	=> -1,
		        'post_type' 	=> $keep_order_on_delete == 'yes' ? 'advert' : 'any',
		        'author' 		=> get_current_user_id()
		    );
		    $user_posts = get_posts( $args );
		    if( !empty( $user_posts ) ){
			    foreach ( $user_posts as $user_post ) {
			        wp_delete_post( $user_post->ID, true );
			    }
			}

			require_once(ABSPATH.'wp-admin/includes/user.php' );
			wp_delete_user( get_current_user_id(), false );
			wp_redirect( home_url('/') );
			die();
		}
	}
}
add_action( 'init', 'adifier_delete_account' );
}

/*
* Phone HTML for single ad and public profile
*/
if( !function_exists('adifier_phone_html') ){
function adifier_phone_html( $phone ){
	$phone_visibility = adifier_get_option( 'phone_visibility' );
	if( !empty( $phone ) && ( $phone_visibility == 1 || ( $phone_visibility == 2 && is_user_logged_in() ) ) ){
		$last = substr( $phone, -3 );
		?>
		<a href="javascript:void(0);" class="reveal-phone flex-wrap flex-start-h" data-last="<?php echo esc_attr( $last ); ?>">
			<i class="aficon-phone"></i>
			<span class="flex-right">
				<em><?php echo substr( $phone, 0, -3).'XXX'; ?></em>
				<span class="description"><?php esc_html_e( 'Click to reveal phone number', 'adifier' ) ?></span>
			</span>
		</a>
		<?php
	}
}
}

?>