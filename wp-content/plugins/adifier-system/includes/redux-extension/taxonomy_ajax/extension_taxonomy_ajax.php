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
if ( ! class_exists( 'Redux_Extension_Taxonomy_Ajax', false ) ) {

	/**
	 * Class Redux_Extension_My_Extension
	 */
	class Redux_Extension_Taxonomy_Ajax extends Redux_Extension_Abstract {
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
		private $field_name = 'taxonomy_ajax';

		/**
		 * Set the friendly name of the extension.  This is for display purposes.  No underscores or dashes are required.
		 *
		 * @var string
		 */
		private $extension_name = 'Taxonomy Ajax';

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

			$this->add_field( 'taxonomy_ajax' );

			add_action('wp_ajax_taxonomy_ajax_ac', [ $this, 'taxonomy_ajax_action' ]);
		}

        public function taxonomy_ajax_action(){
            $taxonomy = sanitize_text_field( $_GET['taxonomy'] );
            $parent_only = sanitize_text_field( $_GET['parent_only'] );
            $q = sanitize_text_field( $_GET['q'] );
            $args = array(
                'name__like'    => $q,
                'taxonomy'      => $taxonomy,
                'hide_empty'    => false
            );
            if( $parent_only == 'true'  ){
                $args['parent'] = 0;
            };
            $terms = get_terms( $args );
            $terms_array = array();
            if( !empty( $terms ) ){
                foreach( $terms as $term ){
                    $terms_array[] = array(
                        'id' => $term->term_id,
                        'text' => wp_specialchars_decode($term->name),
                    );
                }
            }
            echo json_encode( $terms_array );
            die();
        } 
	}
}