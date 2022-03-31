<?php
/*
* Load theme textdomain
*/
if( is_dir( get_stylesheet_directory() . '/languages' ) ) {
	load_theme_textdomain('adifier', get_stylesheet_directory() . '/languages');
}
else{
	load_theme_textdomain('adifier', get_template_directory() . '/languages');
}

if ( ! isset( $content_width ) ) $content_width = 1200;

/*
* Admin notices
*/

if( !function_exists('adifier_admin_notices') ){
function adifier_admin_notices() {
	$google_api_key = adifier_get_option( 'google_api_key' );
	$map_source = adifier_get_option( 'map_source' );
	if( empty( $google_api_key ) && class_exists('ReduxFramework') && in_array( $map_source, array( 'google', 'mapbox' ) ) ){
	    ?>
	    <div class="notice notice-success is-dismissible error">
	    	<?php if( $map_source == 'google' ): ?>
	        	<p>Generate Google API key like explained <a href="https://developers.google.com/places/web-service/get-api-key" target="_blank">here</a> and place it in Adifier WP -> Ads -> Map API Key</p>
	        <?php elseif( $map_source == 'mapbox' ): ?>
	        	<p>Copy mapbox API key from <a href="https://account.mapbox.com/" target="_blank">here</a> and place it in Adifier WP -> Ads -> Map API Key</p>
	        <?php endif; ?>
	    </div>
	    <?php
	}

	$active_status = get_user_meta( get_current_user_id(), 'user_active_status', true );
	if( $active_status == 'inactive' ){
		?>
	    <div class="notice notice-success error">
	        <p>Your account is set as inactive and your ads will not be visible. To change it Users -> click to edit user -> User Status or click <a href="<?php echo esc_url( get_edit_profile_url() )?>"> here</a></p>
	    </div>		
		<?php
	}

	$enable_phone_verification = adifier_get_option( 'enable_phone_verification' );
	$twilio_service_sid = adifier_get_option( 'twilio_service_sid' );
	if( $enable_phone_verification == 'yes' && empty( $twilio_service_sid ) ){
		?>
	    <div class="notice notice-success error">
	        <p>Phone verification is enabled but tokens are not present or you were using Twilio v1. Go to Adifier WP -> Ads -> Phone Verification and update tokens since we have moved to v2 version</p>
	    </div>		
		<?php
	}	

	if( function_exists('cmb_init') ):
		$smeta_data = get_plugins( '/smeta' );
	    if( !empty( $smeta_data['smeta.php'] ) && $smeta_data['smeta.php']['Version'] != '1.5' ):
		    ?>
		    <div class="notice notice-success is-dismissible error">
		        <p><?php esc_html_e( 'Reinstall Smeta plugin ( Delete it and theme will offer you to install it again )', 'adifier' ); ?></p>
		    </div>
		    <?php
	    endif;
	endif;

	if( function_exists('adifier_create_post_types') ):
		$smeta_data = get_plugins( '/adifier-system' );
	    if( !empty( $smeta_data['adifier-system.php'] ) && $smeta_data['adifier-system.php']['Version'] != '3.0.9' ):
		    ?>
		    <div class="notice notice-success is-dismissible error">
		        <p><?php esc_html_e( 'Reinstall Adifier System plugin ( Delete it and theme will offer you to install it again )', 'adifier' ); ?></p>
		    </div>
		    <?php
	    endif;
	endif;	

	if( function_exists('adifier_create_post_types') ):
		$smeta_data = get_plugins( '/adifier-import' );
	    if( !empty( $smeta_data['adifier-import.php'] ) && $smeta_data['adifier-import.php']['Version'] != '1.3' ):
		    ?>
		    <div class="notice notice-success is-dismissible error">
		        <p><?php esc_html_e( 'Reinstall Adifier Import plugin ( Delete it and theme will offer you to install it again )', 'adifier' ); ?></p>
		    </div>
		    <?php
	    endif;
	endif;	

	/* for update 3.8.1 change option which uses adifier grouped to combine multiple elements with + instead of || to allow empty values */
	$update = get_option( 'adifier_update' );
	if( empty( $update ) && class_exists( 'Redux' ) ){
		$list = array(
			'hybrid_free_stuff',
			'packages',
			'subscriptions',
			'hybrids',
			'currencies'
		);

		foreach( $list as $item ){
			$data = adifier_get_option( $item );
			$data = str_replace( '||', '+', $data );

			Redux::setOption( 'adifier_options', $item, $data );
		}

		$update = update_option( 'adifier_update', '1' );

	}

}
add_action( 'admin_notices', 'adifier_admin_notices' );
}

/*
* LIst of required plugins for the theme
*/
if( !function_exists('adifier_requred_plugins') ){
function adifier_requred_plugins(){
	$plugins = array(
		array(
				'name'                 => esc_html__( 'Redux Framework', 'adifier' ),
				'slug'                 => 'redux-framework',
				'required'             => true,
				'version'              => '',
				'force_activation'     => false,
				'force_deactivation'   => false,
				'external_url'         => '',
		),
		array(
				'name'                 => esc_html__( 'King Composer', 'adifier' ),
				'slug'                 => 'kingcomposer',
				'required'             => false,
				'version'              => '',
				'force_activation'     => false,
				'force_deactivation'   => false,
				'external_url'         => '',
		),	
		array(
				'name'                 => esc_html__( 'Adifier System', 'adifier' ),
				'slug'                 => 'adifier-system',
				'source'               => get_template_directory() . '/lib/plugins/adifier-system.zip',
				'required'             => true,
				'version'              => '',
				'force_activation'     => false,
				'force_deactivation'   => false,
				'external_url'         => '',
		),
		array(
				'name'                 => esc_html__( 'Smeta', 'adifier' ),
				'slug'                 => 'smeta',
				'source'               => get_template_directory() . '/lib/plugins/smeta.zip',
				'required'             => true,
				'version'              => '',
				'force_activation'     => false,
				'force_deactivation'   => false,
				'external_url'         => '',
		),
		array(
				'name'                 => esc_html__( 'Adifier Import', 'adifier' ),
				'slug'                 => 'adifier-import',
				'source'               => get_template_directory() . '/lib/plugins/adifier-import.zip',
				'required'             => false,
				'version'              => '',
				'force_activation'     => false,
				'force_deactivation'   => false,
				'external_url'         => '',
		),
	);
	$config = array(
			'domain'           => 'industry',
			'default_path'     => '',
			'menu'             => 'install-required-plugins',
			'has_notices'      => true,
			'is_automatic'     => false,
			'message'          => ''
	);

	tgmpa( $plugins, $config );
}
add_action( 'tgmpa_register', 'adifier_requred_plugins' );
}

/*
* Theme sidebars
*/
if( !function_exists('adifier_widgets_init') ){
function adifier_widgets_init(){

	$sidebars = array(
		array(
			'name' 	=> esc_html__('Blog Sidebar', 'adifier'),
			'id'	=> 'blog',
			'desc'	=> esc_html__('Appears on the right side of blog listing.', 'adifier')
		),
		array(
			'name' 	=> esc_html__('Single Blog Sidebar', 'adifier'),
			'id'	=> 'single-blog',
			'desc'	=> esc_html__('Appears on the right side of single blog page.', 'adifier')
		),
		array(
			'name' 	=> esc_html__('Bottom Sidebar 1', 'adifier'),
			'id'	=> 'bottom-1',
			'desc'	=> esc_html__('Appears at the bottom of the page.', 'adifier')
		),
		array(
			'name' 	=> esc_html__('Bottom Sidebar 2', 'adifier'),
			'id'	=> 'bottom-2',
			'desc'	=> esc_html__('Appears at the bottom of the page.', 'adifier')
		),
		array(
			'name' 	=> esc_html__('Bottom Sidebar 3', 'adifier'),
			'id'	=> 'bottom-3',
			'desc'	=> esc_html__('Appears at the bottom of the page.', 'adifier')
		),
		array(
			'name' 	=> esc_html__('Page Right Sidebar', 'adifier'),
			'id'	=> 'right',
			'desc'	=> esc_html__('Appears at the right of the page.', 'adifier')
		),
		array(
			'name' 	=> esc_html__('Page Left Sidebar', 'adifier'),
			'id'	=> 'left',
			'desc'	=> esc_html__('Appears at the left of the page.', 'adifier')
		)
	);

	$page_custom_sidebars = adifier_get_option( 'page_custom_sidebars' );
	if( !empty( $page_custom_sidebars ) ){
		for( $i=1; $i <= $page_custom_sidebars; $i++ ){
			$sidebars[] = array(
				'name' 			=> esc_html__('Custom Sidebar ', 'adifier').$i,
				'id' 			=> 'custom-sidebar-'.$i,
				'desc'			=> ''				
			);
		}	
	}		

	foreach( $sidebars as $sidebar ){
		register_sidebar(array(
			'name' 				=> $sidebar['name'],
			'id' 				=> $sidebar['id'],
			'before_widget' 	=> '<div class="widget white-block clearfix %2$s" >',
			'after_widget' 		=> '</div>',
			'before_title' 		=> '<div class="white-block-title"><h5>',
			'after_title' 		=> '</h5></div>',
			'description' 		=> $sidebar['desc']
		));			
	}
}

add_action('widgets_init', 'adifier_widgets_init');
}

/*
* Default values of thee options when Redux PLugin is disabled
*/
if( !function_exists('adifier_defaults') ){
function adifier_defaults( $id ){	
	$defaults = array(
		'site_logo' 					=> array( 'url' => '' ),
		'header_style'					=> 'header-1',
		'header_banner'					=> '',
		'enable_sticky' 				=> 'yes',
		'direction' 					=> '',
		'custom_css' 					=> '',
		'trans_advert' 					=> 'advert',
		'trans_advert-category' 		=> 'advert-category',
		'trans_advert-location' 		=> 'advert-location',
		'enable_share' 					=> 'yes',
		'facebook_app_id' 				=> '',
		'facebook_app_secret' 			=> '',
		'twitter_app_id' 				=> '',
		'twitter_app_secret' 			=> '',
		'google_app_id' 				=> '',
		'google_app_secret' 			=> '',
		'ad_types' 						=> '',
		'submit_terms' 					=> '',
		'location_search' 				=> 'geo',
		'use_google_location' 			=> 'yes',
		'single_location_display' 		=> 'geo_value',
		'profile_location_display' 		=> 'geo_value',
		'regular_expires' 				=> '30',
		'auction_expires' 				=> '10',
		'google_location_restriction' 	=> '',
		'max_top_ads' 					=> '5',
		'approval_method' 				=> 'auto',
		'enable_conditions' 			=> 'yes',
		'max_homemap_ads' 				=> '100',
		'radius_units' 					=> 'mi',
		'radius_max' 					=> '300',
		'adverts_per_page' 				=> '9',
		'adverts_per_page_author' 		=> '8',
		'video_thumbnail' 				=> '',
		'bidding_step' 					=> 5,
		'sender_email' 					=> '',
		'sender_name' 					=> '',
		'email_logo' 					=> '',
		'max_videos' 					=> '',
		'max_images' 					=> '',
		'map_style' 					=> '',
		'enable_compare' 				=> 'no',
		'show_decimals' 				=> 'yes',
		'compare_max_ads' 				=> '20',
		'single_ad_sidebar_location' 	=> 'top',
		'package_free_ads' 				=> '',
		'subscription_free_time' 		=> '',
		'hybrid_free_stuff' 			=> '',
		'account_payment' 				=> 'free',
		'packages' 						=> '',
		'subscriptions' 				=> '',
		'hybrids' 						=> '',
		'thousands_separator' 			=> ',',
		'decimal_separator' 			=> '.',
		'currency_location' 			=> 'front',
		'currency_abbr' 				=> '',
		'payment_enviroment' 			=> 'live',
		'currency_symbol' 				=> '',
		'tax' 							=> '',
		'tax_name' 						=> '',
		'invoice_data' 					=> '',
		'listing_type' 					=> '1',
		'mail_chimp_api' 				=> '',
		'mail_chimp_list_id' 			=> '',
		'main_color' 					=> '#00a591',
		'main_color_hover' 				=> '#008c77',
		'main_color_font' 				=> '#ffffff',
		'main_color_font_hover' 		=> '#fff',
		'search_btn_bg' 				=> '#ff5a5f',
		'search_btn_bg_hover' 			=> '#d54b4f',
		'search_btn_font' 				=> '#fff',
		'search_btn_font_hover' 		=> '#fff',
		'link_color' 					=> '#666',
		'price_color' 					=> '#d54b4f',
		'logo_width' 					=> '',
		'logo_height' 					=> '',
		'header_submit' 				=> 'full',
		'text_font' 					=> 'Open Sans',
		'text_font_size' 				=> '14px',
		'text_font_line_height' 		=> '24px',
		'text_font_weight' 				=> '400',
		'text_font_color' 				=> '#484848',
		'title_font' 					=> 'Quicksand',
		'title_font_weight' 			=> '700',
		'title_font_color' 				=> '#333',
		'heading_line_height' 			=> '1.3',
		'h1_font_size' 					=> '40px',
		'h2_font_size' 					=> '35px',
		'h3_font_size' 					=> '30px',
		'h4_font_size' 					=> '25px',
		'h5_font_size' 					=> '18px',
		'h6_font_size' 					=> '16px',
		'footer_bg_color' 				=> '#374252',
		'footer_font_color' 			=> '#959ba7',
		'footer_active_color' 			=> '#fff',
		'show_breadcrumbs' 				=> 'no',
		'breadcrumbs_style' 			=> 'normal',
		'breadcrumbs_bg_color' 			=> '#2a2f36',
		'breadcrumbs_font_color' 		=> '#fff',
		'breadcrumbs_image_bg' 			=> array( 'url' => '' ),
		'pt_price_bg_color' 			=> '#374252',
		'pt_price_font_color' 			=> '#fff',
		'pt_title_bg_color' 			=> '#2e3744',
		'pt_title_font_color' 			=> '#fff',
		'pt_btn_bg_color' 				=> '#374252',
		'pt_btn_font_color' 			=> '#fff',
		'pt_btn_bg_color' 				=> '#374252',
		'pt_btn_font_color' 			=> '#fff',
		'pt_btn_bg_color_hover' 		=> '#2e3744',
		'pt_btn_font_color_hover' 		=> '#fff',
		'pt_ac_price_bg_color' 			=> '#00a591',
		'pt_ac_price_font_color' 		=> '#fff',
		'pt_ac_title_bg_color' 			=> '#008c77',
		'pt_ac_title_font_color' 		=> '#fff',
		'pt_ac_btn_bg_color' 			=> '#00a591',
		'pt_ac_btn_font_color' 			=> '#fff',
		'pt_ac_btn_bg_color_hover' 		=> '#008c77',
		'pt_ac_btn_font_color_hover' 	=> '#fff',
		'copyrights_bg_color' 			=> '#2d323e',
		'copyrights_font_color' 		=> '#aaa',
		'copyrights_active_color' 		=> '#fff',
		'dark_nav_bg_color' 			=> '#374252',
		'dark_nav_font_color' 			=> '#fff',
		'dark_nav_font_color_active' 	=> '#fff',
		'dark_nav_logo' 				=> array( 'url' => '' ),
		'subscription_bg_color' 		=> '#fff',
		'subscription_font_color' 		=> '#2d323e',
		'contact_phone_icon_bg_color' 	=> '#d54b4f',
		'contact_phone_bg_color' 		=> '#ff5a5f',
		'contact_phone_font_color' 		=> '#fff',
		'contact_msg_icon_bg_color' 	=> '#2e3744',
		'contact_msg_bg_color' 			=> '#4b586b',
		'contact_msg_font_color' 		=> '#fff',
		'contact_form_email' 			=> '',
		'markers' 						=> '',
		'marker_icon' 					=> '',
		'markers_max_zoom' 				=> '',
		'google_api_key' 				=> '',
		'show_subscription_form' 		=> 'no',
		'show_footer' 					=> 'no',
		'copyrights' 					=> '',
		'tb_social' 					=> 'no',
		'tb_facebook_link' 				=> '',
		'tb_twitter_link' 				=> '',
		'tb_google_link' 				=> '',
		'tb_instagram_link' 			=> '',
		'tb_youtube_link' 				=> '',
		'tb_pinterest_link' 			=> '',
		'tb_rss_link' 					=> '',
		'password_length'				=> 6
	);
	
	if( isset( $defaults[$id] ) ){
		return $defaults[$id];
	}
	else{
		
		return '';
	}
}
}

/*
* Get value of theme option
*/
if( !function_exists('adifier_get_option') ){
function adifier_get_option($id){
	if( class_exists( 'Adifier_Options' ) && method_exists( 'Adifier_Options', 'get' ) ){
		return Adifier_Options::get( $id );
	}
	else{
		return adifier_defaults( $id );
	}
}
}


/*
* Image sizes
*/
if( !function_exists('adifier_image_sizes') ){
function adifier_image_sizes(){
	set_post_thumbnail_size( 750 );
	add_image_size( 'adifier-widget', 70, 70, true );
	$list = array(
		'grid',
		'list',
		'single-slider'
	);
	foreach( $list as $item ){
		add_image_size( 'adifier-'.$item, adifier_get_option( $item.'_width' ), adifier_get_option( $item.'_height' ), adifier_get_option( $item.'_crop' ) );	
	}
}
add_action('after_setup_theme', 'adifier_image_sizes');
}

/*
* Setup neccessary theme support, add image sizes
*/
if( !function_exists('adifier_setup') ){
function adifier_setup(){
	add_theme_support('automatic-feed-links');
	add_theme_support( "title-tag" );
	add_theme_support('html5', array(
		'comment-form',
		'comment-list'
	));
	register_nav_menu( 'main-navigation', esc_html__('Main Navigation', 'adifier') );
	register_nav_menu( 'bottom-navigation', esc_html__('Bottom Navigation', 'adifier') );
	
	add_theme_support( 'post-thumbnails' );

	add_editor_style();
}
add_action('after_setup_theme', 'adifier_setup');
}

/*
* Enqueue map script depending on map selection
*/
if( !function_exists('adifier_map_script') ){
function adifier_map_script( $enqueue = false ){
	$use_google_location = adifier_get_option( 'use_google_location' );

	if( $use_google_location !== 'yes' && !$enqueue ){
		return false;
	}

	$map_source = adifier_get_option( 'map_source' );
	$map_api_key = adifier_get_option( 'google_api_key' );
	$api = '';

	$map_lang = adifier_get_option( 'google_map_lang' );
	if( !empty( $map_lang ) ){
		$api = '&language='.$map_lang;
	}

	wp_enqueue_script( 'adifier-map-consent', get_theme_file_uri( '/js/jquery.mapconsent.js' ), array('jquery'), false, true);

	if( $map_source == 'google' ){
		
		if( !empty( $map_api_key ) ){
			$api .= '&key='.$map_api_key;
		}
		wp_enqueue_script( 'adifier-map', '//maps.googleapis.com/maps/api/js?libraries=places&v=3'.$api, array('jquery'), false, true );
	}
	else if( $map_source == 'mapbox' ){
		wp_enqueue_script( 'adifier-map', '//api.mapbox.com/mapbox-gl-js/v0.54.0/mapbox-gl.js', array('jquery'), false, true );
		wp_enqueue_script( 'geocoder', '//api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.5.1/mapbox-gl-geocoder.min.js', array('adifier-map','jquery'), false, true );
		wp_localize_script( 'geocoder', 'adifier_mapbox_data', array(
			'api' 				=> $map_api_key,
			'language' 			=> $map_lang,
			'placeholder' 		=> esc_html__( 'Start typing...', 'adifier' )
		));
		wp_enqueue_style( 'mapbox', '//api.mapbox.com/mapbox-gl-js/v0.54.0/mapbox-gl.css' );
		wp_enqueue_style( 'geocoder', '//api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.5.1/mapbox-gl-geocoder.css' );
		wp_enqueue_script( 'geocoder-autocomplete', get_theme_file_uri( '/js/jquery.autocomplete.min.js' ), array('adifier-map'), false, true );
	}
	else if( $map_source == 'osm' ){
		wp_enqueue_script( 'adifier-map', '//unpkg.com/leaflet@1.6.0/dist/leaflet.js', array(), false, true );
		wp_enqueue_script( 'geocoder', get_theme_file_uri( '/js/jquery.autocomplete.min.js' ), array('adifier-map'), false, true );
		
		wp_enqueue_style( 'adifier-map-style', '//unpkg.com/leaflet@1.6.0/dist/leaflet.css');
	}

	$ask_map_consent = ( !is_user_logged_in() && empty( $_COOKIE['adifier_consent_cookie'] ) && !empty( adifier_get_option( 'gdpr_text' ) ) )? true: false;

	wp_localize_script( 'adifier-map', 'adifier_map_data', array(
		'map_source' 			=> adifier_get_option( 'map_source' ),
		'ask_map_consent'		=> $ask_map_consent,
		'consent_map_text'		=> esc_html__( 'I accept map cookies', 'adifier' ),		
	));
}
}

/*
* Enqueue gmap is mapis used
*/
if( !function_exists( 'adifier_enqueue_gmap' ) ){
function adifier_enqueue_gmap(){
	$use_google_location = adifier_get_option( 'use_google_location' );

	if( $use_google_location !== 'yes'){
		return false;
	}

	wp_enqueue_script('adifier-gmap', get_theme_file_uri( '/js/gmap.js' ), array('jquery'), false, true);
}
}


/*
* Load elementor icons
*/
if( !function_exists('adifier_elementor_icons') ){
function adifier_elementor_icons(){
	if( !empty( $_GET['action'] ) && $_GET['action'] == 'elementor' ){
		wp_enqueue_style( 'afementor', get_theme_file_uri( '/css/afementor.css' ) );
	}
}
add_action( 'elementor/editor/before_enqueue_styles', 'adifier_elementor_icons' );
}


/*
* Load script and styles to the admin section
*/
if( !function_exists('adifier_admin_scripts_styles') ){
function adifier_admin_scripts_styles( $hook ){
	global $post;

	/* If we are on user edit screen */
	if( $hook == 'profile.php' || $hook == 'user-edit.php' ){
		wp_enqueue_media();
		adifier_load_comon_scripts_styles();
		
		wp_enqueue_style( 'cmb-jquery-ui' );
		wp_enqueue_style( 'cmb-timepicker-ui' );
		wp_enqueue_style( 'adifier-datetimepicker' );
		wp_enqueue_script('cmb-timepicker');

		adifier_map_script();
		adifier_enqueue_gmap();
	}
	/* If we are on reviews page */
	if( $hook == 'toplevel_page_admin_reviews' ){
		wp_enqueue_style( 'adifier-icons', get_theme_file_uri( '/css/adifier-icons.css' ) );
	}
	/* If we are on posts page */
	if( ( $hook == 'post.php' || $hook == 'post-new.php' ) && $post->post_type == 'advert' ){
		/* Custom fields */
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'adifier-cf', get_theme_file_uri( '/css/custom-fields.css' ) );
		wp_enqueue_script('adifier-cf', get_theme_file_uri( '/js/custom-fields.js' ), array('jquery'), false, true);		
		wp_enqueue_script( 'adifier-auction', get_theme_file_uri( '/js/auction.js' ), array('jquery'), false, true);
	}
	/* If we are on taxonomy edit screen */
	if( isset( $_GET['taxonomy'] ) && stristr( $_GET['taxonomy'], 'advert-category' ) ){
		wp_enqueue_media();
	}

	/* Admin specific */
	wp_enqueue_style( 'adifier-admin', get_theme_file_uri( '/css/admin.css' ) );
	wp_enqueue_script('adifier-admin', get_theme_file_uri( '/js/admin.js' ), array('jquery'), false, true);
	wp_localize_script( 'adifier-admin', 'adifier_data', array(
		'adifier_nonce' 		=> wp_create_nonce('adifier_nonce'),
	));
	
}
add_action('admin_enqueue_scripts', 'adifier_admin_scripts_styles', 1);
}

if( !function_exists('adifier_frontend_editor_style') ){
function adifier_frontend_editor_style( $mceInit ) {
	ob_start();
	include( get_theme_file_path( 'css/main-color.css.php' ) );
	$editor_style = ob_get_contents();
	ob_end_clean();		

	$editor_style = str_replace(array("\n", "\t", "\r", "\n\r"), '', $editor_style);
	$editor_style = str_replace('"', '\"', $editor_style);

    $styles = "@import url('".adifier_which_fonts()."');".$editor_style;
    if ( isset( $mceInit['content_style'] ) ) {
        $mceInit['content_style'] .= ' ' . $styles . ' ';
    } else {
        $mceInit['content_style'] = $styles . ' ';
    }
    return $mceInit;
}
}

/*
* Crete fonts array since it is used for tinymce as well
*/
if( !function_exists('adifier_which_fonts') ){
function adifier_which_fonts(){
	$load_fonts = array(
		array(
			'font' 	   => adifier_get_option( 'text_font' ),
			'weight'   => adifier_get_option( 'text_font_weight' ).',600,700',
		),
		array(
			'font' 	   => adifier_get_option( 'title_font' ),
			'weight'   => adifier_get_option( 'title_font_weight' ).',400,500',
		),
	);

	/* for google brand requirements */
	if( !is_user_logged_in() ){
		$load_fonts[] = array(
			'font'		=> 'Roboto',
			'weight'	=> '500'
		);
	}

	$list = array();
	$loaded_fonts = array();
	foreach( $load_fonts as $key => $data ){
		if( !empty( $data['font'] ) && !isset( $loaded_fonts[$data['font']] ) ){
			$loaded_fonts[$data['font']] = $data['weight'];
		}
		else{
			$loaded_fonts[$data['font']] .= ','.$data['weight'];
		}
	}

	foreach( $loaded_fonts as $font => $weight ){
		$list[] = $font.':'.$weight;
	}

	$list = implode( '|', $list ).'&subset=all';

	$font_family = str_replace( '+', ' ', $list );
	
	return add_query_arg( 'family', urlencode( $font_family ), "//fonts.googleapis.com/css" );
        
}
}

/*
* Load selected fonts and it's styles
*/
if( !function_exists('adifier_enqueue_font') ){
function adifier_enqueue_font() {
    wp_enqueue_style( 'adifier-fonts', adifier_which_fonts(), array(), '1.0.0' );
}
}

/*
* Common scripts fro frontend and backend
*/
if( !function_exists('adifier_load_comon_scripts_styles') ){
function adifier_load_comon_scripts_styles(){
	if( function_exists('cmb_init') ){
		wp_register_style( 'cmb-jquery-ui', trailingslashit( CMB_URL ) . 'css/vendor/jquery-ui/jquery-ui.css');
		wp_register_style( 'cmb-timepicker-ui', trailingslashit( CMB_URL ) . '/css/jquery-ui-timepicker-addon.css');
		wp_register_style( 'adifier-datetimepicker', get_theme_file_uri( '/css/datetimepicker.min.css' ) );
		wp_register_script( 'cmb-timepicker', trailingslashit( CMB_URL ) . 'js/jquery-ui-timepicker-addon.js', array( 'jquery', 'jquery-ui-slider', 'jquery-ui-core', 'jquery-ui-datepicker' ), false, false, true );
	}
}
}

/*
* Load frontend scriptss and styles 
*/
if( !function_exists('adifier_scripts_styles') ){
function adifier_scripts_styles(){
	/* Load script and style for invoice only */
	if( is_singular( 'ad-order' ) ){
		wp_enqueue_style( 'adifier-ad-order-font', '//fonts.googleapis.com/css?family=Roboto%3A400%2C300%26subset%3Dall%7COpen+Sans%3A400%26subset%3Dall' );
		wp_enqueue_style( 'adifier-ad-order-css', get_theme_file_uri( '/css/single-ad-order.css' ) );	
		return false;
	}

	/* Bootstrap */
	wp_enqueue_style( 'bootstrap', get_theme_file_uri( '/css/bootstrap.min.css' ) );
	wp_enqueue_script( 'bootstrap', get_theme_file_uri( '/js/bootstrap.min.js' ), array('jquery'), false, true);
	/* adifier-icons */
	wp_enqueue_style( 'adifier-icons', get_theme_file_uri( '/css/adifier-icons.css' ) );

	if( function_exists('elementor_fail_php_version') ){
		wp_enqueue_style( 'adifier-fontawesome', plugins_url( 'elementor/assets/lib/font-awesome/css/font-awesome.min.css') );
	}

	/* Load Fonts */
	adifier_enqueue_font();
	
	if (is_singular() && comments_open() && get_option('thread_comments')){
		wp_enqueue_script('comment-reply');
	}

	/* Map */
	adifier_map_script();

	/* Date picker */
	adifier_load_comon_scripts_styles();

	/* Custom Fields */
	wp_register_style( 'adifier-cf', get_theme_file_uri( '/css/custom-fields.css' ) );
	wp_register_script( 'adifier-cf', get_theme_file_uri( '/js/custom-fields.js' ), array('jquery'), false, true);

	/* Scrollbar for messages */
	wp_enqueue_script('scrollbar', get_theme_file_uri( '/js/profile/jquery.scrollbar.min.js' ), array('jquery'), false, true);

	/* Auction */
	wp_register_script( 'adifier-auction', get_theme_file_uri( '/js/auction.js' ), array('jquery'), false, true);

	/* script for displaying location on advert single and on author profile */
	wp_register_script( 'adifier-location', get_theme_file_uri( '/js/location-map.js' ), array('jquery'), false, true);

	if( in_array( get_page_template_slug(), array( 'page-tpl_contact.php'  ) ) ){
		adifier_map_script( true );
	}

	/* When we are on search page */
	if( ( is_page() && in_array( get_page_template_slug(), array( 'page-tpl_search.php', 'page-tpl_search_map.php' ) ) || is_tax( 'advert-category' ) || is_tax( 'advert-location' ) ) ){
		//wp_enqueue_script( 'googlemap' );
		wp_enqueue_style( 'cmb-jquery-ui' );
		wp_enqueue_style( 'cmb-timepicker-ui' );
		wp_enqueue_style( 'adifier-datetimepicker' );
		wp_enqueue_script('cmb-timepicker');

		/* Searching script */
		wp_enqueue_script( 'touch-punch', get_theme_file_uri( '/js/search/jquery.ui.touch-punch.min.js' ), array('jquery' ), false, true);
		$use_google_location = adifier_get_option( 'use_google_location' );
		if( $use_google_location == 'yes' ){
			$dependencies = array('jquery', 'adifier-map', 'cmb-timepicker');
		}
		else{
			$dependencies = array('jquery', 'cmb-timepicker');
		}
		wp_enqueue_script( 'adifier-search', get_theme_file_uri( '/js/search/search.js' ), $dependencies, false, true);
	}

	if( ( is_page() && in_array( get_page_template_slug(), array( 'page-tpl_search.php', 'page-tpl_search_map.php' ) ) ) || ( !empty( $_GET['screen'] ) && in_array( $_GET['screen'], array( 'new', 'profile', 'edit' ) ) ) || is_tax( 'advert-category' ) || is_tax( 'advert-location' ) ){
		/* select2 */
		wp_enqueue_style( 'select2', get_theme_file_uri( '/css/select2.min.css' ) );
		wp_enqueue_script( 'select2', get_theme_file_uri( '/js/search/select2.min.js' ), array('jquery'), false, true );


		wp_enqueue_script( 'adifier-taxonomy-multifilter', get_theme_file_uri( '/js/taxonomy-multifilter.js' ), array('jquery'), false, true );
	}

	/* Searching with map script */
	if( is_page() && in_array( get_page_template_slug(), array( 'page-tpl_search_map.php', 'page-tpl_home.php', 'page-tpl_full_width_builder.php', 'page-tpl_full_width_no_push.php' ) ) || is_tax( 'advert-category' ) || is_tax( 'advert-location' )  ){
		$use_google_location = adifier_get_option( 'use_google_location' );
		if( $use_google_location == 'yes' ){
			$map_source = adifier_get_option( 'map_source' );
			if( $map_source == 'google' ){
				wp_enqueue_script( 'infobox', get_theme_file_uri( '/js/search/infobox.js' ), array('jquery', 'adifier-map'), false, true);
				wp_enqueue_script( 'adifier-infobox-extend', get_theme_file_uri( '/js/search/infobox-extend.js' ), array('jquery', 'adifier-map', 'infobox'), false, true);
				wp_enqueue_script( 'markerclusterer', get_theme_file_uri( '/js/search/markerclusterer.js' ), array('jquery', 'adifier-map'), false, true);
			}
			wp_enqueue_script( 'adifier-search-map-start-function', get_theme_file_uri( '/js/search/search-map-start-function.js' ), array('jquery', 'adifier-map'), false, true);
			if( get_page_template_slug() == 'page-tpl_search_map.php' || is_tax( 'advert-category' ) || is_tax( 'advert-location' ) ){
				wp_enqueue_script( 'adifier-search-map', get_theme_file_uri( '/js/search/search-map.js' ), array('jquery', 'adifier-map', 'adifier-search-map-start-function'), false, true);
			}
		}
		if( in_array( get_page_template_slug(), array( 'page-tpl_home.php', 'page-tpl_full_width_builder.php', 'page-tpl_full_width_no_push.php'  ) ) ){
			wp_enqueue_script('adifier-elements', get_theme_file_uri( '/js/elements.js' ), array('jquery'), false, true);
			wp_enqueue_script('typed', get_theme_file_uri( '/js/typed.min.js' ), array('jquery'), false, true);
		}
	}

	if(  !empty( $_GET['elementor_library'] ) ){
		wp_enqueue_script('adifier-elements', get_theme_file_uri( '/js/elements.js' ), array('jquery'), false, true);
		wp_enqueue_script('typed', get_theme_file_uri( '/js/typed.min.js' ), array('jquery'), false, true);
	}	

	/* OWL Carousel */
	wp_enqueue_style( 'owl-carousel', get_theme_file_uri( '/css/owl.carousel.min.css' ) );
	wp_enqueue_script( 'owl-carousel', get_theme_file_uri( '/js/owl.carousel.min.js' ), array('jquery'), false, true );

	/* Magnific popup */
	wp_enqueue_style( 'magnific-popup', get_theme_file_uri( '/css/magnific-popup.css' ) );
	wp_enqueue_script( 'magnific-popup', get_theme_file_uri( '/js/jquery.magnific-popup.min.js' ), array('jquery'), false, true );

	/* If we are on profile page */
	if( is_author() ){
		/* Author CSS */
		wp_enqueue_style( 'adifier-author', get_theme_file_uri( '/css/author.css' ) );

		if( !adifier_is_own_account() ){
			adifier_enqueue_gmap();	
			if( adifier_get_option( 'use_google_location' ) == 'yes' ){
				wp_enqueue_script( 'adifier-location' );
			}
			wp_enqueue_script( 'adifier-author', get_theme_file_uri( '/js/author.js' ), array('jquery'), false, true);
		}

		/* If wee are on own dashboard start charts */
		if( adifier_is_own_account() ){
			wp_enqueue_script( 'chart', get_theme_file_uri( '/js/Chart.bundle.min.js' ), false, false, true );	
			wp_enqueue_script( 'adifier-dashboard', get_theme_file_uri( '/js/profile/dashboard.js' ), array('jquery'), false, true);
			wp_enqueue_script( 'adifier-comments', get_theme_file_uri( '/js/profile/comments.js' ), false, false, true );	
		}

		/* If we are on profile edit, adding new ad, modifying existing one or on ads listin */
		if( !empty( $_GET['screen'] ) && in_array( $_GET['screen'], array( 'profile', 'new', 'ads', 'edit', 'favorites' ) ) ){
			adifier_enqueue_gmap();

			wp_enqueue_style( 'cmb-jquery-ui' );
			wp_enqueue_style( 'cmb-timepicker-ui' );
			wp_enqueue_style( 'adifier-datetimepicker' );
			wp_enqueue_script( 'cmb-timepicker' );

			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ), array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), false, true );
			wp_register_script( 'wp-color-picker', admin_url( 'js/color-picker.js' ), array( 'jquery', 'wp-i18n', 'iris' ), false, true );
			wp_enqueue_script( 'wp-color-picker' );
		    $colorpicker_l10n = array(
		        'clear' 		=> esc_html__( 'Clear', 'adifier' ),
		        'defaultString' => esc_html__( 'Default', 'adifier' ),
		        'pick' 			=> esc_html__( 'Select Color', 'adifier' ),
		        'current' 		=> esc_html__( 'Current Color', 'adifier' ),
		    );
		    //wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n ); 

			wp_enqueue_style( 'adifier-cf' );
			wp_enqueue_script( 'adifier-cf' );

			wp_enqueue_script('jquery-ui-sortable');

			/* Dropzone for image uploads */
			wp_enqueue_script('adifier-exif', get_theme_file_uri( '/js/profile/exif.js' ), array('jquery'), false, true);
			wp_enqueue_script('adifier-dropzone', get_theme_file_uri( '/js/profile/dropzone.js' ), array('jquery'), false, true);

			wp_enqueue_script('jquery-ui-datepicker');

			if( adifier_is_allowed_ad_type(2) ){
				wp_enqueue_script('adifier-auction');
			}

			/* Profile actions */
			wp_enqueue_script('adifier-profile', get_theme_file_uri( '/js/profile/profile.js' ), array('jquery'), false, true);
			wp_localize_script( 'adifier-profile', 'dropzone_locale', array(
				"dictDefaultMessage"			=>  esc_html__( "Drop files here to upload", 'adifier' ),
				"dictFallbackMessage"			=>  esc_html__( "Your browser does not support drag'n'drop file uploads.", 'adifier' ),
				"dictFallbackText"				=>  esc_html__( "Please use the fallback form below to upload your files like in the olden days.", 'adifier' ),
				"dictFileTooBig"				=>  esc_html__( "File is too big ({{filesize}}MiB). Max filesize: {{maxFilesize}}MiB.", 'adifier' ),
				"dictInvalidFileType"			=>  esc_html__( "You can't upload files of this type.", 'adifier' ),
				"dictResponseError"				=>  esc_html__( "Server responded with {{statusCode}} code.", 'adifier' ),
				"dictCancelUpload"				=>  esc_html__( "Cancel upload", 'adifier' ),
				"dictUploadCanceled"			=>  esc_html__( "Upload canceled.", 'adifier' ),
				"dictCancelUploadConfirmation"	=>  esc_html__( "Are you sure you want to cancel this upload?", 'adifier' ),
				"dictRemoveFile"				=>  esc_html__( "Remove file", 'adifier' ),
				"dictMaxFilesExceeded"			=>  esc_html__( "You can not upload any more files.", 'adifier' ),
				"minImageWidth"					=> 	adifier_get_option( 'single-slider_width' ),
				"minImageHeight"				=> 	adifier_get_option( 'single-slider_height' )
			));
		}
		/* If we are on screen for messaging */
		if( !empty( $_GET['screen'] ) && in_array( $_GET['screen'], array( 'messages' ) ) ){
			/* Messaging scripts */
			wp_enqueue_script('adifier-messages', get_theme_file_uri( '/js/profile/messages.js' ), array('jquery', 'bootstrap'), false, true);
		}
		/* If we are on screen for listing reviews where user can respond */
		if( !adifier_is_own_account() || ( !empty( $_GET['screen'] ) && in_array( $_GET['screen'], array( 'reviews' ) ) ) ){
			/* reviews scripts */
			wp_enqueue_script('adifier-reviews', get_theme_file_uri( '/js/profile/reviews.js' ), array('jquery'), false, true);
		}
		/* If we are on screen for package/subscription payment or add listing where we can purchase promotion */
		if( !empty( $_GET['screen'] ) && in_array( $_GET['screen'], array( 'acc_pay', 'ads' ) ) ){
			/* Handle purchasing */
			wp_enqueue_script('adifier-purchase', get_theme_file_uri( '/js/profile/purchase.js' ), array('jquery'), false, true);	
		}
	}
	/* Is we are on advet single page */
	if( is_singular( 'advert' ) ){
		//wp_enqueue_script( 'googlemap' );
		if( adifier_get_option( 'use_google_location' ) == 'yes' ){
			wp_enqueue_script( 'adifier-location' );
		}
		/* Advert single script */
		if( adifier_is_allowed_ad_type(2) ){
			wp_enqueue_script( 'adifier-auction' );
			wp_enqueue_script( 'adifier-countdown', get_theme_file_uri( '/js/countdown.js' ), array('jquery'), false, true);	
		}
		wp_enqueue_script( 'adifier-single-advert', get_theme_file_uri( '/js/single-advert.js' ), array('jquery'), false, true);	
	}

	/* If user is logged in send beacon every 15 mins to set online status */
	if( is_user_logged_in() ){
		wp_enqueue_script('adifier-beacon', get_theme_file_uri( '/js/profile/beacon.js' ), array('jquery'), false, true);	
	}
	
	/* login scripts */
	if( !is_user_logged_in() ){
		wp_enqueue_script('adifier-sc', get_theme_file_uri( '/js/adifier-sc.js' ), array('jquery'), false, true);	
	}

	if( adifier_get_option( 'enable_compare' ) == 'yes' ){
		wp_enqueue_script('adifier-compare', get_theme_file_uri( '/js/compare.js' ), array('jquery'), false, true);	
	}

	/* Common data */
	wp_enqueue_script('adifier-custom', get_theme_file_uri( '/js/custom.js' ), array('jquery'), false, true);
	wp_localize_script( 'bootstrap', 'adifier_data', adifier_get_js_options());
}
add_action('wp_enqueue_scripts', 'adifier_scripts_styles', 11 );
}


/*
Get max upload size for images
*/
function adifier_get_image_max_upload_size(){
	$max = adifier_get_option( 'max_image_size' );
	if( empty( $max ) ){
		$max = number_format ( wp_max_upload_size() * 0.000001 );
	}

	return $max;
}

/*
Get common data for scripts
*/
if( !function_exists('adifier_get_js_options') ){
function adifier_get_js_options(){
	$password_length = adifier_get_option( 'password_length' );
	$password_length = empty( $password_length ) ? 6 : $password_length;
	$marker_icon = adifier_get_option( 'marker_icon' );
	$data =  array(
		'markers_max_zoom' 		=> adifier_get_option( 'markers_max_zoom' ),
		'ajaxurl' 				=> admin_url('admin-ajax.php'),
		'adifier_nonce' 		=> wp_create_nonce('adifier_nonce'),
		'enable_sticky' 		=> adifier_get_option( 'enable_sticky' ),
		'marker_icon' 			=> '',
		'payment_enviroment'	=> adifier_get_option( 'payment_enviroment' ),
		'max_videos'			=> adifier_get_option( 'max_videos' ),
		'max_images'			=> adifier_get_option( 'max_images' ),
		'max_image_size'		=> adifier_get_image_max_upload_size(),
		'url'					=> get_template_directory_uri().'/images/',
		'map_style'				=> str_replace(array("\n", "\t", "\r", "\n\r"), '', adifier_get_option( 'map_style' )),
		'map_language'			=> adifier_get_option( 'google_map_lang' ),
		'mapbox_map_lang'		=> adifier_get_option( 'mapbox_map_lang' ),
		'mapbox_geocoder_lang'	=> adifier_get_option( 'mapbox_geocoder_lang' ),
		'osm_map_lang'			=> adifier_get_option( 'osm_map_lang' ),
		'country_restriction'	=> adifier_get_option( 'google_location_restriction' ),
		'main_color'			=> adifier_get_option( 'main_color' ),
		'address_order'			=> adifier_get_option( 'address_order' ),
		'use_google_direction'	=> adifier_get_option( 'use_google_direction' ),
		'tns_image_too_smal'	=> esc_html__( 'Image is too small', 'adifier' ),
		'tns_now'				=> esc_html__( 'Now', 'adifier' ),
		'tns_done'				=> esc_html__( 'Done', 'adifier' ),
		'search_trigger'		=> adifier_get_option( 'search_trigger' ),
		'pw'					=> array(
			'more_char' => sprintf( esc_html__('Min %s chars', 'adifier'), $password_length ),
			'strong' 	=> esc_html__('Strong', 'adifier'),
			'medium' 	=> esc_html__('Medium', 'adifier'),
			'weak' 		=> esc_html__('Weak', 'adifier'),
		),
		'password_strength'		=> adifier_get_option( 'password_strength_test' ),
		'compare_box_autoopen'  => adifier_get_option( 'compare_box_autoopen' ),
		'is_gdpr'				=> !empty( adifier_get_option( 'gdpr_text' ) ) ? 1 : 0,
		'video_consent_text'	=> esc_html__( 'By clicking play you are giving cookies consent', 'adifier' )
	);

	if( !empty( $marker_icon['url'] ) )	{
		$data['marker_icon'] = $marker_icon['url'];
		$data['marker_icon_width'] = $marker_icon['width'];
		$data['marker_icon_height'] = $marker_icon['height'];
	}

	return $data;
}
}


/*
* Dynamic CSS for applying appearance options
*/
if( !function_exists('adifier_add_main_style') ){
function adifier_add_main_style(){
	if( is_singular( 'ad-order' ) ){
		return false;
	}
	wp_enqueue_style('adifier-style', get_stylesheet_uri());
	ob_start();
	include( get_template_directory().'/css/main-color.css.php' );
	$custom_css = ob_get_contents();
	ob_end_clean();
	$custom_css = str_replace(array("\n", "\t", "\r", "\n\r"), '', $custom_css);
	wp_add_inline_style( 'adifier-style', $custom_css );
}
add_action('wp_enqueue_scripts', 'adifier_add_main_style', 13);
}

/*
* List of tags
*/
if( !function_exists('adifier_the_tags') ){
function adifier_the_tags(){
	$tags = get_the_tags();
	$list = array();
	if( !empty( $tags ) ){
		foreach( $tags as $tag ){
			$list[] = '<a href="'.esc_url( get_tag_link( $tag->term_id ) ).'">#'.$tag->name.'</a>';
		}
	}
	
	return join( ", ", $list );
}
}

/*
* Sizes of the tags in widget
*/
if( !function_exists('adifier_cloud_sizes') ){
function adifier_cloud_sizes($args) {
	$args['smallest'] = 11;
	$args['largest'] = 11;
	$args['unit'] = 'px';
	$tags_number = adifier_get_option( 'tags_number' );
	if( !empty( $tags_number ) ){
		$args['number'] = $tags_number;
	}
	return $args; 
}
add_filter('widget_tag_cloud_args','adifier_cloud_sizes');
}

/*
* Change excerpt more indicator
*/
if( !function_exists('adifier_custom_excerpt_more') ){
function adifier_custom_excerpt_more( $more ) {
	return '...';
}
add_filter( 'excerpt_more', 'adifier_custom_excerpt_more' );
}

/*
* Get category or all categories  for the post
*/
if( !function_exists('adifier_the_category') ){
function adifier_the_category( $number = -1, $show_color = true ){
	$list = '';
	$categories = get_the_category();
	if( !empty( $categories ) ){
		$number = $number == -1 ? sizeof( $categories ) : $number;
		for( $i=0; $i<$number; $i++ ){
			$category = $categories[$i];
			$bz_cat_color = get_term_meta( $category->term_id, 'bz_cat_color', true);
			$list .= '<a href="'.esc_url( get_category_link( $category->term_id ) ).'" class="bz-cat" '.( ( !empty( $bz_cat_color ) && $show_color ) ? 'style="background: '.esc_attr( $bz_cat_color ).';"' : '' ).'>'.$category->name.'</a> ';
		}
	}
	
	return $list;
}
}


/*
* Display comments structure
*/
if( !function_exists('adifier_comments') ){
function adifier_comments( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	$add_below = ''; 
	?>
	<!-- comment -->
	<div class="comment <?php echo  $comment->comment_parent != '0' ? esc_attr('comment-margin-left') : ''; ?>" id="comment-<?php echo esc_attr( get_comment_ID() ); ?>">
		<div class="flex-wrap flex-start-h">
			<div class="flex-left">
				<div class="flex-wrap flex-start-h">
					<div class="comment-avatar">
						<?php if( $comment->user_id == get_the_author_meta( 'ID' ) ): ?>
							<i class="icon-user aficon-user-alt" title="<?php esc_attr_e( 'Author', 'adifier' ) ?>"></i>
						<?php endif; ?>
						<?php echo get_avatar( $comment, 80 ); ?>
					</div>
					<div class="comment-info">
						<h5><?php comment_author(); ?></h5>
						<p><?php comment_time( get_option('date_format') ); esc_html_e(' at ','adifier'); comment_time( get_option('time_format')  ); ?> </p>
					</div>					
				</div>
			</div>
			<?php 
			if( get_the_author_meta( 'ID' ) != $comment->user_id ){
				comment_reply_link( 
					array_merge( 
						$args, 
						array( 
							'reply_text' 	=> '<i class="aficon-reply"></i> '.esc_html__( 'Reply', 'adifier' ),
							'add_below' 	=> $add_below, 
							'depth' 		=> $depth, 
							'max_depth' 	=> $args['max_depth'] 
						) 
					) 
				);
			}
			?>
		</div>
		<div class="comment-content-wrap">
			<?php 
			if ($comment->comment_approved != '0'){
				comment_text();
			}
			else{
				echo '<p>'.esc_html__('Your comment is awaiting moderation.', 'adifier').'</p>';
			}
			?>		
		</div>
	<?php  
}
}

/*
* Add video container arround embeding content of editor
*/
if( !function_exists('adifier_embed_html') ){
function adifier_embed_html( $html ) {
    return '<div class="video-container">' . $html . '</div>';
}
add_filter( 'embed_oembed_html', 'adifier_embed_html', 10, 3 );
add_filter( 'video_embed_html', 'adifier_embed_html' ); // Jetpack
}

/*
* Add span arrount category count so it can be styled
*/
if( !function_exists('adifier_cat_count_span') ){
function adifier_cat_count_span($links) {
  $links = str_replace('</a> (', '</a> <span>(', $links);
  $links = str_replace(')', ')</span>', $links);
  return $links;
}
add_filter('wp_list_categories', 'adifier_cat_count_span');
}

/*
* Add span arrount archive count so it can be styled
*/
if( !function_exists('adifier_archive_count_inline') ){
function adifier_archive_count_inline($links) {
	$links = str_replace('&nbsp;(', ' <span>(', $links);
	$links = str_replace(')', ')</span>', $links);
	return $links;
}
add_filter('get_archives_link', 'adifier_archive_count_inline');
}

/*
* HEx to rgba
*/

if( !function_exists('adifier_hex2rgba') ){
function adifier_hex2rgba($color, $opacity = false) {
 
	$default = 'rgb(0,0,0)';

	//Return default if no color provided
	if(empty($color)){
		return $default; 
	}

	//Sanitize $color if "#" is provided 
	if ($color[0] == '#' ) {
		$color = substr( $color, 1 );
	}

	//Check if color has 6 or 3 characters and get values
	if (strlen($color) == 6) {
	    $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
	} elseif ( strlen( $color ) == 3 ) {
	    $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
	} else {
	    return $default;
	}

	//Convert hexadec to rgb
	$rgb =  array_map('hexdec', $hex);

	//Check if opacity is set(rgba or rgb)
	if($opacity){
		if(abs($opacity) > 1){
			$opacity = 1.0;	
		}
		
		$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
	} 
	else {
		$output = 'rgb('.implode(",",$rgb).')';
	}

	return $output;
}
}

include( get_theme_file_path( 'includes/classes/menu-walker.class.php' ) );
include( get_theme_file_path( 'includes/classes/class-tgm-plugin-activation.php' ) );	

?>