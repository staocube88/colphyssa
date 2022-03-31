<?php
/**
 * Redux My Extension Field Class
 * Short description.
 *
 * @package Redux Extentions
 * @class   Redux_Extension_My_Extension
 * @version 1.0.0
 *
 * There is no free support for extension development.  This example is 'as is'.  If you need assitance,
 * please consider a Premium Support purchase: https://redux.io/extension/premium-support
 *
 * Please be sure to replace ALL instances of "My Extension" and "My_Extension" with the name of your actual
 * extension.  Please also change the file name so the 'my-extension' portion is also the name of your extension.
 * Please use dashes and not underscores in the filename.  Please use underscores instead of dashes in the classname.
 */

defined( 'ABSPATH' ) || exit;

// Don't duplicate me!
if ( ! class_exists( 'Redux_Post_Type_Ajax', false ) ) {

	/**
	 * Main ReduxFramework_options_object class
	 *
	 * @since       1.0.0
	 */
	class Redux_Post_Type_Ajax extends Redux_Field {
		/**
		 * Field Constructor.
		 * Required - must call the parent constructor, then assign field and value to vars
		 *
		 * @param array  $field Field array.
		 * @param mixed  $value Field values.
		 * @param object $parent ReduxFramework pointer.
		 */
		public function __construct( array $field, $value, $parent ) {
			parent::__construct( $field, $value, $parent );

			// Set default args for this field to avoid bad indexes. Change this to anything you use.
			$defaults = array(
                'post_type'          => '',
                'multi'             => false,
                'sortable'          => false,
                'additional'        => array()
			);

			$this->field = wp_parse_args( $this->field, $defaults );
		}
		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @return      void
		 */
        public function render() {
            $multi = ( isset( $this->field['multi'] ) && $this->field['multi'] ) ? 'true' : 'false';

            $sortable = ( isset( $this->field['sortable'] ) && $this->field['sortable'] ) ? 'true' : 'false';

            $placeholder = ( isset( $this->field['placeholder'] ) ) ? esc_attr( $this->field['placeholder'] ) : esc_html__( 'Select an item', 'adifier' );

            if ( ! empty( $this->field['width'] ) ) {
                $width = ' style="' . $this->field['width'] . '"';
            } else {
                $width = ' style="width: 40%;"';
            }

            $selected = array();
            if( !empty( $this->value ) ){
                $this->value = is_array( $this->value ) ? $this->value : explode(',', $this->value);
                foreach ( $this->value as $value ) {
                    $post = get_post( $value );
                    if( !empty( $post ) ){
                        $selected[] = '<option value="'.$post->ID.'" selected="selected">'.$post->post_title.'</option>';
                    }
                }
            }

            echo '<select data-additional="'.htmlspecialchars( json_encode( $this->field['additional'] ) ).'" data-post_type="'.esc_attr( $this->field['post_type'] ).'" '.( $multi ? 'multiple' : '' ).' data-sortable="'.$sortable.'" name="' . $this->field['name'] . $this->field['name_suffix'] . '[]" class="redux-select-item post-type-ajax' . $this->field['class'] . '"' . $width . ' >';
            echo implode( '', $selected );
            echo '</select>';
        } 

		/**
		 * Enqueue Function.
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @return      void
		 */
		public function enqueue() {
            wp_enqueue_script(
                'redux-field-post-type-ajax', 
                $this->url . 'field_post_type_ajax.js', 
                array( 'jquery' ),
                time(),
                true
            );
		}
	}
}