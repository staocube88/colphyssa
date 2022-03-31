<?php

if( !function_exists('adifier_elementor_template_column') ){
function adifier_elementor_template_column( $columns ){
    return array_merge(
        $columns, 
        array( 
            'aeshortcode' => esc_html__('Shortcode', 'adifier')
        )
    );
}

add_filter('manage_elementor_library_posts_columns', 'adifier_elementor_template_column');
}

if( !function_exists( 'adifier_elementor_template_column_populate' ) ){
function adifier_elementor_template_column_populate( $column_key, $post_id ){
    if ( $column_key == 'aeshortcode' ) {
        ?>
        <input type="text" readonly value='[elementor-template id="<?php echo esc_attr( $post_id ) ?>"]' /> 
        <?php
    }
}
add_action('manage_elementor_library_posts_custom_column', 'adifier_elementor_template_column_populate', 10, 2 );
}

if( !function_exists( 'adifier_elementor_template_shortcode' ) ){
function adifier_elementor_template_shortcode( $atts ){
    $atts = shortcode_atts( array(
        'id' => '',
    ), $atts );

    $data = get_post_meta( $atts['id'], '_elementor_data', true );
    if( !empty( $data ) ){
        $data = json_decode( $data, true );
        if( !empty( $data ) ){
            $template = \Elementor\Plugin::instance()->elements_manager->create_element_instance(
                $data[0],
                []
            );
            $template->print_element();
        }
    }
}
}
add_shortcode('elementor-template', 'adifier_elementor_template_shortcode')

?>