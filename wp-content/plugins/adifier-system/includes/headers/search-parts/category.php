<div class="styled-select">
	<?php 
	$category_selection = adifier_get_option( 'category_selection' );
	if( $category_selection == 'combined' ){
		$categories = adifier_get_taxonomy_hierarchy( 'advert-category', 0, true ); 
	}
	else{
		$categories = get_terms(array(
			'taxonomy' 	=> 'advert-category',
			'parent'	=> 0
		));		
	}
	?>
	<select name="category">
		<option value=""><?php esc_html_e( 'In Category', 'adifier' ) ?></option>
		<?php
		if( !empty($categories) ){
			addifier_hierarchy_select_taxonomy( $categories, 0, array(), true );
		}
		?>
	</select>
</div>