<?php
if( !class_exists('Adifier_Elementor_kc_text_slider') ){
class Adifier_Elementor_kc_text_slider extends Adifier_Elementor_Base {
	protected function render() {
		$this->clear_unused_atts();
        
        if( !empty( $this->atts['grouped_slides'] ) ){
            foreach( $this->atts['grouped_slides'] as &$att ){
                $att = (object)$att;
            }
        }

        $this->includes_kc_file();
	} 
}
}
?>