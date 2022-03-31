<?php
if( !class_exists('Adifier_Elementor_kc_slider_bg_text') ){
class Adifier_Elementor_kc_slider_bg_text extends Adifier_Elementor_Base {
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