<?php
if( !class_exists('Adifier_Fondy') ) {
class Adifier_Fondy{
	/*
	Add stripe options to the theme options
	*/
	static public function register_in_options( $sections ){
        $sections[] = array(
            'title'     => esc_html__('Fondy', 'adifier') ,
            'icon'      => '',
            'subsection'=> true,
            'desc'      => esc_html__('Configure Fondy payment.', 'adifier'),
            'fields'    => array(
                array(
                    'id'        => 'enable_fondy',
                    'type'      => 'select',
                    'options'   => array(
                        'yes'       => esc_html__( 'Yes', 'adifier' ),
                        'no'        => esc_html__( 'No', 'adifier' )
                    ),
                    'title'     => esc_html__('Enable Fondy', 'adifier') ,
                    'desc'      => esc_html__('Enable or disable payment via Fondy', 'adifier'),
                    'default'   => 'no'
                ),
                array(
                    'id' 		=> 'fondy_merchant_id',
                    'type' 		=> 'text',
                    'title' 	=> esc_html__('Merchant ID', 'adifier'),
                    'compiler' 	=> 'true',
                    'desc' 		=> esc_html__('Input your Fondy merchant ID', 'adifier')
                ),
                array(
                    'id' 		=> 'fondy_merchant_password',
                    'type' 		=> 'text',
                    'title' 	=> esc_html__('Merchant Password', 'adifier'),
                    'compiler' 	=> 'true',
                    'desc' 		=> esc_html__('Input your Fondy merchant password', 'adifier'),
                ),
            )
        );

        return $sections;
    }

	/*
	Check payment method
	*/
	static public function start_payment(){
		if( self::is_enabled() ){
            if( !empty( $_GET['adifier_verify_payment'] ) && $_GET['adifier_verify_payment'] == 'fondy' ){
                self::verify_payment();
            }			
			if( !empty( $_GET['screen'] ) && in_array( $_GET['screen'], array( 'ads', 'acc_pay' ) ) ){
				add_action( 'wp_enqueue_scripts', 'Adifier_Fondy::enqueue_scripts' );
				add_action( 'adifier_payment_methods', 'Adifier_Fondy::render' );
			}

			/* this is executed fro the backgriound */
			add_action( 'adifier_refund_fondy', 'Adifier_Fondy::refund', 10, 2 );
			add_filter( 'adifier_payments_dropdown', 'Adifier_Fondy::select_dropdown' );

			add_action( 'wp_ajax_fondy_create_payment', 'Adifier_Fondy::create_payment' );
		}
	}  

	static public function select_dropdown( $dropdown ){
		$dropdown['fondy'] = esc_html__( 'Fondy', 'adifier' );
		return $dropdown;
	}


	/*
	First checn if stripe is configured and enabled
	*/
	static public function is_enabled(){
		$enable_fondy = adifier_get_option( 'enable_fondy' );
		$fondy_merchant_id = adifier_get_option( 'fondy_merchant_id' );
		$fondy_merchant_password = adifier_get_option( 'fondy_merchant_password' );
		if( $enable_fondy == 'yes' && !empty( $fondy_merchant_id ) && !empty( $fondy_merchant_password ) ){
			return true;
		}
		else{
			return false;
		}
	}

	/*
	Add required scripts and styles
	*/
	static public function enqueue_scripts(){		
		wp_enqueue_script('adifier-fondy', get_theme_file_uri( '/js/payments/fondy.js' ), array('jquery', 'adifier-purchase'), false, true);
		wp_enqueue_style( 'adifier-fondy', get_theme_file_uri( '/css/payments/fondy.css' ) );
	}

	/*
	Add stripe to the list of the available payments in the frontend
	*/
	static public function render(){
		?>	
		<li>
			<a href="javascript:void(0);" id="fondy-button" data-returnmessage="<?php esc_attr_e( 'You payment is being processed and once it is approved you will receive purchased goods', 'adifier' ) ?>">
				<img src="<?php echo esc_url( get_theme_file_uri( '/images/fondy.png' ) ); ?>" alt="fondy" width="148" height="42">
			</a>
		</li>
		<?php
	}

	/*
	Execute refund
	*/
	static public function refund( $order, $order_transaction_id ){
		$_order_json_details = Adifier_Order::get_order_details( $order->ID );
		$params = array(
			'order_id'			=> $order_transaction_id,
			'merchant_id'		=> adifier_get_option( 'fondy_merchant_id' ),
			'amount'			=> $_order_json_details['price'] * 100,
			'currency'      	=> adifier_get_option( 'currency_abbr' )

		);
		$params['signature'] = self::generate_signature( $params );

		$response = self::http('https://api.fondy.eu/api/reverse/order_id', $params);

		if( !empty( $response->reverse_status ) && in_array( $response->reverse_status, array('created', 'approved') ) ){
			Adifier_Order::mark_as_refunded( $order );
		}
		else{
			set_transient( 
				'adifier_order_notice', 
				array( 
					'errors' => array( $response->error_message ), 
					'notice' => 'error' 
				) 
			);
		}
	}

	/*
	Generate signature
	*/
	static private function generate_signature( $fields ){
		$fondy_merchant_password = adifier_get_option( 'fondy_merchant_password' );
		if( is_array( $fields ) ){
			$fields = array_filter($fields,'strlen');
			ksort($fields);
			$fields = implode("|",$fields);
		}

		$fields = $fondy_merchant_password."|".$fields;

		return sha1( $fields );
	}

	/*
	Return data for payment initiation
	*/
	static public function create_payment(){
		if( !isset( $_REQUEST['adifier_nonce'] ) || !wp_verify_nonce( $_REQUEST['adifier_nonce'], 'adifier_nonce' ) ){
			die();
		}
		$order = Adifier_Order::get_order();
		$order_id = Adifier_Order::create_transient( $order );


		if( !empty( $order['price'] ) ){

			$fondy_merchant_id = adifier_get_option( 'fondy_merchant_id' );
			$fondy_merchant_key = adifier_get_option( 'fondy_merchant_key' );

			$form = '';
			ob_start();
			$fields = array(
				'merchant_id'				=> esc_attr( $fondy_merchant_id ),
				'amount'					=> esc_attr( $order['price'] ) * 100,
				'currency'      			=> adifier_get_option( 'currency_abbr' ),
				'order_desc'      			=> esc_attr( $order_id ),
				'order_id'					=> esc_attr( $order_id ),
				'response_url'				=> $_POST['redirectUrl'].'#fondy-return',
				'server_callback_url'		=> add_query_arg( array('adifier_verify_payment' => 'fondy'), site_url('/') ),
			);

			$fields["signature"] = self::generate_signature( $fields );

			?>
			<form method="post" action="https://api.fondy.eu/api/checkout/redirect/" class="fondy-form">
				<?php

				foreach( $fields as $key => $value ){
					echo '<input name="'.$key.'" value="'.$value.'" type="hidden">';
				}
				?>
			</form>	
			<?php
			$form = ob_get_contents();
			ob_end_clean();

			$response = array(
				'form' => $form
			);		
		}
		else{
			$response['error'] = esc_html__( 'Can not process your request, contact administration', 'adifier' );
		}

		echo json_encode( $response );
		die();		
	}

	/*
	* Print answer for fondy
	*/
	static private function print_answer(){
		echo 'OK';
		die();
	}	

    /*
    Verify payment of fondy
    */
    static public function verify_payment(){
    	if( !empty( $_POST["order_status"] ) && $_POST["order_status"] == 'approved' ){
			$fields = $_POST;
			unset( $fields['response_signature_string'] );
			unset( $fields['signature'] );

			$signature = self::generate_signature( $fields );

			if ( $signature == $_POST['signature'] ){
				Adifier_Order::create_order(array(
					'order_payment_type' 	=> 'fondy',
					'order_transaction_id' 	=> $_POST['order_id'],
					'order_id'				=> $_POST['order_id'],
					'order_paid'			=> 'yes'
				));				
			}

			self::print_answer( 'OK' );
    	}
    	else{
    		self::print_answer( 'OK' );
    	}
	}

	/*
	HTTP calling
	*/
	static public function http( $url, $data = array() ){
		
	    $response = wp_remote_post( $url, array(
	        'method' 		=> 'POST',
	        'timeout' 		=> 45,
	        'redirection' 	=> 0,
	        'httpversion' 	=> '1.0',
	        'blocking' 		=> true,
	        'headers' 		=> array(
				'Content-Type'	=> 'application/json'
			),
	        'body' 			=> json_encode( array( 'request' =>  $data ) ),
	        'cookies' 		=> array()
	    ));

		if ( is_wp_error( $response ) ) {
		} 
		else{
			   $data =  json_decode( $response['body'] );
			   return $data->response;
		}
	}

}
add_filter( 'init', 'Adifier_Fondy::start_payment' );
add_filter( 'adifier_payment_options', 'Adifier_Fondy::register_in_options' );
}
?>