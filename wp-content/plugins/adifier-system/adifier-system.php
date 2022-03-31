<?php
/*
Plugin Name: Adifier System
Plugin URI: http://themeforest.net/user/spoonthemes
Description: Adifier system
Version: 3.0.9
Author: SpoonThemes
Author URI: http://themeforest.net/user/spoonthemes
License: GNU General Public License version 3.0
*/



/*
Include file from plugin if it is not available in theme
*/
if( !function_exists('adifier_get_theme_filepath') ){
function adifier_get_theme_filepath($path, $file){
	if( !file_exists( $path ) ){
		$plugin_path = plugin_dir_path( __FILE__ ).$file;
		if( file_exists( $plugin_path ) ){
			$path = $plugin_path;
		}
	}

	return $path;
}
}
add_filter( 'theme_file_path', 'adifier_get_theme_filepath', 10, 2 );

/*
Include functions from functions.php in order to maintain  users child theme functions Functions related to core WP are left in themes functions.php
*/
if( !function_exists('adifier_system_functions') ){
function adifier_system_functions(){
	include( plugin_dir_path( __FILE__ ).'functions.php' );
}
}
add_action('after_setup_theme', 'adifier_system_functions');

/*
register ad custom post type and built in taxonomies
*/
function adifier_create_post_types(){
	$advert_args = array(
		'labels' => array(
			'name' => esc_html__( 'Ads', 'adifier' ),
			'singular_name' => esc_html__( 'Ad', 'adifier' )
		),
		'public' => true,
		'show_in_rest' => true,
		'menu_icon' => 'dashicons-megaphone',
		'has_archive' => true,
		'supports' => array(
			'title',
			'thumbnail',
			'editor',
			'author',
			'comments',
			'excerpt'
		)
	);
	if( class_exists('ReduxFramework') && function_exists('adifier_get_option') ){
		$advert_slug = adifier_get_option( 'trans_advert' );
		if( !empty( $advert_slug ) ){
			$advert_args['rewrite'] = array( 'slug' => $advert_slug );
		}
	}
	register_post_type( 'advert', $advert_args );

	/* ORDERS CUSTOM POST TYPE */
	$orders_args = array(
		'labels' => array(
			'name' => esc_html__( 'Orders', 'adifier' ),
			'singular_name' => esc_html__( 'Order', 'adifier' )
		),
		'public' => true,
		'menu_icon' => 'dashicons-cart',
		'has_archive' => false,
		'show_in_rest'	=> true,
		'supports' => array(
			'title',
			'author'
		)
	);
	register_post_type( 'ad-order', $orders_args );		

	/* PROJECT TAXONIMIES */
	$taxonomies[] = array(
		'slug' 			=> 'advert-category',
		'plural' 		=> esc_html__( 'Categories', 'adifier' ),
		'singular' 		=> esc_html__( 'Category', 'adifier' ),
		'hierarchical' 	=> true,
		'post_type' 	=> 'advert',
		'rewrite' 		=> class_exists('ReduxFramework') && function_exists('adifier_get_option') ? adifier_get_option( 'trans_advert-category' ) : ''
	);

	if( function_exists('adifier_get_option') && adifier_get_option('use_predefined_locations')  == 'yes' ){
		$taxonomies[] = array(
			'slug' 			=> 'advert-location',
			'plural' 		=> esc_html__( 'Locations', 'adifier' ),
			'singular' 		=> esc_html__( 'Location', 'adifier' ),
			'hierarchical' 	=> true,
			'post_type' 	=> 'advert',
			'rewrite' 		=> class_exists('ReduxFramework') && function_exists('adifier_get_option') ? adifier_get_option( 'trans_advert-location' ) : ''
		);
	}

	for( $i=0; $i<sizeof( $taxonomies ); $i++ ){
		$val = $taxonomies[$i];
		$tax_args = array(
			'show_in_rest'	=> true,
			'label' => $val['plural'],
			'hierarchical' => $val['hierarchical'],
			'labels' => array(
				'name' 							=> $val['plural'],
				'singular_name' 				=> $val['singular'],
				'menu_name' 					=> $val['plural'],
				'all_items'						=> esc_html__( 'All ', 'adifier' ).$val['plural'],
				'edit_item'						=> esc_html__( 'Edit ', 'adifier' ).$val['singular'],
				'view_item'						=> esc_html__( 'View ', 'adifier' ).$val['singular'],
				'update_item'					=> esc_html__( 'Update ', 'adifier' ).$val['singular'],
				'add_new_item'					=> esc_html__( 'Add New ', 'adifier' ).$val['singular'],
				'new_item_name'					=> esc_html__( 'New ', 'adifier').$val['singular'].__( ' Name', 'adifier' ),
				'parent_item'					=> esc_html__( 'Parent ', 'adifier' ).$val['singular'],
				'parent_item_colon'				=> esc_html__( 'Parent ', 'adifier').$val['singular'].__( ':', 'adifier' ),
				'search_items'					=> esc_html__( 'Search ', 'adifier' ).$val['plural'],
				'popular_items'					=> esc_html__( 'Popular ', 'adifier' ).$val['plural'],
				'separate_items_with_commas'	=> esc_html__( 'Separate ', 'adifier').strtolower( $val['plural'] ).__( ' with commas', 'adifier' ),
				'add_or_remove_items'			=> esc_html__( 'Add or remove ', 'adifier' ).strtolower( $val['plural'] ),
				'choose_from_most_used'			=> esc_html__( 'Choose from the most used ', 'adifier' ).strtolower( $val['plural'] ),
				'not_found'						=> esc_html__( 'No ', 'adifier' ).strtolower( $val['plural'] ).__( ' found', 'adifier' ),
			),

		);
	
		if( !empty( $val['rewrite'] ) ){
			$tax_args['rewrite'] = array( 'slug' => $val['rewrite'] );
		}

		register_taxonomy( $val['slug'], $val['post_type'], $tax_args );
	}	

}
add_action('init', 'adifier_create_post_types' );

/*
Create required tables
*/
if( !function_exists( 'adifier_create_tables' ) ){
function adifier_create_tables(){
	global $wpdb;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$wpdb->prefix}adifier_cf_groups (
		group_id mediumint(9) NOT NULL AUTO_INCREMENT,
		name varchar(255),
		categories varchar(2000),
		PRIMARY KEY group_id (group_id),
		KEY categories (categories)
	) $charset_collate;";
	dbDelta( $sql );

	$sql = "CREATE TABLE {$wpdb->prefix}adifier_cf (
		cf_id mediumint(9) NOT NULL AUTO_INCREMENT,
		group_id mediumint(9),
		cf_label varchar(255),
		cf_slug varchar(28),
		cf_orderby varchar(20),
		cf_description varchar(255),
		cf_type int(2),
		cf_fixed int(1),
		cf_order int(3) DEFAULT 0,
		cf_is_hidden int(1) DEFAULT 0,
		cf_is_mandatory int(1) DEFAULT 0,
		PRIMARY KEY cf_id (cf_id),
		KEY cf_label (cf_label)
	) $charset_collate;";
	dbDelta( $sql );

	$sql = "CREATE TABLE {$wpdb->prefix}adifier_advert_data (
		meta_id mediumint(9) NOT NULL AUTO_INCREMENT,
		post_id mediumint(9),
		latitude decimal(10,8),
		longitude decimal(11,8),
		price decimal(20,2),
		sale_price decimal(20,2),
		expire bigint(11),
		urgent bigint(11),
		sold int(1),
		views mediumint(9),
		views_data longtext,
		type int(1),
		cond int(1),
		bids mediumint DEFAULT '0',
		start_price decimal(20,2),
		currency varchar(5),
		exp_info varchar(1) DEFAULT '0',
		PRIMARY KEY meta_id (meta_id),
		KEY post_id (post_id),
		KEY expire (expire)
	) $charset_collate;";
	dbDelta( $sql );	

	$sql = "CREATE TABLE {$wpdb->prefix}adifier_bids (
		bid_id mediumint(9) NOT NULL AUTO_INCREMENT,
		post_id mediumint(9),
		user_id mediumint(9),
		bid decimal(20,2),
		ip varchar(15),
		created bigint(11),
		PRIMARY KEY bid_id (bid_id),
		KEY post_id (post_id)
	) $charset_collate;";
	dbDelta( $sql );

	$sql = "CREATE TABLE {$wpdb->prefix}adifier_conversations (
		con_id mediumint(9) NOT NULL AUTO_INCREMENT,
		advert_title text,
		post_id mediumint(9),
	   	sender_id mediumint(9),
	   	recipient_id mediumint(9),
	   	sender_delete int(1),
	  	recipient_delete int(1),
	  	last_message_id mediumint(9),
	  	sender_review int(1),
	  	recipient_review int(1),
	  	invert_review int(1),
	  	PRIMARY KEY con_id (con_id),
	  	KEY last_message_id (last_message_id)
	) $charset_collate;";
	dbDelta( $sql );

	$sql = "CREATE TABLE {$wpdb->prefix}adifier_conversation_messages (
		message_id mediumint(9) NOT NULL AUTO_INCREMENT,
		con_id mediumint(9),
		source_id mediumint(9),
	  	message text,
	  	is_read int(1),
	  	created bigint(11),
	  	PRIMARY KEY message_id (message_id),
	  	KEY con_id (con_id)
	) $charset_collate;";
	dbDelta( $sql );

	$sql = "CREATE TABLE {$wpdb->prefix}adifier_reviews (
		review_id mediumint(9) NOT NULL AUTO_INCREMENT,
		reviewer_id mediumint(9),
		reviewed_id mediumint(9),
		con_id mediumint(9),
		review text,
		rating int(1),
		advert_title text,
	  	is_seller int(1),
	  	created bigint(11),
	  	parent mediumint(9),
	  	PRIMARY KEY review_id (review_id),
	  	KEY parent (parent)
	) $charset_collate;";
	dbDelta( $sql );	

	$sidebars_widgets = get_option( 'adifier_sidebar_widgets' );
	if( !empty( $sidebars_widgets ) ){
		update_option( 'sidebars_widgets', $sidebars_widgets );	
	}

}
register_activation_hook( __FILE__, 'adifier_create_tables' );
}

function adifier_save_widgets(){
	$sidebars_widgets = get_option( 'sidebars_widgets' );
	update_option( 'adifier_sidebar_widgets', $sidebars_widgets );
}
register_deactivation_hook( __FILE__, 'adifier_save_widgets');

if( !function_exists('adifier_disable_admin_bar') ){
function adifier_disable_admin_bar( $show_admin_bar ){
	if ( ! current_user_can( 'manage_options' ) ) {
	    return false;
	}
	return $show_admin_bar;
}
}
add_filter('show_admin_bar', 'adifier_disable_admin_bar', 10, 1);

if( !function_exists('adifier_parse_svg') ){
function adifier_parse_svg( $attachment_id ){
	$file = get_attached_file( $attachment_id );
	if( !empty( $file ) ){
		$content = file_get_contents( $file );
		$content = preg_replace('/<\?xml[^>]+\?>/im', '', $content);
		return $content;
	}
}
}

if( !function_exists('adifier_allow_svg') ){
function adifier_allow_svg($mimes) {
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter('upload_mimes', 'adifier_allow_svg');
}


if( !function_exists('adifier_menu_page') ){
function adifier_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function  ) {
	add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function );
}
}

if( !function_exists( 'adifier_wp_send_mail' ) ){
function adifier_wp_send_mail( $to, $subject, $message, $headers = array() ){
	return wp_mail( $to, $subject, $message, $headers );
}
}

if( !function_exists( 'adifier_b64_encode' ) ){
function adifier_b64_encode( $string ){
	return base64_encode( $string );
}
}

if( !function_exists( 'adifier_get_IP' ) ){
function adifier_get_IP(){
	return $_SERVER['REMOTE_ADDR'];
}
}

if( !function_exists('adifier_script_tag_handler') ){
function adifier_script_tag_handler( $funct ){
	add_action( 'script_loader_tag', $funct, 10, 3 );
}
}

remove_filter('wp_get_attachment_image_src', 'kc_get_attachment_image_src', 999, 4);

if( !function_exists('adifier_register_meta_box') ){
function adifier_register_meta_box( $post_type, $post ){
	do_action( 'adifier_amb_action', $post_type, $post );
}
add_action( 'add_meta_boxes', 'adifier_register_meta_box', 10, 2 );
}

if( !function_exists('adifier_amb') ){
function adifier_amb( $id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args = null ) {
	add_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args);
}
}

if( !function_exists('adifier_server_variables') ){
function adifier_server_variables(){
	return !empty( $_SERVER ) ? $_SERVER : array();
}
}


if( !function_exists('adifier_payment_output') ){
function adifier_payment_output(){
	return file_get_contents('php://input');
}
}

if( !function_exists('adifier_output') ){
function adifier_output( $data ){
	echo $data;
}
}

if( !function_exists('adifier_clear_filter') ){
function adifier_clear_filter( $hook, $func ){
	remove_filter( $hook, $func );
}
}

?>