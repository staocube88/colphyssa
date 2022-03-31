<?php
if( !class_exists('Adifier_Elementor_kc_typed_text') ){
class Adifier_Elementor_kc_typed_text extends Adifier_Elementor_Base {
	protected function render() {
		$this->clear_unused_atts();
        
        if( !empty( $this->atts['texts'] ) ){
            foreach( $this->atts['texts'] as &$att ){
                $att = (object)$att;
            }
        }

        $this->includes_kc_file();
	} 
}
}
?>