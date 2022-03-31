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
if ( ! class_exists( 'Redux_Grouped_Adifier', false ) ) {

	/**
	 * Main ReduxFramework_options_object class
	 *
	 * @since       1.0.0
	 */
	class Redux_Grouped_Adifier extends Redux_Field {
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
                'repeatable'          => false,
                'allow_empty'         => false
			);

			$this->field = wp_parse_args( $this->field, $defaults );
		}

        private function generate_groups( $group = '' ){
            $values = !empty( $group ) ? explode( '|', $group ) : array();
            ?>
            <div class="grouped-adifier-group">
                <?php
                for( $i=0; $i<sizeof($this->field['subfields']); $i++ ){
                    ?>
                        <label><?php echo esc_html( $this->field['subfields'][$i] ); ?></label>
                        <input type="text" value="<?php echo isset( $values[$i] )  ? esc_attr( $values[$i] ) : '' ?>" class="regular-text af-gap <?php echo $this->field['allow_empty'] == true ? 'can-empty' : '' ?>">
                    <?php
                }
                ?>
                <a href="javascript:void(0);" class="grouped-adifier-remove button"><?php esc_html_e( 'Remove', 'adifier' ) ?></a>
            </div>            
            <?php
        }

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @return      void
		 */
        public function render() {
            $groups = !empty( $this->value ) ? explode( '+', $this->value ) : array();
            if( !empty( $groups ) ){
                foreach( $groups as $group ){
                    $this->generate_groups( $group );
                }
            }
            else{
                $this->generate_groups();
            }
            if( $this->field['repeatable'] ):
                ?>
                <a href="javascript:void(0);" class="grouped-adifier-add button button-primary"><?php esc_html_e( 'Add New', 'adifier' ) ?></a>
                <?php
            endif;
            ?>
            <input type="text" value="<?php echo esc_attr( $this->value ); ?>" name="<?php echo esc_attr( $this->field['name'].$this->field['name_suffix'] ); ?>" class="hidden grouped-adifier-value">
            <?php
        } 

		/**
		 * Enqueue Function.
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @return      void
		 */
		public function enqueue() {
            wp_enqueue_script(
                'redux-field-grouped-ajax', 
                $this->url . 'field_grouped_adifier.js', 
                array( 'jquery' ),
                time(),
                true
            );

            wp_enqueue_style( 'redux-field-grouped-ajax', $this->url . 'field_grouped_adifier.css' );
		}
	}
}