<?php
/**
 * Redux My Extension Extension Class
 * Short description.
 *
 * @package Redux Extentions
 * @class   Redux_Extension_My_Extension
 * @version 1.0.0
 *
 * There is no free support for extension development.  This example is 'as is'.  If you need assitance,
 * please consider a Premium Support purchase: https://redux.io/extension/premium-support
 *
 * Please be sure to replce ALL instances of "My Extension" and "My_Extension" with the name of your actual
 * extension.  Please also change the file name so the 'my-extension' portion is also the name of your extension.
 * Please use dashes and not underscores in the filename.  Please use underscores instead of dashes in the classname.
 *
 * Thanks!  :)
 */

defined( 'ABSPATH' ) || exit;

// Don't duplicate me!
if ( ! class_exists( 'Redux_Extension_Post_Type_Ajax', false ) ) {

	/**
	 * Class Redux_Extension_My_Extension
	 */
	class Redux_Extension_Post_Type_Ajax extends Redux_Extension_Abstract {
		/**
		 * Set extension version.
		 *
		 * @var string
		 */
		public static $version = '1.0.0';

		/**
		 * Set the name of the field.  Ideally, this will also be your extension's name.
		 * Please use underscores and NOT dashes.
		 *
		 * @var string
		 */
		private $field_name = 'post_type_ajax';

		/**
		 * Set the friendly name of the extension.  This is for display purposes.  No underscores or dashes are required.
		 *
		 * @var string
		 */
		private $extension_name = 'Post Type Ajax';

		/**
		 * Set the minumum required version of Redux here (optional).
		 *
		 * Leave blank to require no minimum version.  This allows you to specify a minimum required version of
		 * Redux in the event you do not want to support older versions.
		 *
		 * @var string
		 */
		private $minimum_redux_version = '4.0.0';

		/**
		 * Redux_Extension_my_extension constructor.
		 *
		 * @param object $parent ReduxFramework pointer.
		 */
		public function __construct( $parent ) {
			parent::__construct( $parent, __FILE__ );

			if ( is_admin() && ! $this->is_minimum_version( $this->minimum_redux_version, self::$version, $this->extension_name ) ) {
				return;
			}

			$this->add_field( 'post_type_ajax' );

			add_action('wp_ajax_post_type_ajax_ac', [ $this, 'post_type_ajax_action' ]);
		}

        public function post_type_ajax_action(){
            $post_type = sanitize_text_field( $_GET['post_type'] );
            $q = sanitize_text_field( $_GET['q'] );
            $additional = !empty( $_GET['additional'] ) ? map_deep( $_GET['additional'], 'sanitize_text_field' ) : array();
            $args = array(
                'post_type' => $post_type,
                'posts_per_page' => '-1',
                'post_status' => 'publish',
                's' => $q
            );
            $args = $args + $additional;
            $posts = get_posts( $args );
            $posts_array = array();
            if( !empty( $posts ) ){
                foreach( $posts as $post ){
                    $posts_array[] = array(
                        'id' => $post->ID,
                        'text' => wp_specialchars_decode($post->post_title),
                    );
                }
            }
            echo json_encode( $posts_array );
            die();
        }
	}
}