<?php
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles', 12 );
function theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    if( is_rtl() ){
        wp_enqueue_style( 'parent-rtl', get_template_directory_uri() . '/rtl.css' );
    }
}

?>