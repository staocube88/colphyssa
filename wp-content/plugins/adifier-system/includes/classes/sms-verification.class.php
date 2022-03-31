<?php
if( !class_exists('Adifier_Twilio') ){
class Adifier_Twilio{

	static public function launch(){
		add_action( 'wp_ajax_send_verification_code', 'Adifier_Twilio::send_verification_code');
		add_action( 'wp_ajax_check_verification_code', 'Adifier_Twilio::check_verification_code');
	}

	static public function is_phone_verification_needed(){
		$enable_phone_verification = adifier_get_option( 'enable_phone_verification' );
		if( $enable_phone_verification == 'yes' && !empty( adifier_get_option( 'twilio_service_sid' ) ) ){
			if( get_user_meta( get_current_user_id(), 'af_phone_verified', true ) == '1' ){
				return false;
			}
			else{
				return true;
			}
		}
		else{
			return false;
		}
	}

	static public function phone_verification_form(){
		$phone = get_user_meta( get_current_user_id(), 'phone', true );
		?>
		<div class="col-sm-6 col-sm-push-3 phone-verification-wrap">
			<div class="white-block">
				<div class="white-block-title">
					<h5><?php esc_html_e( 'You need to verify your phone number before proceeding', 'adifier' ) ?></h5>
				</div>
				<div class="white-block-content">
					<form method="post" action="" class="ajax-form phone-verification" autocomplete="off">
						
						<div class="ajax-form-result"></div>
						<div class="row">
							<div class="col-sm-4">
								<div class="form-group has-feedback">
									<label for="pv_country_code" class="bold"><?php esc_html_e( 'Country Code *', 'adifier' ) ?></label>
									<input type="text" class="form-control" id="pv_country_code" name="pv_country_code" placeholder="<?php esc_attr_e( 'For example: +1', 'adifier' ); ?>"/>
								</div>							
							</div>
							<div class="col-sm-4">
								<div class="form-group has-feedback">
									<label for="pv_phone_number" class="bold"><?php esc_html_e( 'Phone Number *', 'adifier' ) ?></label>
									<input type="text" class="form-control" id="pv_phone_number" name="pv_phone_number" value="<?php echo esc_attr( $phone ) ?>" placeholder="<?php esc_attr_e( 'Without leading zero', 'adifier' ); ?>"/>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group has-feedback">
									<label for="pv_phone_number" class="bold"><?php esc_html_e( 'Verification Mode *', 'adifier' ) ?></label>
									<div class="styled-select">
										<select id="pv_verification_mode" name="pv_verification_mode">
											<option value="sms"><?php esc_html_e( 'SMS Code', 'adifier' ) ?></option>
											<option value="call"><?php esc_html_e( 'Voice Call', 'adifier' ) ?></option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<input type="hidden" value="send_verification_code" name="action" />
						<a href="javascript:;" class="submit-ajax-form af-button" data-callbacktrigger="af-phone-verify-code"><?php esc_html_e( 'Send Code', 'adifier' ) ?> </a>
						<?php wp_nonce_field( 'adifier_nonce', 'adifier_nonce' ); ?>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	static public function send_verification_code(){
		if( !isset( $_REQUEST['adifier_nonce'] ) || !wp_verify_nonce( $_REQUEST['adifier_nonce'], 'adifier_nonce' ) ){
			die();
		}
		if( empty( $_POST['pv_country_code'] ) ){
			$response['message'] = '<div class="alert-error">'.esc_html__( 'Country code is missing.', 'adifier' ).'</div>';
		}
		else if( empty( $_POST['pv_phone_number'] ) ){
			$response['message'] = '<div class="alert-error">'.esc_html__( 'Phone number is missing.', 'adifier' ).'</div>';
		}
		else if( self::_phone_exists() ){
			$response['message'] = '<div class="alert-error">'.esc_html__( 'Phone number is already registered.', 'adifier' ).'</div>';	
		}
		else{
			$data = self::http('Verifications', array(
				'Channel'		=> $_POST['pv_verification_mode'],
				'To'			=> $_POST['pv_country_code'].$_POST['pv_phone_number']
			));
			if( !empty( $data->sid ) ){
				$response['message'] = '';
				ob_start();
				self::_code_verification_form( $data->sid );
				$response['codeform'] = ob_get_contents();
				ob_end_clean();
			}
			else{
				$response['message'] = '<div class="alert-error">'.( !empty( $data->message ) ? $data->message : esc_html__( 'Could not send verification code.', 'adifier' ) ).'</div>';	
			}
		}

		echo json_encode( $response );
		die();
	}

	static private function _phone_exists(){
		global $wpdb;
		$phone = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_value = %s LIMIT 1", $_POST['pv_country_code'].$_POST['pv_phone_number'] ) );
		if( !empty( $phone ) ){
			return true;
		}
		else{
			return false;
		}
	}

	static private function _code_verification_form( $sid ){
		?>
		<div class="white-block">
			<div class="white-block-title">
				<h5><?php esc_html_e( 'Input your verification code', 'adifier' ) ?></h5>
			</div>
			<div class="white-block-content">

				<form method="post" action="" class="ajax-form phone-verification" autocomplete="off">

					<div class="ajax-form-result"><div class="alert-success"><?php $_POST['pv_verification_mode'] == 'sms' ? esc_attr_e( 'Verification code has been sent', 'adifier' ) : esc_attr_e( 'You will receive a phone call shortly', 'adifier' ) ?></div></div>

					<input type="hidden" value="<?php echo esc_attr( $_POST['pv_country_code'] ) ?>" name="pv_country_code" />
					<input type="hidden" value="<?php echo esc_attr( $_POST['pv_phone_number'] ) ?>" name="pv_phone_number" />
					
					<label for="pv_verification_code" class="bold"><?php esc_html_e( 'Verification Code *', 'adifier' ) ?></label>
					<input type="text" class="form-control" id="pv_verification_code" name="pv_verification_code" placeholder="<?php esc_attr_e( 'Input your received verification code', 'adifier' ); ?>"/>
					<input type="hidden" value="check_verification_code" name="action" />
					<input type="hidden" value="<?php echo esc_attr( $sid ) ?>" name="sid">
					<a href="javascript:;" class="submit-ajax-form af-button"><?php esc_html_e( 'Verify', 'adifier' ) ?> </a>
					<?php wp_nonce_field( 'adifier_nonce', 'adifier_nonce' ); ?>
				</form>

				<div class="or-divider"><h6><?php esc_html_e( 'OR', 'adifier' ) ?></h6></div>

				<form method="post" action="" class="ajax-form phone-verification phone-veriofication-send-again" autocomplete="off">
					<input type="hidden" value="<?php echo esc_attr( $_POST['pv_country_code'] ) ?>" name="pv_country_code" />
					<input type="hidden" value="<?php echo esc_attr( $_POST['pv_phone_number'] ) ?>" name="pv_phone_number" />
					<input type="hidden" value="send_verification_code" name="action" />
					<a href="javascript:;" class="submit-ajax-form af-button phone-code-send-again" data-callbacktrigger="af-phone-verify-code"><?php esc_html_e( 'Send Again', 'adifier' ) ?> </a>
					<?php wp_nonce_field( 'adifier_nonce', 'adifier_nonce' ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	static public function check_verification_code(){
		if( !isset( $_REQUEST['adifier_nonce'] ) || !wp_verify_nonce( $_REQUEST['adifier_nonce'], 'adifier_nonce' ) ){
			die();
		}
		if( empty( $_POST['pv_verification_code'] ) ){
			$response['message'] = '<div class="alert-error">'.esc_html__( 'Verification code is missing.', 'adifier' ).'</div>';
		}
		else if( empty( $_POST['pv_country_code'] ) ){
			$response['message'] = '<div class="alert-error">'.esc_html__( 'Country code is missing.', 'adifier' ).'</div>';
		}
		else if( empty( $_POST['pv_phone_number'] ) ){
			$response['message'] = '<div class="alert-error">'.esc_html__( 'Phone number code is missing.', 'adifier' ).'</div>';
		}
		else{
			$data = self::http('VerificationCheck', array(
				'To'	=> $_POST['pv_country_code'].$_POST['pv_phone_number'],
				'Code'	=> $_POST['pv_verification_code']
			), 'POST');

			if( !empty( $data->status ) && $data->status == 'approved' ){
				update_user_meta( get_current_user_id(), 'af_phone_verified', 1 );
				update_user_meta( get_current_user_id(), 'phone', sanitize_text_field( $_POST['pv_country_code'].$_POST['pv_phone_number'] ) );

				$response['message'] = '<div class="alert-success">'.esc_html__( 'Phone number is successfully verified, page will reload now.', 'adifier' ).'</div>';	
				$response['reload'] = true;
			}
			else{
				$response['message'] = '<div class="alert-error">'.( !empty( $data->message ) ? $data->message : esc_html__( 'Phone number verification failed.', 'adifier' ) ).'</div>';	
			}
		}

		echo json_encode( $response );

		die();
	}

	static public function http( $checkpoint, $data, $method = 'POST' ){
	    $response = wp_remote_post( 'https://verify.twilio.com/v2/Services/'.adifier_get_option( 'twilio_service_sid' ).'/'.$checkpoint, array(
	        'method' 		=> $method,
	        'timeout' 		=> 45,
	        'redirection' 	=> 5,
	        'httpversion' 	=> '1.0',
	        'blocking' 		=> true,
	        'headers' 		=> array(
	            'Authorization' 	=> 'Basic '.base64_encode( adifier_get_option( 'twilio_account_sid' ).':'.adifier_get_option( 'twilio_auth_token' ) )
	        ),
	        'body' 			=> $data,
	        'cookies' 		=> array()
	    ));

		if ( is_wp_error( $response ) ) {
		} 
		else{
		   	return json_decode( $response['body']);		   	
		}		
	}
}
if( is_user_logged_in() ){
	Adifier_Twilio::launch();
}
}
?>