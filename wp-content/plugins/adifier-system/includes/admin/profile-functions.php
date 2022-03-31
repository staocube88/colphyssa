<?php
/*
* User addition data
*/
if( !function_exists('adifier_add_user_meta') ){
function adifier_add_user_meta( $user ){
    ?>
        <h3><?php esc_html_e( 'User Details', 'adifier' ) ?></h3>
        <?php
        $user_active_status = get_user_meta( $user->ID, 'user_active_status', true );
		$author_meta = array(
			'phone'			=> get_user_meta( $user->ID, 'phone', true ),
			'hide_phone'	=> get_user_meta( $user->ID, 'hide_phone', true ),
			'facebook'		=> get_user_meta( $user->ID, 'facebook', true ),
			'twitter'		=> get_user_meta( $user->ID, 'twitter', true ),
			'youtube'		=> get_user_meta( $user->ID, 'youtube', true ),
			'linkedin'		=> get_user_meta( $user->ID, 'linkedin', true ),
			'instagram'		=> get_user_meta( $user->ID, 'instagram', true ),	
			'website'		=> get_user_meta( $user->ID, 'website', true ),	
            'avatar_id'     => get_user_meta( $user->ID, 'avatar_id', true ),   
            'af_adverts'    => get_user_meta( $user->ID, 'af_adverts', true ),  
            'af_subscribe'  => get_user_meta( $user->ID, 'af_subscribe', true ),
			'af_location_id'=> (array)get_user_meta( $user->ID, 'af_location_id', true ),
		);
		$location = get_user_meta( $user->ID, 'location', true );
		if( empty( $location ) ){
			$location = array(
				'lat' 		=> '',
				'long' 		=> '',
				'country' 	=> '',
				'state' 	=> '',
				'city' 		=> '',
				'street' 	=> '',
			);
		}

        /* get user extra data */
        extract( $author_meta );
        ?>
        <table class="form-table">
            <tr>
                <th><label for="user_active_status"><?php esc_html_e( 'User Status', 'adifier' ); ?></label></th>
                <td>
                    <select name="user_active_status">
                        <option <?php echo empty( $user_active_status ) || $user_active_status == 'inactive' ? 'selected="selected"' : '' ?> value="inactive"><?php esc_html_e( 'Inactive', 'adifier' ) ?></option>
                        <option <?php echo  $user_active_status == 'active' ? esc_attr('selected="selected"') : '' ?> value="active"><?php esc_html_e( 'Active', 'adifier' ) ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                	<label for="phone"><?php esc_html_e( 'Phone', 'adifier' ); ?></label>
                </th>
                <td>
                    <input type="text" class="regular-text" value="<?php echo esc_attr( $phone ) ?>" name="phone" />
                    <input type="checkbox" class="regular-text" value="1" name="hide_phone" id="hide_phone" <?php checked( 1, $hide_phone ) ?> />
                    <label for="hide_phone"><?php esc_html_e( 'Hide phone?', 'adifier' ); ?></label>
                </td>
            </tr>
            <tr>
                <th>
                	<label for="facebook"><?php esc_html_e( 'Facebook', 'adifier' ); ?></label>
                </th>
                <td>
                    <input type="text" class="regular-text" value="<?php echo esc_attr( $facebook ) ?>" name="facebook" />
                </td>
            </tr>
            <tr>
                <th>
                	<label for="twitter"><?php esc_html_e( 'Twitter', 'adifier' ); ?></label>
                </th>
                <td>
                    <input type="text" class="regular-text" value="<?php echo esc_attr( $twitter ) ?>" name="twitter" />
                </td>
            </tr>
            <tr>
                <th>
                	<label for="youtube"><?php esc_html_e( 'YouTube', 'adifier' ); ?></label>
                </th>
                <td>
                    <input type="text" class="regular-text" value="<?php echo esc_attr( $youtube ) ?>" name="youtube" />
                </td>
            </tr>
            <tr>
                <th>
                	<label for="linkedin"><?php esc_html_e( 'LinkedIn', 'adifier' ); ?></label>
                </th>
                <td>
                    <input type="text" class="regular-text" value="<?php echo esc_attr( $linkedin ) ?>" name="linkedin" />
                </td>
            </tr>
            <tr>
                <th>
                	<label for="instagram"><?php esc_html_e( 'Instagram', 'adifier' ); ?></label>
                </th>
                <td>
                    <input type="text" class="regular-text" value="<?php echo esc_attr( $instagram ) ?>" name="instagram" />
                </td>
            </tr>
            <tr>
                <th>
                	<label for="website"><?php esc_html_e( 'Website', 'adifier' ); ?></label>
                </th>
                <td>
                    <input type="text" class="regular-text" value="<?php echo esc_attr( $website ) ?>" name="website" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="af_adverts"><?php esc_html_e( 'Packages', 'adifier' ); ?></label>
                </th>
                <td>
                    <input type="text" class="regular-text" value="<?php echo esc_attr( $af_adverts ) ?>" name="af_adverts" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="af_subscribe"><?php esc_html_e( 'Subscription', 'adifier' ); ?></label>
                </th>
                <td>
                    <input type="text" class="regular-text af-subscribe" value="<?php echo !empty( $af_subscribe ) ? date('m/d/Y H:i:s', $af_subscribe) : ''; ?>" name="af_subscribe" />
                </td>
            </tr>
            <tr>
                <th>
                	<label><?php esc_html_e( 'Location', 'adifier' ); ?></label>
                </th>
                <td>
					<div class="adifier-map">

                        <?php
                        $locations = adifier_get_taxonomy_hierarchy( 'advert-location' );
                        if( !empty( $locations ) ){
                            ?>
                            <div class="form-group no-margin">
                                <label for="location_id"><?php esc_html_e( 'Location *', 'adifier' ) ?></label>
                                <div class="styled-select">
                                    <select name="location_id" id="location_id">
                                        <option value=""><?php esc_html_e( '- Select -', 'adifier' ) ?></option>
                                        <?php
                                        if( !empty( $locations ) ){
                                            addifier_hierarchy_select_taxonomy( $locations, 0, $af_location_id );
                                        }
                                        ?>
                                    </select>
                                </div>
                                <p class="description"><?php esc_html_e( 'Select location', 'adifier' ); ?></p> 
                            </div>                      
                            <?php
                        }
                        ?>

                        <?php if( adifier_get_option( 'use_google_location' ) == 'yes' ): ?>
    						<?php adifier_get_map_autocomplete_html() ?>
    						<div class="map-holder"></div>

    						<input type="hidden" name="lat" value="<?php echo esc_attr( $location['lat'] ) ?>">
    						<input type="hidden" name="long" value="<?php echo esc_attr( $location['long'] ) ?>">

    						<div class="form-group">
    							<label for="country"><?php esc_html_e( 'Country', 'adifier' ) ?></label>
    							<input type="text" name="country" id="country" class="regular-text" value="<?php echo esc_attr( $location['country'] ) ?>">
    						</div>

    						<div class="form-group">
    							<label for="state"><?php esc_html_e( 'State', 'adifier' ) ?></label>
    							<input type="text" name="state" id="state" class="regular-text" value="<?php echo esc_attr( $location['state'] ) ?>">
    						</div>

    						<div class="form-group">
    							<label for="city"><?php esc_html_e( 'City', 'adifier' ) ?></label>
    							<input type="text" name="city" id="city" class="regular-text" value="<?php echo esc_attr( $location['city'] ) ?>">
    						</div>
    						
    						<div class="form-group">
    							<label for="street"><?php esc_html_e( 'Street', 'adifier' ) ?></label>
    							<input type="text" name="street" id="street" class="regular-text" value="<?php echo esc_attr( $location['street'] ) ?>">
    						</div>
                        <?php endif; ?>

					</div>
                </td>
            </tr>
            <tr>
                <th>
                	<label><?php esc_html_e( 'Avatar', 'adifier' ); ?></label>
                </th>
                <td>
                	<div class="af-image-selection">
	                    <div class="af-image-holder">
	                        <?php echo get_avatar( $user->ID, 'thumbnail' ); ?>
	                    </div>
                    	<a href="javascript:void(0);" class="button af-image-select"><?php esc_html_e( 'Change Avatar', 'adifier' ) ?></a>
                    	<a href="javascript:void(0);" class="button af-image-remove">X</a>
                    	<input type="hidden" name="avatar_id" value="<?php echo esc_attr( $avatar_id ) ?>" />
                    </div>
                </td>
            </tr>
        </table>
    <?php
}
add_action( 'show_user_profile', 'adifier_add_user_meta' );
add_action( 'edit_user_profile', 'adifier_add_user_meta' );
}

/*
* Save user details when we arrive from backend profile page
*/
if( !function_exists('adifier_save_user_meta') ){
function adifier_save_user_meta( $user_id ){
    update_user_meta( $user_id, 'af_adverts', sanitize_text_field( $_POST['af_adverts'] ) );
    $af_subscribe = empty($_POST['af_subscribe']) ? '' : strtotime( sanitize_text_field( $_POST['af_subscribe'] ) );
    update_user_meta( $user_id, 'af_subscribe', $af_subscribe );
    $user_active_status = get_user_meta( $user_id, 'user_active_status', true );
    update_user_meta( $user_id, 'user_active_status', sanitize_text_field( $_POST['user_active_status'] ) );
	adifier_update_account( $user_id );

    if( $user_active_status !== $_POST['user_active_status'] ){
        $user = get_user_by( 'ID', $user_id );
        $text = esc_html__( 'Your account has been disabled by administration of the site. If you would like to raise a dispute reply to this mail', 'adifier' );
        $inactive = get_option( 'adifier_inactive_users' );
        if( $_POST['user_active_status'] == 'active' ){
            if( !empty( $inactive[$user_id] ) ){
                unset( $inactive[$user_id] );
            }
            $text = esc_html__( 'Your account has been enabled by administration of the site', 'adifier' );
        }
        else{
            $inactive[$user_id] = $user_id;
        }       

        update_option( 'adifier_inactive_users', $inactive );

        ob_start();
        include( get_theme_file_path( 'includes/emails/account-status-change.php' ) );
        $message = ob_get_contents();
        ob_end_clean();

        adifier_send_mail( $user->user_email, esc_html__( 'Account Status Change', 'adifier' ), $message );        


        /*  since user status is changed to inactive update taxonomy terms count */
        $user_adverts = get_posts(array(
            'posts_per_page'    => -1,
            'author'            => $user_id,
            'post_status'       => 'publish',
            'post_type'         => 'advert'
        ));

        /* update category and locations count to exclude expired */
        $taxonomies = get_object_taxonomies( 'advert' );
        $terms = array();

        if( !empty( $user_adverts ) ){
            foreach( $user_adverts as $advert ){
                $terms = array_merge( $terms, wp_get_object_terms( $advert->ID, $taxonomies ) );
            }
        }

        adifier_update_taxonomy_count( $terms );         
    }

}
add_action( 'personal_options_update', 'adifier_save_user_meta' );
add_action( 'edit_user_profile_update', 'adifier_save_user_meta' );
}

if( !function_exists('adifier_add_extra_user_column') ){
function adifier_add_extra_user_column($columns) {
    unset( $columns['posts'] );
    return array_merge( $columns, array( 'custom_posts' => esc_html__( 'Ads Posted', 'adifier' ) ) );
}
add_filter('manage_users_columns' , 'adifier_add_extra_user_column');
}

if( !function_exists('adifier_extra_user_column_value') ){
function adifier_extra_user_column_value($custom_column,$column_name,$user_id) {
    if ( $column_name=='custom_posts' ) {
        return '<a href="'.admin_url() . "edit.php?post_type=advert&author=".$user_id.'">'.count_user_posts( $user_id, 'advert' ).'</a>';
    }
}
add_action('manage_users_custom_column','adifier_extra_user_column_value',10,3);
}


/*
Add filter for active/unactive users in wp-admin
*/
if( !function_exists( 'adifier_admin_add_users_filter' ) ){
function adifier_admin_add_users_filter( $views ) {
	global $wpdb;
	$inactive = $wpdb->get_var( "SELECT COUNT(umeta_id) FROM {$wpdb->usermeta} WHERE meta_key = 'user_active_status' AND meta_value = 'inactive'" );
	if( $inactive > 0 ){
		$views['inactive'] = '<a href="'.esc_url( admin_url('users.php?adifier_active_status=inactive') ).'">'.esc_html__( 'Inactive', 'adifier' ).' <span class="count">('.$inactive.')</span></a>';
    }   
    $active = $wpdb->get_var( "SELECT COUNT(umeta_id) FROM {$wpdb->usermeta} WHERE meta_key = 'user_active_status' AND meta_value = 'active'" );
	if( $active > 0 ){
		$views['active'] = '<a href="'.esc_url( admin_url('users.php?adifier_active_status=active') ).'">'.esc_html__( 'Active', 'adifier' ).' <span class="count">('.$active.')</span></a>';
    }
   
    return $views;
}
add_filter( 'views_users', 'adifier_admin_add_users_filter' );
}

/*
If we are on on admin listing opf coupons add filters
*/
if( !function_exists('adifier_admin_filter_users') ){
function adifier_admin_filter_users( $query ) {
    if( !empty( $_GET['adifier_active_status'] ) ){
        $query->set( 'meta_key', 'user_active_status' );
        $query->set( 'meta_value', sanitize_text_field( $_GET['adifier_active_status'] ) );
    }

    return $query;
}
add_filter('pre_get_users', 'adifier_admin_filter_users');
}
?>