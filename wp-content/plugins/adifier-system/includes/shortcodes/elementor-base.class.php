<?php
if( !class_exists('Adifier_Elementor_Base') ){
abstract class Adifier_Elementor_Base extends \Elementor\Widget_Base {

	public function get_name() {
		return str_replace( 'Adifier_Elementor_', '', static::class );
	}

	public function get_icon() {
		$name = $this->get_name();
		return 'af-'.str_replace('_', '-', $name);
	}

	public function get_title() {
		global $adifier_elementor_shortcodes;
		return $adifier_elementor_shortcodes[$this->get_name()]['shortcode_name'];
	}

	public function get_categories() {
		return array('adifier');
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'content_section',
			array(
				'label' => esc_html__( 'Content', 'plugin-name' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

        global $adifier_elementor_shortcodes;
        foreach( $adifier_elementor_shortcodes[$this->get_name()]['params'] as $key => $param ){
            if( !empty( $param['group_fields'] ) ){
                $repeater = new \Elementor\Repeater();
                foreach( $param['group_fields'] as $subkey => $subparam){
                    $repeater->add_control( $subkey, $subparam );        
                }

                $param['fields'] = $repeater->get_controls();
            }

            $this->add_control( $key, $param );
        }

		$this->end_controls_section();

	}
	
	protected function clear_unused_atts(){
		$this->atts = $this->get_settings_for_display();
		if( !empty( $this->atts ) ){
			foreach( $this->atts as $key => $att ){
				if( $att != '0' && empty ( $att ) ){
					unset( $this->atts[$key] );
				}
			}
		}
	}

	protected function includes_kc_file(){
		$atts = $this->atts;
		include( get_theme_file_path( 'includes/shortcodes/kingcomposer/'.$this->get_name().'.php' ) );
	}
    
	protected function render() {
		$this->clear_unused_atts();
		$this->includes_kc_file();
        
	}    

}
}
?>