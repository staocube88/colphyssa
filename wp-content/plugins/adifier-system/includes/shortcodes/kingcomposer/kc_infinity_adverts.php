<?php
extract( shortcode_atts( array(
	'ads_source' 		=> 'by_category',
	'topads' 			=> 'no',
	'post_ids' 			=> '',
	'post_number' 		=> '6',
	'category_ids' 		=> '',
	'location_ids' 		=> '',
	'type' 				=> '',
	'orderby' 			=> 'date',
	'order' 			=> 'DESC',
	'style' 			=> 'grid',
	'items_in_row' 		=> '4'
), $atts ) );

$post_number = empty( $post_number ) ? 6 : $post_number;


$cpage = !empty( $page ) ? $page : 1;

$args = array(
    'paged'           => $cpage,
    'post_status'    => 'publish',
    'posts_per_page' => $post_number,
    'orderby'		 => $orderby,
    'order'		 	 => $order,    
);

if( $topads == 'yes' ){
    $args['post__in'] = adifier_topads_ids_list();
}

if( !empty( $category_ids ) ){
    $args['tax_query'] = array(
        array(
            'taxonomy' 	=> 'advert-category',
            'terms'		=> is_array( $category_ids ) ? $category_ids : explode( ',', $category_ids )
        )
    );
}

if( !empty( $location_ids ) ){
    $args['tax_query'] = array(
        array(
            'taxonomy' 	=> 'advert-location',
            'terms'		=> is_array( $location_ids ) ? $location_ids : explode( ',', $location_ids )
        )
    );
}	

if( !empty( $type ) ){
	$args['type'] = $type;
}

$adverts = new Adifier_Advert_Query( $args );
if( $adverts->have_posts() ){
    $total = $adverts->found_posts;
    $has_more = 0;
    if( ceil( $total / $post_number ) > $cpage ) {
        $has_more = 1;
    }
    ?>
    <div class="adverts-list clearfix af-items-<?php echo esc_attr( $items_in_row ) ?> adverts-infinity-load" data-has_more="<?php echo $has_more; ?>" data-page="1" data-atts="<?php echo base64_encode( json_encode( $atts ) ) ?>">
        <?php
        while( $adverts->have_posts() ){
            $adverts->the_post();
            ?>
            <div class="af-item-wrap">
                <?php  include( get_theme_file_path( 'includes/advert-boxes/'.$style.'.php' ) ); ?>
            </div>
        <?php
        }
        ?>
    </div>
    <?php
}
wp_reset_postdata();
?>