<div class="navigation-wrap">
	<ul class="navigation list-inline list-unstyled">
		<?php
		if ( has_nav_menu( 'main-navigation' ) ) {
			wp_nav_menu( array(
				'theme_location'  	=> 'main-navigation',
				'container'			=> false,
				'echo'          	=> true,
				'items_wrap'        => '%3$s',
				'walker' 			=> new adifier_walker
			) );
		}
		?>
	</ul>
</div>