<?php
if( !class_exists('Adifier_Elementor_kc_how_it_works') ){
class Adifier_Elementor_kc_how_it_works extends Adifier_Elementor_Base {
	protected function render() {
		$this->clear_unused_atts();
        
        if( !empty( $this->atts['hiw_item'] ) ){
            foreach( $this->atts['hiw_item'] as &$att ){
                $att = (object)$att;
            }
        }

        $this->includes_kc_file();
	} 
}
}
?>