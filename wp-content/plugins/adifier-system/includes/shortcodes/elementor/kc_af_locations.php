<?php
if( !class_exists('Adifier_Elementor_kc_af_locations') ){
class Adifier_Elementor_kc_af_locations extends Adifier_Elementor_Base {
	protected function render() {
		$this->clear_unused_atts();
        
        if( !empty( $this->atts['grouped_terms'] ) ){
            foreach( $this->atts['grouped_terms'] as &$att ){
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