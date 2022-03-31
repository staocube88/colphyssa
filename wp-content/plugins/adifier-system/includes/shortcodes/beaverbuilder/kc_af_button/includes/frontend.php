<?php
$unique_id = 'btn_'.uniqid();

if( $settings->link == '#submit' ){
	if( is_user_logged_in() ){
		$settings->link =  esc_url( add_query_arg( 'screen', 'new', get_author_posts_url( get_current_user_id() ) ));	
	}
	else{
		$settings->link = '#login';
		$settings->class .= 'af-modal';
	}
}
else if( is_user_logged_in() && in_array( $settings->link, array( '#login', '#register' ) )){
	$settings->link = esc_url( get_author_posts_url( get_current_user_id() ));
}
else if( !is_user_logged_in() && in_array( $settings->link, array( '#login', '#register' ) )){
	$settings->class .= ' af-modal';
}


?>
<div class="a-wrapper-<?php echo $unique_id; ?>">
    <a href="<?php echo $settings->link ?>" class="<?php echo $unique_id; ?> <?php echo $settings->class ?>">
        <?php echo $settings->text ?>
    </a>
</div>
<style scoped>
    .<?php echo $unique_id ?>,
    .<?php echo $unique_id ?>:active
    .<?php echo $unique_id ?>:focus{
        <?php echo !empty( $settings->border_width ) ? 'border: '.$settings->border_width.' solid #'.$settings->border_color .';' : '' ?>
        <?php echo !empty( $settings->border_radius ) ? 'border-radius: '. $settings->border_radius.';' : '' ?>
        <?php echo !empty( $settings->font_color ) ? 'color: #'.$settings->font_color .';' : '' ?>
        <?php echo !empty( $settings->bg_color ) ? 'background: #'.$settings->bg_color .';' : '' ?>
        <?php echo !empty( $settings->padding ) ? 'padding: '.$settings->padding.';' : '' ?>
    }

    .a-wrapper-<?php echo $unique_id; ?>{
        <?php 
        if( $settings->align == 'left' ){
            echo 'text-align: left;';
        } 
        else if( $settings->align == 'center' ){
            echo 'text-align: center;';
        }
        else if( $settings->align == 'right' ){
            echo 'text-align: right;';
        }
        ?>        
    }

    .<?php echo $unique_id ?>:hover{
        <?php echo !empty( $settings->border_color_hvr ) ? 'border-color: #'.$settings->border_color_hvr.';' : '' ?>
        <?php echo !empty( $settings->font_color_hvr ) ? 'color: #'.$settings->font_color_hvr.';' : '' ?>
        <?php echo !empty( $settings->bg_color_hvr ) ? 'background: #'.$settings->bg_color_hvr.';' : '' ?>
    }
</style>