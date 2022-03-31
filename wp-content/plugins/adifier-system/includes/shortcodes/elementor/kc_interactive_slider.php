<?php
if( !class_exists('Adifier_Elementor_kc_interactive_slider') ){
class Adifier_Elementor_kc_interactive_slider extends Adifier_Elementor_Base {
	protected function render() {
		$this->clear_unused_atts();
        
        if( !empty( $this->atts['grouped_slides'] ) ){
            foreach( $this->atts['grouped_slides'] as &$att ){
                if( !empty( $att['image'] ) ){
                    $att['image'] = $att['image']['id'];
                }

                $att = (object)$att;
            }
        }

        $this->includes_kc_file();
	} 
}
}
?>