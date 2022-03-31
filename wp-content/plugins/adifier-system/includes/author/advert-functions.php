<?php
/*
* HTML for image listing on advbert edit/add screen
*/
if( !function_exists('adifier_profile_advert_image') ){
function adifier_profile_advert_image( $image, $featured_image = 0 ){
	?>
	<div class="image-input-wrap">
		<a href="javascript:void(0);" class="remove-image" title="<?php esc_attr_e( 'Remove this image', 'adifier' ) ?>"><i class="aficon-times-circle"></i></a>
		<div class="styled-radio" title="<?php esc_attr_e( 'Set this image as featured', 'adifier' ) ?>">
			<input type="radio" name="featured_image" id="featured_image-<?php echo esc_attr( $image ) ?>" value="<?php echo esc_attr( $image ); ?>" <?php checked( $featured_image, $image ) ?>>
			<label for="featured_image-<?php echo esc_attr( $image ) ?>"></label>
		</div>
		<input type="hidden" id="images" name="images[]" value="<?php echo esc_attr( $image ); ?>" />
		<?php echo wp_get_attachment_image( $image, 'thumbnail' ) ?>
	</div>	
	<?php
}
}

/*
* Save uploaded images for advert and set featured one
*/
if( !function_exists('adifier_upload_images') ){
function adifier_upload_images(){
	if( !isset( $_REQUEST['adifier_nonce'] ) || !wp_verify_nonce( $_REQUEST['adifier_nonce'], 'adifier_nonce' ) ){
		die();
	}
	$advert_id = !empty( $_POST['advert_id'] ) ? sanitize_text_field( $_POST['advert_id'] ) : '';
	adifier_is_own_ad( $advert_id );
	if ( !empty( $_FILES ) ) {
		$featured_image = !empty( $_POST['featured_image'] ) ? sanitize_text_field( $_POST['featured_image'] ) : '';

		foreach( $_FILES as $file ){

			$checkfile = wp_check_filetype( $file['name'], array(
				'jpg|jpeg|jpe'	=> 'image/jpeg',
				'png'			=> 'image/png'
			));
			if( $checkfile['ext'] !== false ){
				$checkfile = @getimagesize( $file['tmp_name'] );
				if( $checkfile !== false ){
					$attach_id = adifier_handle_image_upload( $file, $advert_id );

					$featured_image = 0;
					if( ( !empty( $featured_image ) && $featured_image == $file['name'] ) || ( empty( $featured_image ) && empty( get_post_thumbnail_id( $advert_id ) ) ) ){
						set_post_thumbnail( $advert_id, $attach_id );
						$featured_image = $attach_id;
					}
					else{
						add_post_meta( $advert_id, 'advert_gallery', $attach_id );
					}
		
					adifier_profile_advert_image( $attach_id, $featured_image );
				}
			}
		}
	}
	die();
}
add_action( 'wp_ajax_upload_image', 'adifier_upload_images' );
}


/*
* Since we are uploading images in parallel there is no guaranteed order so we will resage advert_gallery at the ebd
*/
if( !function_exists('adifier_resage_gallery_upload') ){
function adifier_resage_gallery_upload(){
	if( !isset( $_REQUEST['adifier_nonce'] ) || !wp_verify_nonce( $_REQUEST['adifier_nonce'], 'adifier_nonce' ) ){
		die();
	}
	if( !empty( $_POST['advert_id'] ) && !empty( $_POST['images'] ) ){
		$advert_id = sanitize_text_field( $_POST['advert_id'] );
		$image_ids = map_deep( $_POST['images'], 'sanitize_text_field' );

		delete_post_meta( $advert_id, 'advert_gallery' );
		$featured = get_post_thumbnail_id( $advert_id );
		foreach( $image_ids as $image_id ){
			if( $image_id != $featured ){
				add_post_meta( $advert_id, 'advert_gallery', $image_id );
			}
		}
	}
}
add_action( 'wp_ajax_adifier_gallery_resave', 'adifier_resage_gallery_upload' );
}

/*
* Save new advert
*/
if( !function_exists('adifier_save_advert') ){
function adifier_save_advert(){
	if( !isset( $_REQUEST['adifier_nonce'] ) || !wp_verify_nonce( $_REQUEST['adifier_nonce'], 'adifier_nonce' ) ){
		die();
	}
	if( !is_user_logged_in() || !adifier_can_post_adverts() ){
		$response['message'] = '<div class="alert-error">'.esc_html__( 'You are not logged in or you do not have ads remaining for submit', 'adifier' ).'</div>';
		echo json_encode( $response );
		die();
	}

	$advert_id = !empty( $_POST['advert_id'] ) ? sanitize_text_field( $_POST['advert_id'] ) : '';

	if( !empty( $advert_id ) ){
		adifier_is_own_ad( $advert_id );
	}

	if( isset( $_POST['is_sold'] ) ){
		adifier_save_advert_meta( $advert_id, 'sold', 1 );
		$response['message'] = '<div class="alert-success">'.esc_html__( 'Ad is marked as sold and changes will not be applied', 'adifier' ).'</div>';
		$response['saved'] = true;

		echo json_encode( $response );
		die();
	}
	

	$submit_terms = adifier_get_option( 'submit_terms' );
	$use_google_location = adifier_get_option( 'use_google_location' );
	$use_predefined_locations = adifier_get_option( 'use_predefined_locations' );
	$approval_method = adifier_get_option( 'approval_method' );
	$mandatory_fields = adifier_get_option( 'mandatory_fields' );
	$bad_words = adifier_get_option( 'bad_words' );
	$thousands_separator = adifier_get_option( 'thousands_separator' );
	$decimal_separator = adifier_get_option( 'decimal_separator' );	
	$max_title_length = adifier_get_option( 'max_title_length' );	
	$redirect_on_submit = adifier_get_option( 'redirect_on_submit' );

	$response = array();

	/* let's filter words */
	if( !empty( $_POST ) ){	
		foreach( $_POST as $key => $item ){
			if( !is_array( $item ) && $key !== 'description' ){
				$_POST[$key] = wp_strip_all_tags( $item );
			}
		}
	}

	$bad_flag = false;
	if( !empty( $_POST ) && !empty( $bad_words ) ){
		$bad_words = explode( '|', $bad_words );
		foreach( $_POST as $key => $item ){
			if( !is_array( $item ) && $key != 'author_url' ){
				$words = explode( ' ', $item );
				foreach( $bad_words as $bad_word ){
					if( in_array( $bad_word, $words ) ){
						$bad_flag = true;
						break;
					}
				}
			}
		}
	}

	if( empty( $submit_terms ) || (!empty( $submit_terms ) && isset( $_POST['terms'] )) ){

		$advert_data = array(
			'advert_id'			=> $advert_id,
			'title'				=> sanitize_text_field( $_POST['title'] ),
			'parent_id'			=> isset( $_POST['advert_parent_id'] ) ? sanitize_text_field( $_POST['advert_parent_id'] ) : false,
			'description'		=> wp_kses_post( $_POST['description'] ?? '' ),
			'featured_image'	=> isset( $_POST['featured_image'] ) ? sanitize_text_field( $_POST['featured_image'] ) : '',
			'images'			=> isset( $_POST['images'] ) ? map_deep( $_POST['images'], 'sanitize_text_field' ) : array(),
			'videos'			=> isset( $_POST['videos'] ) ? map_deep( $_POST['videos'], 'sanitize_text_field' ) : array(),
			'is_sold'			=> isset( $_POST['is_sold'] ) ? 1 : 0,
			'is_negotiable'		=> isset( $_POST['is_negotiable'] ) ? 1 : 0,
			'type'				=> isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '',
			'cond'				=> isset( $_POST['cond'] ) ? sanitize_text_field( $_POST['cond'] ) : 0,
			'price'				=> isset( $_POST['price'] ) ? sanitize_text_field( $_POST['price'] ) : false,
			'sale_price'		=> isset( $_POST['sale_price'] ) ? sanitize_text_field( $_POST['sale_price'] ) : false,
			'start_price'		=> isset( $_POST['start_price'] ) ? sanitize_text_field( $_POST['start_price'] ) : false,
			'reserved_price'	=> isset( $_POST['reserved_price'] ) ? sanitize_text_field( $_POST['reserved_price'] ) : false,
			'currency'			=> isset( $_POST['currency'] ) ? sanitize_text_field( $_POST['currency'] ) : '',
			'user_address'		=> isset( $_POST['user_address'] ) ? true : false,
			'user_contact'		=> isset( $_POST['user_contact'] ) ? true : false,
			'phone'				=> isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '',
			'category'			=> isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : '',
			'location_id'		=> isset( $_POST['location_id'] ) ? sanitize_text_field( $_POST['location_id'] ) : '',
		);

		$check_type = $advert_data['type'];
		if( empty( $check_type ) ){
			$check_type = adifier_get_advert_meta( $advert_data['advert_id'], 'type', true );
		}

		if( $check_type == '6' ){
			$advert_data['is_negotiable'] = isset( $_POST['is_negotiable_rent'] ) ? 1 : 0;
			$advert_data['price'] = isset( $_POST['rent_price'] ) ? sanitize_text_field( $_POST['rent_price'] ) : false;
			$advert_data['rent_period'] = isset( $_POST['rent_period'] ) ? sanitize_text_field( $_POST['rent_period'] ) : false;
		}
		else if( $check_type == '3' ){
			$advert_data['is_negotiable'] = isset( $_POST['is_negotiable_buy'] ) ? 1 : 0;
			$advert_data['price'] = isset( $_POST['buy_price'] ) ? sanitize_text_field( $_POST['buy_price'] ) : false;			
		}
		else if( $check_type == '7' ){
			$advert_data['is_negotiable_salary'] = isset( $_POST['is_negotiable_salary'] ) ? 1 : 0;
			$advert_data['price'] = isset( $_POST['salary'] ) ? sanitize_text_field( $_POST['salary'] ) : false;
			$advert_data['start_price'] = isset( $_POST['max_salary'] ) ? sanitize_text_field( $_POST['max_salary'] ) : false;
		}

		if( $use_google_location == 'yes' ){
			$advert_data['location'] = array(
				'lat' 				=> sanitize_text_field( $_POST['lat'] ),
				'long'				=> sanitize_text_field( $_POST['long'] ),
				'country'			=> sanitize_text_field( $_POST['country'] ),
				'state'				=> sanitize_text_field( $_POST['state'] ),
				'city'				=> sanitize_text_field( $_POST['city'] ),
				'street'			=> sanitize_text_field( $_POST['street'] )
			);
		}


		$new_advert = false;
		$needs_approval = false;
		$is_update = false;
		$user_profile_has_location = false;
		$user_profile_has_phone = false;		
		extract( $advert_data );

		/* first let's see if the user profile account has location and phone and aslo use that data if it exists */
		if( $use_google_location == 'yes' ){
			$user_location = get_user_meta( get_current_user_id(), 'location', true );
			if( !empty( $user_location['lat'] ) ){
				$user_profile_has_location = true;
			}
		}
		if( $use_predefined_locations == 'yes' ){
			$user_location_ids = get_user_meta( get_current_user_id(), 'af_location_id', true );
			if( !empty( $user_location_ids ) ){
				$user_profile_has_location = true;
			}
		}

		$user_phone = get_user_meta( get_current_user_id(), 'phone', true );
		if( !empty( $user_phone ) ){
			$user_profile_has_phone = true;
		}		

		if( $user_address ){
			if( $use_google_location == 'yes' ){
				if( !empty( $user_location['lat'] ) ){
					$location['lat'] = $user_location['lat'];
					$location['long'] = $user_location['long'];
				}
			}
			if( $use_predefined_locations == 'yes' ){
				$location_ids = $user_location_ids;
			}
		}
		else{
			$location_ids = get_ancestors( absint( $location_id ), 'advert-location' );
			if( !empty( $location_id ) ){
				$location_ids[] = absint( $location_id );	
			}
		}

		if( $user_contact || adifier_get_option( 'enable_phone_verification' ) == 'yes' ){
			$phone = $user_phone;
		}	

		$errors = array();

		if( $bad_flag === true ){
			$errors[] = esc_html__( 'Some of your fields contain bad word(s)', 'adifier' );
		}
		if( empty( $title ) ){
			$errors[] = esc_html__( 'Title is empty', 'adifier' );
		}
		if( !empty( $max_title_length ) && strlen( $title ) > $max_title_length ){
			$errors[] = sprintf( esc_html__( 'Maximum length of title can be %s chars', 'adifier' ), $max_title_length );
		}
		if( empty( $description ) ){
			$errors[] = esc_html__( 'Description is empty', 'adifier' );
		}
		if( $use_google_location == 'yes' && ( empty( $location['lat'] ) || empty( $location['long'] ) ) ){
			$errors[] = esc_html__( 'Location is not set', 'adifier' );
		}
		if( $mandatory_fields['phone'] == 1 && empty( $phone ) ){
			$errors[] = esc_html__( 'Phone is empty', 'adifier' );
		}
		if( empty( $category ) ){
			$errors[] = esc_html__( 'Category is not selected', 'adifier' );
		}
		if( $use_predefined_locations == 'yes' && empty( $location_ids ) ){
			$errors[] = esc_html__( 'Location from dropdown is not selected', 'adifier' );
		}
		if( $type == '2' && empty( $start_price ) ){
			$errors[] = esc_html__( 'Start price is required', 'adifier' );	
		}
		if( !empty( $start_price ) && !adifier_validate_price_format( $start_price, $currency ) ){
			$errors[] = esc_html__( 'Start price format is invalid', 'adifier' ).adifier_acceptable_price_formats( $currency );
		}
		if( !empty( $price ) && !adifier_validate_price_format( $price, $currency ) ){
			$errors[] = esc_html__( 'Price format is invalid', 'adifier' ).adifier_acceptable_price_formats( $currency );
		}
		if( !empty( $sale_price ) && !adifier_validate_price_format( $sale_price, $currency ) ){
			$errors[] = esc_html__( 'Sale price format is invalid', 'adifier' ).adifier_acceptable_price_formats( $currency );
		}
		if( !empty( $sale_price ) && adifier_clean_price( $sale_price, $currency ) >= adifier_clean_price( $price, $currency ) ){
			$errors[] = esc_html__( 'Sale price can not be greather or equal to regular price', 'adifier' );
		}
		if( !empty( $videos ) ){
			foreach( $videos as &$vid ){
				if( !empty( $vid ) ){
					if( stristr( $vid, 'youtube' ) ){
						if( !preg_match( "/^(?:https?:\/\/)?(?:www\.)?(youtube|youtube-nocookie)\.(com)\/watch\?(?=.*v=((\w|-){11}))(?:\S+)?$/", $vid ) ){
							$errors[] = esc_html__( 'Full YouTube link format must be https://www.youtube.com/watch?v=XXXXXXXXXXX', 'adifier' );
						}
						else if( !empty( $gdpr_text ) ){
							$vid = str_replace( 'youtube.', 'youtube-nocookie.', $vid );
						}
					}
					else if( stristr( $vid, 'youtu.be' ) ){
						if( !preg_match( "/(?:youtube\.com\/\S*(?:(?:\/e(?:mbed))?\/|watch\?(?:\S*?&?v\=))|youtu\.be\/)([a-zA-Z0-9_-]{6,11})/", $vid ) ){
							$errors[] = esc_html__( 'Short YouTube link format must be https://www.youtu.be/XXXXXXXXXXX', 'adifier' );
						}
						else{
							$code = explode( '/', $vid );
							$code = $code[sizeof($code) - 1];
							$vid = 'https://www.youtube.com/watch?v='.$code;
						}
					}					
					else if( stristr( $vid, 'vimeo' ) ){
						if( !preg_match( "/https:\/\/vimeo.com\/[0-9]*$/", $vid ) ){
							$errors[] = esc_html__( 'Vimeo video format must be https://vimeo.com/XXXXXXXX', 'adifier' );
						}
					}
					else{
						$errors[] = esc_html__( 'Supported video links are from vimeo and youtube only', 'adifier' );
					}
				}
			}
		}
		if( !empty( $category ) ){
			$custom_fields = Adifier_Custom_Fields_Search::get_fields_by_category_id( $category, true );
			if( !empty( $custom_fields ) ){
				foreach( $custom_fields as $custom_field ){
					$flag = false;
					if( $custom_field->cf_is_mandatory == 1 ){
						if( !empty( $_POST['cf_fields']['type_'.$custom_field->cf_type][$custom_field->cf_slug] ) ){
							$field = $_POST['cf_fields']['type_'.$custom_field->cf_type][$custom_field->cf_slug];
							if( $custom_field->cf_type == 5 ){
								$labels = explode( '|', $custom_field->cf_label );
								for( $i=0; $i<sizeof( $labels ); $i++ ){
									if( empty( $field['value']['depth_'.$i] ) ){
										$flag = true;
									}
									if( $flag ){
										$flag = false;
										if( empty( $field['new']['depth_'.$i] ) ){
											$flag = true;
										}
									}
									if( $flag ){
										$errors[] = $labels[$i].' '.esc_html__( 'is required', 'adifier' );
									}
								}
							}
							else{
								if( empty( $field['value'] ) || ( is_array( $field['value'] ) && empty( $field['value'][0] ) ) ){
									$flag = true;
								}
								if( $flag ){
									$flag = false;
									if( empty( $field['new'] ) || ( is_array( $field['new'] ) && empty( $field['new'][0] ) ) ){
										$flag = true;
									}
								}					
							}
						}
						else{
							$flag = true;
						}							
					}

					if( $flag === true ){
						$errors[] = $custom_field->cf_label.' '.esc_html__( 'is required', 'adifier' );
					}					

				}
			}
		}

		if( !empty( $errors ) ){
			$response['message'] = '<div class="alert-error">'.implode('<br/>', $errors).'</div>';
		}
		else{
			/* if user profile does not have location and phone add the ones from it's first ad */
			if( $user_profile_has_location == false ){
				if( $use_google_location == 'yes' ){
					update_user_meta( get_current_user_id(), 'location', $location );
				}
				if( $use_predefined_locations == 'yes' ){
					update_user_meta( get_current_user_id(), 'af_location_id', $location_ids );
				}
			}
			if( $user_profile_has_phone == false ){
				update_user_meta( get_current_user_id(), 'phone', $phone );
			}

			/* if advert exists then update it */
			if( !empty( $advert_id ) ){
				$args = array(
					'ID'			=> $advert_id,
					'post_title'	=> $title,
					'post_content'	=> $description,
				);
				if( !empty( $_POST['renew_advert'] ) ){
					$new_advert = true;
					$time = current_time('mysql');
					$args['post_date'] = $time;
					$args['post_date_gmt'] = get_gmt_from_date( $time );
				}
				/* if we need manually to approve advert then create child. If the update is during previous update update child */
				if( $parent_id !== false ){
					$args['post_type'] = 'advert';
					$args['post_author'] = get_current_user_id();
					$args['post_status'] = 'draft';
					if( $parent_id == '0' && get_post_status( $advert_id ) == 'publish' ){
						$args['post_parent'] = $advert_id;
						unset( $args['ID'] );

						$advert_id = wp_insert_post( $args );

						adifier_save_advert_meta( $advert_id, 'type', adifier_get_advert_meta( $args['post_parent'], 'type', true ) );
						if( !empty( $_POST['renew_advert'] ) ){
							update_post_meta( $advert_id, 'af_needs_renew', '1' ) ;
						}
						$needs_approval = true;
						$is_update = true;

						$email_title = esc_html__( 'New Ad Update Was Submitted', 'adifier' );
						$email_message = esc_html__( 'There is a new ad update waiting for your review ', 'adifier' ).'<b>'.$title.'</b>';

					}
					else{
						$args['post_parent'] = $parent_id;
						wp_update_post( $args );
						$needs_approval = true;
					}
				}
				else{
					wp_update_post( $args );
				}
			}
			/* if advert does not exists then create it it */
			else{

				$needs_approval = $approval_method == 'auto' ? false : true;
				$advert_id = wp_insert_post(array(
					'post_type'		=> 'advert',
					'post_status'	=> $needs_approval ? 'draft' : 'publish',
					'post_title'	=> $title,
					'post_content'	=> $description,
					'post_author'	=> get_current_user_id()
				));

				adifier_save_advert_meta( $advert_id, 'type', $type );
				
				if( $type == '2' ){
					$price = $start_price;
					$sale_price = '';
				}
				else if( $type != '7' ){
					$start_price = '';
				}

				$new_advert = true;
				if( $needs_approval ){
					$email_title = esc_html__( 'New Ad Was Submitted', 'adifier' );
					$email_message = esc_html__( 'There is a new ad waiting for your review ', 'adifier' ).'<b>'.$title.'</b>';
				}

			}

			if( $new_advert && !$needs_approval ){
				adifier_save_advert_meta( $advert_id, 'expire', '' );
			}

			/* assign advert to categories */
			$category_ids = get_ancestors( absint( $category ), 'advert-category' );
			$category_ids[] = absint( $category );	
			wp_set_post_terms( $advert_id, $category_ids, 'advert-category' );

			/* assign advert to locations */
			if( $use_predefined_locations == 'yes' ){
				wp_set_post_terms( $advert_id, $location_ids, 'advert-location' );
			}

			/* save videos */
			delete_post_meta( $advert_id, 'advert_videos' );
			if( !empty( $videos[0] ) ){
				foreach( $videos as $video ){
					add_post_meta( $advert_id, 'advert_videos', $video );
				}
			}

			/* save adderss for the advert */
			if( $use_google_location == 'yes' ){
				delete_post_meta( $advert_id, 'advert_location' );
				if( empty( $user_address )  ){
					update_post_meta( $advert_id, 'advert_location', $location );
				}
				adifier_save_advert_meta( $advert_id, 'latitude', $location['lat'] );
				adifier_save_advert_meta( $advert_id, 'longitude', $location['long'] );	
			}

			/* if we have set featured assign it */

			/* save images */
			$advert_gallery = get_post_meta( $advert_id, 'advert_gallery' );
			$saved_featured = get_post_thumbnail_id( $advert_id );
			delete_post_meta( $advert_id, 'advert_gallery' );
			if( !empty( $saved_featured ) ){
				$advert_gallery[] = $saved_featured;	
			}

			if( $needs_approval && !empty( $parent_id ) ){
				$parent_gallery = get_post_meta( $parent_id, 'advert_gallery' );
				$parent_features = get_post_thumbnail_id( $parent_id );
				if( !empty( $saved_featured ) ){
					$parent_gallery[] = $parent_features;	
				}

				$advert_gallery = array_diff( $advert_gallery, $parent_gallery );
			}


			$delete_images = array_diff( $advert_gallery, $images );
			if( !empty( $delete_images ) ){
				foreach( $delete_images as $image_id ){
					wp_delete_attachment( $image_id, true );
				}
			}

			if( !empty( $images[0] ) ){

				if( empty( $featured_image ) ){
					$featured_image = $images[0];
				}

				$images = array_diff( $images, array( $featured_image ) );
				foreach( $images as $image_id ){
					add_post_meta( $advert_id, 'advert_gallery', $image_id );
				}
			}

			if( is_numeric( $featured_image ) ){
				set_post_thumbnail( $advert_id, $featured_image );
			}
			else{
				delete_post_thumbnail( $advert_id );
			}			

			/* save price status  meta */
			if( $price || $check_type != '2' ){
				adifier_save_advert_meta( $advert_id, 'price', adifier_mysql_format_price( $price, $currency ) );
				adifier_save_advert_meta( $advert_id, 'sale_price', adifier_mysql_format_price( $sale_price, $currency ) );
			}
			else if( $needs_approval && $parent_id == '0' && ( !empty( $args) && get_post_status( $args['post_parent'] ) == 'publish' ) ){
				adifier_save_advert_meta( $advert_id, 'price', adifier_get_advert_meta( $args['post_parent'], 'price', true ) );
				adifier_save_advert_meta( $advert_id, 'sale_price', adifier_get_advert_meta( $args['post_parent'], 'sale_price', true ) );
			}
			if( $start_price ){
				adifier_save_advert_meta( $advert_id, 'start_price', adifier_mysql_format_price( $start_price, $currency ) );
			}
			else if( $needs_approval && $parent_id == '0' && ( !empty( $args) && get_post_status( $args['post_parent'] ) == 'publish' ) ){
				adifier_save_advert_meta( $advert_id, 'start_price', adifier_get_advert_meta( $args['post_parent'], 'start_price', true ) );
			}
			if( $reserved_price ){
				update_post_meta( $advert_id, 'advert_reserved_price', adifier_mysql_format_price( $reserved_price, $currency ) );
			}
			else if( $needs_approval && $parent_id == '0' && ( !empty( $args) && get_post_status( $args['post_parent'] ) == 'publish' ) ){
				update_post_meta( $advert_id, 'advert_reserved_price', get_post_meta( $args['post_parent'], 'advert_reserved_price', true ) );
			}
			adifier_save_advert_meta( $advert_id, 'sold', $is_sold );
			update_post_meta( $advert_id, 'advert_negotiable', $is_negotiable );

			/* save currency */
			if( !empty( $currency ) ){
				adifier_save_advert_meta( $advert_id, 'currency', $currency );
			}
			else if( $needs_approval && $parent_id == '0' && ( !empty( $args ) && get_post_status( $args['post_parent'] ) == 'publish' ) ){
				adifier_save_advert_meta( $advert_id, 'currency', adifier_get_advert_meta( $args['post_parent'], 'currency', true ) );
			}

			/* if it is rent save rent period */
			if( !empty( $rent_period ) ){
				update_post_meta( $advert_id, 'advert_rent_period', $rent_period );
			}

			/* save condition */
			adifier_save_advert_meta( $advert_id, 'cond', $cond );

			/* save contact phone if overwrite exists */
			if( $user_contact || $user_profile_has_phone == false ){
				delete_post_meta( $advert_id, 'advert_phone' );
			}
			else{
				update_post_meta( $advert_id, 'advert_phone', $phone );
			}

			$response['advert_id'] = $advert_id;
			if( $new_advert ){
				$account_payment = adifier_get_option( 'account_payment' );
				adifier_save_advert_meta( $advert_id, 'exp_info', 0 );
				if( in_array( $account_payment, array( 'packages', 'hybrids' ) ) ){
					$adverts = get_user_meta( get_current_user_id(), 'af_adverts', true );
					update_user_meta( get_current_user_id(), 'af_adverts', $adverts - 1 );
				}
			}

			if( $needs_approval ){
				if( $is_update ){
					update_post_meta( $args['post_parent'], 'adifier_has_update', 1 );	
					update_post_meta( $advert_id, 'adifier_is_update', 1 );	
				}
				update_post_meta( $advert_id, 'adifier_manual_approve', 1 );
				if( !empty( $email_title ) ){
					$email_link = add_query_arg( array( 'post' => $advert_id, 'action' => 'edit' ), admin_url( 'post.php' ) );
					ob_start();
					include( get_theme_file_path( 'includes/emails/new-advert-admin.php' ) );
					$message = ob_get_contents();
					ob_end_clean();
					adifier_send_mail( get_option( 'admin_email' ), esc_html__( 'Action Required', 'adifier' ), $message );
				}
				$response['message'] = '<div class="alert-success">';
				if( $redirect_on_submit == 'yes' ){
					$response['message'] .= esc_html__( 'Ad is submitted for the review. Redirecting to your ads...', 'adifier' );
					$response['redirect'] = add_query_arg( 'screen', 'ads', get_author_posts_url( get_current_user_id() ) );
				}
				else{
					$response['message'] .= esc_html__( 'Ad is submitted for the review. You can add another one by clicking on Submit Ad.', 'adifier' );
				}
				$response['message'] .= '</div>';

				$response['parent_id'] = $is_update ? $args['post_parent'] : '';
			}
			else{
				$response['message'] = '<div class="alert-success">';
				if( $redirect_on_submit == 'yes' ){
					$response['message'] .=  esc_html__( 'Ad is saved. Redirecting to your ads...', 'adifier' );
					$response['redirect'] = add_query_arg( 'screen', 'ads', get_author_posts_url( get_current_user_id() ) );
				}
				else{
					$response['message'] .=  esc_html__( 'Ad is saved. You can add another one by clicking on Submit Ad.', 'adifier' );
				}
				$response['message'] .= '</div>';
			}

			$response['saved'] = true;
		}

	}
	else{
		$response['message'] = '<div class="alert-error">'.esc_html__( 'You must agree on terms & conditions.', 'adifier' ).'</div>';
	}

	echo json_encode( $response );

	die();
}
add_action( 'wp_ajax_adifier_save_advert', 'adifier_save_advert', 10 );
}

/*
* When user wants to delete advert allow him to delete every type except for auction which will be set as draft
*/
if( !function_exists('adifier_delete_advert') ){
function adifier_delete_advert(){
	if( !isset( $_REQUEST['adifier_nonce'] ) || !wp_verify_nonce( $_REQUEST['adifier_nonce'], 'adifier_nonce' ) ){
		die();
	}
	if( !is_user_logged_in() ){
		return;
	}
	$advert_id = sanitize_text_field( $_POST['advert_id'] );
	
	adifier_is_own_ad( $advert_id );

	if( !empty( $advert_id ) ){
		if( get_post_field( 'post_author', $advert_id ) == get_current_user_id() ){

			$children = get_posts(array(
				'post_parent' 		=> $advert_id,
				'posts_per_page'	=> '-1',
				'post_type'			=> 'advert',
				'post_status'		=> 'draft,publish'
			));
			if( !empty( $children ) ){
				foreach( $children as $child ){
					wp_delete_post( $child->ID, true );
				}
			}

			if( adifier_get_advert_meta( $advert_id, 'type', true ) !== '2' ){
				$result = wp_delete_post( $advert_id, true );
				if( $result ){
					$result = true;
				}
				else{
					$result = esc_html__( 'Failed to delete ad, try again', 'adifier' );
				}
			}
			else{
				$result = wp_update_post(array(
					'ID' 			=> $advert_id,
					'post_status'	=> 'private'
				));

				if( !empty( $result ) ){
					$result = true;
				}
				else{
					$result = esc_html__( 'Failed to delete ad, try again', 'adifier' );
				}
			}
		}
	}
	else{
		$result = esc_html__( 'No ad with provided ID', 'adifier' );
	}
	echo json_encode(array(
		'result' => $result
	));	

	die();
}  
add_action( 'wp_ajax_adifier_delete_advert', 'adifier_delete_advert' );
}

/*
* On advert delete delete its record in adifier_meta_data table
*/
if( !function_exists('adifier_before_delete_post') ){
function adifier_before_delete_post( $post_id ){
	global $wpdb; 
	$post = get_post( $post_id );
	if ( $post->post_type != 'advert' ){
		return;
	}
	
	adifier_remove_advert_galery_images( $post_id );

	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}adifier_bids WHERE post_id = %d", $post_id ) );
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}adifier_advert_data WHERE post_id = %d", $post_id ) );
}
add_action( 'before_delete_post', 'adifier_before_delete_post', 11 );
}

/*
* Delete images associated to advert on advert deletion
*/
if( !function_exists('adifier_remove_advert_galery_images') ){
function adifier_remove_advert_galery_images( $advert_id ){
	$gallery_images = get_post_meta( $advert_id, 'advert_gallery' );
	if( !empty( $gallery_images ) ){
		foreach( $gallery_images as $image_id ){
			wp_delete_attachment( $image_id, true );
		}
	}

	$post_thumbnail_id = get_post_thumbnail_id( $advert_id );
	if( !empty( $post_thumbnail_id ) ){
		wp_delete_attachment( $post_thumbnail_id, true );
	}
}
}

/*
* Check if user has credits/subcribe time to post adverts
*/
if( !function_exists('adifier_can_post_adverts') ){
function adifier_can_post_adverts(){
	$can_submit = true;
	$account_payment = adifier_get_option( 'account_payment' );
	if( $account_payment == 'packages' ){
		$adverts = Adifier_Order::get_user_package_adverts( get_current_user_id() );
		if( $adverts <= 0 ){
			$can_submit = false;
		}
	}
	else if( $account_payment == 'subscriptions' ){
		$subscription = Adifier_Order::get_user_package_subscription( get_current_user_id() );
		if( $subscription < current_time( 'timestamp' ) ){
			$can_submit = false;
		}
	}
	else if( $account_payment == 'hybrids' ){
		$adverts = Adifier_Order::get_user_package_adverts( get_current_user_id() );
		$subscription = Adifier_Order::get_user_package_subscription( get_current_user_id() );
		if( $subscription >= current_time( 'timestamp' ) ){
			if( $adverts <= 0 ){
				$can_submit = false;
			}
		}
		else{
			$can_submit = false;
		}
	}

	if( !empty( $_GET['screen'] ) && $_GET['screen'] == 'edit'){
		if( adifier_is_expired( sanitize_text_field( $_GET['id'] ) ) && $can_submit == false ){
			$can_submit = false;	
		}
		else{
			$can_submit = true;
		}
	}

	// if( empty(  ) && !empty( $_POST['advert_id'] ) ){
	// 	$can_submit = true;
	// }

	return $can_submit;

}
}

/*
* Check if user have populated mandatory fields in profile before submitting
*/
if( !function_exists('adifier_mandatory_profile_fields_populated') ){
function adifier_mandatory_profile_fields_populated(){
	$mandatory_fields = adifier_get_option( 'mandatory_fields' );
	foreach( $mandatory_fields as $field => $value ){
		if( $value == 1 && empty( get_user_meta( get_current_user_id(), $field, true ) ) ){
			return false;
		}
	}
	return true;
}
}

/*
* Put active and available promotions for each advert in data attribute which is then used by script on opening promotions modal
*/
if( !function_exists('adifier_get_advert_attr_data') ){
function adifier_get_advert_attr_data(){
	$promotions = adifier_available_promotions();
	$attr_list = array();
	foreach( $promotions as $promotion ){
		$promo = str_replace( 'promo_', '', $promotion['id'] );
		if( !empty( $promotion['value_handler'] ) ){
			$attr_value = call_user_func( $promotion['value_handler'] );
		}
		else{
			if( $promotion['value_holder'] == 'extra_value' ){
				$value = adifier_get_advert_meta( get_the_ID(), $promo, true );
			}
			else{
				$value = get_post_meta( get_the_ID(), $promotion['id'], true );	
			}

			$attr_value = $value >= current_time( 'timestamp' ) ? sprintf( esc_html__( '(Active until: %s)', 'adifier' ), date_i18n( get_option( 'date_format' ), $value ) ) : '';
		}

		$attr_list[] = 'data-'.$promo.'="'.( !empty( $attr_value ) ? $attr_value : Adifier_Order::check_onhold( $promotion['id']  ) ).'"';
	}

	return join( ' ', $attr_list );
}
}

/*
* Get registered and active promotions and create array of data for each one which is then used on promotion listing modal
*/
if( !function_exists('adifier_get_promotion') ){
function adifier_get_promotion( $promo ){
	$promotion = adifier_get_option( $promo );
	$promotions = adifier_available_promotions();
	$tax = adifier_get_option( 'tax' );
	$args = array();
	if( !empty( $promotion ) ){
		$packs = explode( '+', $promotion );	
		$args = array(
			'name'			=> $promotions[$promo]['title'],
			'desc'			=> $promotions[$promo]['desc'],
			'front_desc'	=> $promotions[$promo]['front_desc']
		);		
		foreach( $packs as $pack ){
			$temp = explode( '|', $pack );	
			$days = sizeof( $temp ) > 1 ? $temp[0] : '';
			$price = sizeof( $temp ) > 1 ? $temp[1] : $temp[0];
			$args['packs'][] = array(
				'days'		=> $days,
				'price'		=> !empty( $tax ) ? $price*( 1 + $tax/100 ) : $price,
				'desc'		=> !empty( $days ) ? esc_html__( 'For', 'adifier').' '.sprintf( _n( '<b>%d</b> day', '<b>%d</b> days', $days, 'adifier' ), $days ) : ''
			);
		}
	}

	return $args;
}
}

/*
* Display promotions in advert prommotion modal
*/
if( !function_exists('adifier_display_promotion') ){
function adifier_display_promotion( $promo ){
	$promotion = adifier_get_promotion( $promo );
	if( !empty( $promotion ) ){
		?>
		<div class="promotion <?php echo esc_attr( $promo ) ?> inactive">
			<div class="styled-checkbox">
				<input type="checkbox" name="promotion" value="<?php echo esc_attr( $promo ) ?>" id="<?php echo esc_attr( $promo ) ?>">
				<label for="<?php echo esc_attr( $promo ) ?>" class="promotion-styled-label">
					<div class="flex-wrap flex-center">
						<h5><?php echo esc_html( $promotion['name'] ) ?></h5>
						<span class="active-promo"></span>
					</div>
				</label>
				<a href="javascript:void(0);" class="promotion-description-toggle" data-target="<?php echo esc_attr( $promo ) ?>">
					<i class="aficon-info-circle"></i>
				</a>
			</div>
			<div class="promotion-description pr-<?php echo esc_attr( $promo ) ?>"><?php echo esc_attr( $promotion['front_desc'] ) ?></div>
			<div class="price-wrap clearfix">
				<?php 
				foreach( $promotion['packs'] as $key => $pack ){
					if( (float)$pack['price'] > 0 ){
						?>
						<div class="promo-price-item">
							<div>
								<?php echo !empty( $pack['desc'] ) ? '<span class="promo-valid-for">'.$pack['desc'].'</span>' : '<span class="promo-valid-for">'.esc_html__( 'One time', 'adifier' ).'</span>'; ?>
								<div class="styled-radio promo-radio">
									<input type="radio" name="<?php echo esc_attr( $promo ) ?>_pack" value="<?php echo esc_attr( $key ) ?>" id="<?php echo esc_attr( $promo.'_'.$key ) ?>">
									<label for="<?php echo esc_attr( $promo.'_'.$key ) ?>">
										<div>
											<div class="price">
												<?php echo adifier_price_format( $pack['price'] ) ?>
											</div>
											<?php adifier_tax_included() ?>
										</div>
									</label>
								</div>
							</div>
						</div>
						<?php
					}
				}
				?>
			</div>
		</div>
		<?php
	}
}
}


/*
* Callback function of CMB meta which is called on all promotions when admin creates/updates advert
* Here we can apply custom action for certain promotion checks
*/
if( !function_exists('adifier_get_promo_admin_status') ){
function adifier_get_promo_admin_status( $post_id, $meta_key, $single = false ){
	if( $meta_key == 'promo_bumpup' ){
		return adifier_check_bumpup( $post_id ) === false ? array( 'no' ) : array( 'yes' );
	}
	else if ( $meta_key == 'promo_topad' ){
		$topads_time = adifier_get_topad_advert_time( $post_id );
		return array( $topads_time );
	}
	else if ( $meta_key == 'promo_homemap' ){
		$homemap_time = adifier_get_homemap_ad_advert_time( $post_id );
		return array( $homemap_time );
	}
}
}

/*
* Callback function of CMB meta which is called on all promotions when admin creates/updates advert
* Here we can apply custom action for certain promotion updates like removing it from advert or applying it
*/
if( !function_exists('adifier_save_promo_admin_status') ){
function adifier_save_promo_admin_status( $post_id, $meta_key, $meta_value ){
	$meta_value = array_shift( $meta_value );
	if( $meta_key == 'promo_bumpup' ){
		$meta_value == 'yes' ? adifier_update_advert_bumpup( $post_id, 'apply' ) : adifier_update_advert_bumpup( $post_id, 'remove' );
	}
	else if ( $meta_key == 'promo_topad' ){
		!empty( $meta_value ) ? adifier_update_advert_topad( $post_id, $meta_value, 'apply' ) : adifier_update_advert_topad( $post_id, '', 'remove' );
	}
	else if ( $meta_key == 'promo_homemap' ){
		!empty( $meta_value ) ? adifier_update_advert_homemap( $post_id, $meta_value, 'apply' ) : adifier_update_advert_homemap( $post_id, '', 'remove' );
	}
}
}

/*
* Wrapper function for handlign bump up promotion called from Order class
*/
if( !function_exists('adifier_bumpup_advert') ){
function adifier_bumpup_advert( $order, $product, $action ){
	adifier_update_advert_bumpup( $order['advert_id'], $action );
}
}

/*
* Handling bump up promotion
* This is called from wrapper class above and on admin advert manipulation
*/
if( !function_exists('adifier_update_advert_bumpup') ){
function adifier_update_advert_bumpup( $advert_id, $action ){
	global $wpdb;
	if( $action == 'apply' ){
		$post_date = get_post_time( 'Y-m-d H:i:s', false, $advert_id ); 
		$post_date_gmt = get_post_time( 'Y-m-d H:i:s', true, $advert_id ); 
		update_post_meta( $advert_id, 'bumpup_dates', $post_date.'|'.$post_date_gmt );
		$current_time = current_time( 'Y-m-d H:i:s' );
		$wpdb->update(
			$wpdb->posts,
			array(
				'post_date'		=> $current_time,
				'post_date_gmt'	=> get_gmt_from_date( $current_time )
			),
			array(
				'ID'	=> $advert_id
			)
		);
	}
	else{
		$times = get_post_meta( $advert_id, 'bumpup_dates', true );
		if( !empty( $times ) ){
			$times = explode( '|', $times );
			$wpdb->update(
				$wpdb->posts,
				array(
					'post_date'		=> $times[0],
					'post_date_gmt'	=> $times[1]
				),
				array(
					'ID'	=> $advert_id
				)
			);
			delete_post_meta( $advert_id, 'bumpup_dates' );
		}
	}
}
}

/*
* Check if bumpup is set for the given advert which is called by the Order and admin advert manipulation
*/
if( !function_exists('adifier_check_bumpup') ){
function adifier_check_bumpup( $post_id = 0 ){
	$post_id = empty( $posts_id ) ? get_the_ID() : $post_id;
	$bumpup_dates = get_post_meta( $post_id, 'bumpup_dates', true );
	if( !empty( $bumpup_dates ) ){
		return esc_html__( '(Promotion has been used for this ad)', 'adifier' );
	}
	else{
		return false;
	}
}
}

/*
* Wrapper function for handlign top ad promotion called from Order class
*/
if( !function_exists('adifier_topad_advert') ){
function adifier_topad_advert( $order, $product, $action ){
	$time = current_time('timestamp') + $product['days'] * 86400;
	adifier_update_advert_topad( $order['advert_id'], $time, $action );
}
}

/*
* Handling top ad promotion
* This is called from wrapper class above and on admin advert manipulation
*/
if( !function_exists('adifier_update_advert_topad') ){
function adifier_update_advert_topad( $advert_id, $time, $action ){
	$top_ads = adifier_get_top_ads_list();
	$term_ids = wp_get_post_terms( $advert_id, 'advert-category', array( 'fields' => 'ids' ) );
	if( !empty( $term_ids ) ){
		foreach( $term_ids as $term_id ){
			if( $action == 'apply' ){
				if( empty( $top_ads[$term_id][$advert_id] ) ){
					$top_ads[$term_id][$advert_id] = $time;		
				}
			}
			else{
				unset( $top_ads[$term_id][$advert_id] );
			}
		}
		update_option( 'adifier_top_ads', $top_ads );
	}
}
}

/*
* Get advert target time by advert id from the selected group of top ads ( group is determined by term_id - category )
*/
if( !function_exists('adifier_get_topad_advert_time') ) {
function adifier_get_topad_advert_time( $advert_id, $advert_group = array() ){
	$advert_group = empty( $advert_group  ) ? adifier_get_topad_advert_group( $advert_id ) : $advert_group;
	return !empty( $advert_group[$advert_id] ) ? $advert_group[$advert_id] : '';
}
}

/*
* Get group of adverts in which advert id is located ( gruop is located by term_id - category )
*/
if( !function_exists('adifier_get_topad_advert_group') ) {
function adifier_get_topad_advert_group( $advert_id, $top_ads = array() ){
	$top_ads = empty( $top_ads ) ? adifier_get_top_ads_list() : $top_ads;
	$term_ids = wp_get_post_terms( $advert_id, 'advert-category', array( 'fields' => 'ids' ) );
	if( !empty( $term_ids ) ){
		foreach( $term_ids as $term_id ){
			if( !empty( $top_ads[$term_id] ) ){
				return $top_ads[$term_id];
			}
		}
	}

	return array();
}
}

/*
* Check if top ad is set for the given advert which is called by the Order and admin advert manipulation
*/
if( !function_exists('adifier_check_topad') ){
function adifier_check_topad(){
	$max_top_ads = adifier_get_option('max_top_ads');
	$top_ads = adifier_get_top_ads_list();
	$advert_group = adifier_get_topad_advert_group( get_the_ID(), $top_ads );
	$advert_time = adifier_get_topad_advert_time( get_the_ID(), $advert_group );
	if( !empty( $advert_time ) ){
		return $advert_time >= current_time( 'timestamp' ) ? sprintf( esc_html__( '(Active until: %s)', 'adifier' ), date_i18n( get_option( 'date_format' ), $advert_time ) ) : '';
	}
	else if( !empty( $max_top_ads ) && sizeof( $advert_group ) >= $max_top_ads ){
		return sprintf( esc_html__( '(First available spot is on: %s)', 'adifier' ), date_i18n( get_option( 'date_format' ),  min( $advert_group ) ) );
	}
	else{
		return false;
	}
}
}


/*
* Wrapper function for handlign home page ads promotion called from Order class
*/
if( !function_exists('adifier_homemap_advert') ){
function adifier_homemap_advert( $order, $product, $action ){
	$time = current_time('timestamp') + $product['days'] * 86400;
	adifier_update_advert_homemap( $order['advert_id'], $time, $action );
}
}

/*
* Handling home map promotion
* This is called from wrapper class above and on admin advert manipulation
*/
if( !function_exists('adifier_update_advert_homemap') ){
function adifier_update_advert_homemap( $advert_id, $time, $action ){
	$homemap_ads = adifier_get_homemap_ads_list();
	if( $action == 'apply' ){
		if( empty( $homemap_ads[$advert_id] ) ){
			$homemap_ads[$advert_id] = $time;		
		}
	}
	else{
		unset( $homemap_ads[$advert_id] );
	}
	update_option( 'adifier_homemap_ads', $homemap_ads );
}
}

/*
* Get time for home map promotion y advert_id
*/
if( !function_exists('adifier_get_homemap_ad_advert_time') ){
function adifier_get_homemap_ad_advert_time( $advert_id, $homemap_ads = array() ){
	$homemap_ads = empty( $homemap_ads ) ? adifier_get_homemap_ads_list() : $homemap_ads;
	return !empty( $homemap_ads[$advert_id] ) ? $homemap_ads[$advert_id] : '';
}
}

/*
* Check if top ad is set for the given advert which is called by the Order and admin advert manipulation
*/
if( !function_exists('adifier_check_homemap') ){
function adifier_check_homemap(){
	$max_homemap_ads = adifier_get_option('max_homemap_ads');
	$homemap_ads = adifier_get_homemap_ads_list();
	$advert_time = adifier_get_homemap_ad_advert_time( get_the_ID(), $homemap_ads );
	if( !empty( $advert_time ) ){
		return $advert_time >= current_time( 'timestamp' ) ? sprintf( esc_html__( '(Active until: %s)', 'adifier' ), date_i18n( get_option( 'date_format' ), $advert_time ) ) : '';
	}
	else if( !empty( $max_homemap_ads ) && sizeof( $homemap_ads ) >= $max_homemap_ads ){
		return sprintf( esc_html__( '(First available spot is on: %s)', 'adifier' ), date_i18n( get_option( 'date_format' ),  min( $homemap_ads ) ) );
	}
	else{
		return false;
	}
}
}


/*
* Report advert
*/
if( !function_exists('adifier_report_advert') ){
function adifier_report_advert(){
	if( !isset( $_REQUEST['adifier_nonce'] ) || !wp_verify_nonce( $_REQUEST['adifier_nonce'], 'adifier_nonce' ) ){
		die();
	}	
	$advert_id = !empty( $_POST['advert_id'] ) ? sanitize_text_field( $_POST['advert_id'] ) : '';
	$reason = !empty( $_POST['reason'] ) ? sanitize_textarea_field( $_POST['reason'] ) : '';
	if( !empty( $reason ) && !empty( $advert_id ) ){
		$reported_by = '';
		if( is_user_logged_in() ){
			$reported_by = wp_get_current_user();
		}
		update_post_meta( $advert_id, 'advert_report', $reason );
		if( !empty( $reported_by ) ){
			update_post_meta( $advert_id, 'advert_report_by', adifier_author_name( $reported_by ) );
		}
		$admin_email = get_option( 'admin_email' );
		ob_start();
		include( get_theme_file_path( 'includes/emails/report-advert.php' ) );
		$message = ob_get_contents();
		ob_end_clean();
		adifier_send_mail( $admin_email, esc_html__( '[Ad Reported] - ', 'adifier' ).get_the_title( $advert_id ), $message  );
		$response['message'] = '<div class="alert-success">'.esc_html__( 'Ad is reported', 'adifier' ).'</div>';
	}
	else{
		$response['message'] = '<div class="alert-error">'.esc_html__( 'Required fields are empty', 'adifier' ).'</div>';
	}

	echo json_encode( $response );

	die();
}
add_action( 'wp_ajax_adifier_report_advert', 'adifier_report_advert' );
add_action( 'wp_ajax_nopriv_adifier_report_advert', 'adifier_report_advert' );
}


/*
* Display category hierarchy for profile adverts listing
*/
if( !function_exists('adifier_category_hierarchy_profile_adverts') ){
function adifier_category_hierarchy_profile_adverts( $terms ){
	if( !empty( $terms ) ){
		foreach( $terms as $term ){
			echo '<a href="'.esc_url( get_term_link( $term ) ).'" target="_blank">'.$term->name.'</a>';
			if( !empty( $term->children ) ){
				echo ' > ';
				adifier_category_hierarchy_profile_adverts( $term->children );
			}
		}
	}
}
}


/*
* Display category hierarchy in select category dropdown on advert form
*/
if( !function_exists('addifier_hierarchy_select_taxonomy') ){
function addifier_hierarchy_select_taxonomy( $terms, $depth = 0, $selected = array(), $allow_parent = false ){
	if( is_wp_error( $terms ) ){
		return;
	}
	foreach( $terms as $term ){
		if( !empty( $term->children ) && !$allow_parent ){
			echo '<optgroup label="'.str_repeat('&nbsp;', $depth).$term->name.'">';
				addifier_hierarchy_select_taxonomy( $term->children, $depth + 2, $selected );
			echo '</optgroup>';
		}
		else{
			echo '<option value="'.esc_attr( $term->term_id ).'" '.( in_array( trim( $term->term_id ), $selected ) ? 'selected="selected"' : '' ).'>'.str_repeat('&nbsp;', $depth).$term->name.'</option>';
		}

		if( !empty( $term->children ) && $allow_parent ){
			addifier_hierarchy_select_taxonomy( $term->children, $depth + 2, $selected, true );
		}
	}
}
}

/*
* Check if advert is in favorites
*/
if( !function_exists('adifier_is_favorite') ){
function adifier_is_favorite(){
	$favorites_ads = get_user_meta( get_current_user_id(), 'favorites_ads', true);
	if( is_array( $favorites_ads ) && in_array( get_the_ID(), $favorites_ads ) ){
		return true;
	}
	else{
		return false;
	}
}
}

/*
* Get advert location source
*/
if( !function_exists('adifier_get_location_source') ){
	$use_google_location = adifier_get_option( 'use_google_location' );
	$use_predefined_locations = adifier_get_option( 'use_predefined_locations' );
	$source = 'geo_value';
	if( $use_google_location == 'no' && $use_predefined_locations == 'yes' ){
		$source = 'predefined';
	}
	else if( $use_google_location == 'yes' && $use_predefined_locations == 'yes' ){
		$source = adifier_get_option( 'single_location_display' );
	}
}

/*
* Clean location for displaying on single avert or display taxonomy location
*/
if( !function_exists('adifier_show_single_address') ){
function adifier_show_single_address( $location ){
	$source = adifier_get_location_source( 'single_location_display' );
	if( $source == 'geo_value' ){
		$address_order = adifier_get_option( 'address_order' );
		if( !empty( $location ) ){
			unset( $location['lat'] );
			unset( $location['long'] );
			if( $address_order == 'back' ){
				$location = array_reverse( $location );
			}			
			return array_filter( $location );
		}
	}
	else{
		$list = array();
		$locations = wp_get_post_terms( get_the_ID(), 'advert-location' );
		if( !empty( $locations ) ){
			$locations = adifier_taxonomy_hierarchy( $locations );
			$location_ids = adifier_taxonomy_id_name_hierarchy( $locations );
			foreach( $location_ids as $data ){
				$list[] = '<a href="'.esc_url( get_term_link( $data['term_id'] ) ).'">'.$data['name'].'</a>';
			}
		}
		return $list;
	}
}
}

/*
* Add custom columns to advert custom post type
*/
if( !function_exists('adifier_advert_custom_admin_columns') ){
function adifier_advert_custom_admin_columns( $columns ) {

	$columns = array_slice($columns, 0, 3, true) + array("type" => esc_html__( 'Type', 'adifier' ), "expire" => esc_html__( 'Expire', 'adifier' )) + array_slice($columns, 3, count($columns) - 1, true) ;    

    return $columns;
}
add_filter( 'manage_advert_posts_columns', 'adifier_advert_custom_admin_columns' );
}

/*
* Display value in the custom admin columns
*/
if( !function_exists('adifier_advert_custom_admin_column_values') ){
function adifier_advert_custom_admin_column_values( $column, $post_id ) {
    switch ( $column ) {

        case 'type' :
        	$type = adifier_get_advert_meta( $post_id, 'type', true );
        	switch( $type ){
        		case '1' : esc_html_e( 'Sell', 'adifier' ); break;
        		case '2' : esc_html_e( 'Auction', 'adifier' ); break;
        		case '3' : esc_html_e( 'Buying', 'adifier' ); break;
        		case '4' : esc_html_e( 'Exchange', 'adifier' ); break;
        		case '5' : esc_html_e( 'Gift', 'adifier' ); break;
        		case '6' : esc_html_e( 'Rent', 'adifier' ); break;
        		case '7' : esc_html_e( 'Job - Offer', 'adifier' ); break;
        		case '8' : esc_html_e( 'Job - Wanted', 'adifier' ); break;
        	}
        	break;
        case 'expire' :
        	$expire = adifier_get_advert_meta( $post_id, 'expire', true );
        	if( !empty( $expire ) ){
        		echo date_i18n( get_option('date_format'), $expire );
        	}

    }
}
add_action( 'manage_advert_posts_custom_column' , 'adifier_advert_custom_admin_column_values', 10, 2 );
}

/*
* Check if ad type is allowed
*/
if( !function_exists('adifier_is_allowed_ad_type') ){
function adifier_is_allowed_ad_type( $ad_type ){
	$ad_types = adifier_get_option( 'ad_types' );
	if( empty( $ad_types ) ){
		return true;
	}
	$allowed_types = array();
	foreach( $ad_types as $key => $value ){
		if( !empty( $value ) && $value == '1' ){
			$allowed_types[] = $key;
		}
	}
	if( empty( $allowed_types ) ){
		return true;
	}
	else if( in_array( $ad_type, $allowed_types ) ){
		return true;
	}
	else{
		return false;
	}
}
}

/*
* Check if there is only one ad type
*/
if( !function_exists('adifier_is_single_ad_type') ){
function adifier_is_single_ad_type(){
	$ad_types = adifier_get_option( 'ad_types' );
	$count = 0;
	if( !empty( $ad_types ) ){
		foreach( $ad_types as $key => $value ){
			if( !empty( $value ) ){
				$count++;
			}
		}
	}

	return $count == 1 ? true : false;
}
}

/*
Print single type
*/
if( !function_exists('adifier_print_single_ad_type') ){
function adifier_print_single_ad_type(){
	$ad_types = adifier_get_option( 'ad_types' );
	$count = 0;
	foreach( $ad_types as $key => $value ){
		if( !empty( $value ) ){
			?>
			<input type="hidden" value="<?php echo esc_attr( $key ) ?>" name="type">
			<?php
		}
	}
}
}

/*
* If there is manual approval we need to differentiate which iamges should be deleted and which do not
*/
if( !function_exists('adifier_manual_approval_image_selection') ){
function adifier_manual_approval_image_selection( $update_id, $advert_id ){
	$advert_gallery = $advert_gallery_diff = get_post_meta( $advert_id, 'advert_gallery' );
	$update_gallery = $update_gallery_diff = get_post_meta( $update_id, 'advert_gallery' );

	$advert_featured = get_post_thumbnail_id( $advert_id );
	if( !empty( $advert_featured ) ){
		$advert_gallery_diff[] = $advert_featured;
	}
	$update_featured = get_post_thumbnail_id( $update_id );
	if( !empty( $update_featured ) ){
		$update_gallery_diff[] = $update_featured;
	}	

	delete_post_meta( $update_id, 'advert_gallery' );
	$diff = array_diff( $advert_gallery_diff, $update_gallery_diff );
	if( !empty( $diff ) ){
		foreach( $diff as $image_id ){
			add_post_meta( $update_id, 'advert_gallery', $image_id );
		}
	}

	if( in_array( $update_featured, $advert_gallery_diff ) ){
		delete_post_thumbnail( $update_id );
	}

	return $update_gallery;
}
}

/*
* If manual approval is enabled we need to copy data from child to paren
*/
if( !function_exists('adifier_manual_approval_action') ){
function adifier_manual_approval_action( $post_id, $post, $update ){
	if ( $post->post_status === 'trash' ) {
        return;
    }	
	if( isset( $_POST['advert_report'] ) ){
		if( get_post_meta( $post_id, 'adifier_manual_approve', true ) ){
			if( $post->post_parent != '0' ){
				unset( $_POST['advert_gallery'] );
				global $wpdb;
				/* CLEAR OLD TAXONOMIES */
				$old_taxonomies = get_post_taxonomies( $post->post_parent );
				wp_delete_object_term_relationships( $post->post_parent, $old_taxonomies );

				$new_taxonomies = get_post_taxonomies( $post_id );
				foreach( $new_taxonomies as $taxonomy ){
					$new_terms = get_the_terms( $post_id, $taxonomy );
					if( !empty( $new_terms ) ){
						$new_terms_ids = wp_list_pluck( $new_terms, 'term_id' );
						wp_set_post_terms( $post->post_parent, $new_terms_ids, $taxonomy );
					}
				}

				$af_needs_renew = get_post_meta( $post_id, 'af_needs_renew', true );

				/* SWITCH CUSTOM META */
				$wpdb->update(
					$wpdb->prefix.'adifier_advert_data',
					array(
						'latitude'		=> adifier_get_advert_meta( $post_id, 'latitude', true ),
						'longitude'		=> adifier_get_advert_meta( $post_id, 'longitude', true ),
						'cond'			=> adifier_get_advert_meta( $post_id, 'cond', true ),
						'price'			=> adifier_get_advert_meta( $post_id, 'price', true ),
						'sale_price'	=> adifier_get_advert_meta( $post_id, 'sale_price', true ),
						'start_price'	=> adifier_get_advert_meta( $post_id, 'start_price', true ),
						'currency'		=> adifier_get_advert_meta( $post_id, 'currency', true ),
						'sold'			=> adifier_get_advert_meta( $post_id, 'sold', true ),
						'expire'		=> !empty( $af_needs_renew ) ? adifier_calculate_expire_time( $post_id ) : adifier_get_advert_meta( $post->post_parent, 'expire', true ),
						'exp_info'		=> !empty( $af_needs_renew ) ? 0 : adifier_get_advert_meta( $post->post_parent, 'exp_info', true )
					),
					array(
						'post_id'	=> $post->post_parent
					),
					array(
						'%s',
						'%s',
						'%d',
						'%f',
						'%f',
						'%f',
						'%s',
						'%d',
						'%f',
						'%s'
					),
					array(
						'%d'
					)

				);

				/* UPDATE FEATURED IMAGE */
				set_post_thumbnail( $post->post_parent, get_post_thumbnail_id( $post_id ) );

				/* SWITCH META VALUES */
				delete_post_meta( $post->post_parent, 'adifier_has_update' );
				delete_post_meta( $post_id, 'adifier_is_update' );

				$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key IN ('advert_videos', 'advert_location', 'advert_negotiable', 'advert_location', 'advert_phone', 'advert_reserved_price')", $post->post_parent));
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET post_id = %d WHERE post_id = %d AND meta_key IN ('advert_videos', 'advert_location', 'advert_negotiable', 'advert_location', 'advert_phone', 'advert_reserved_price')", $post->post_parent, $post_id));

				/* UPDATE ADVERT */
				$update_data = array(
					'post_title'	=> $post->post_title,
					'post_content'	=> $post->post_content,
					'post_excerpt'	=> $post->post_excerpt
				);

				
				if( !empty( $af_needs_renew ) ){
					$update_data['post_date'] = $post->post_date;
					$update_data['post_date_gmt'] = $post->post_date_gmt;
				}

				$wpdb->update(
					$wpdb->posts,
					$update_data,
					array(
						'ID'	=> $post->post_parent
					)
				);

				set_transient( get_current_user_id()."_redirect", add_query_arg( array( 'post' => $post->post_parent, 'action' => 'edit' ), admin_url( 'post.php' ) ) );
				set_transient( get_current_user_id()."_clear_update_id", $post_id );

				$new_gallery = adifier_manual_approval_image_selection( $post_id, $post->post_parent );
				delete_post_meta( $post->post_parent, 'advert_gallery' );
				if( !empty( $new_gallery ) ){
					foreach( $new_gallery as $image_id ){
						add_post_meta( $post->post_parent, 'advert_gallery', $image_id );
					}
				}

				$email_title = esc_html__( 'Ad Update Approved', 'adifier' );
				$email_message = sprintf( __('Congratulations, your submitted ad update %s is approved', 'adifier' ), '<a href="'.get_the_permalink( $post->post_parent ).'" target="_blank" style="color:'.adifier_get_option( 'main_color' ).';"><b>'.$post->post_title.'</b></a>' );
			}
			else {
				$email_title = esc_html__( 'Ad Approved', 'adifier' );
				$email_message = sprintf( __('Congratulations, your submitted ad %s is approved', 'adifier' ), '<a href="'.get_the_permalink( $post_id ).'" target="_blank" style="color:'.adifier_get_option( 'main_color' ).';"><b>'.$post->post_title.'</b></a>' );
			}

			delete_post_meta( $post_id, 'adifier_manual_approve' );

			ob_start();
			include( get_theme_file_path( 'includes/emails/advert-approval-status-user.php' ) );
			$message = ob_get_contents();
			ob_end_clean();
			adifier_send_mail( get_the_author_meta( 'user_email', $post->post_author ), esc_html__( 'Ad Approved', 'adifier' ), $message );			
		}
	}
}
add_action( 'save_post', 'adifier_manual_approval_action', 12, 3 );
}

/*
* If manual approval is enabled there is no point of quick edit on drafts
*/
if( !function_exists('adifier_manual_approval_remove_quick_edit') ){
function adifier_manual_approval_remove_quick_edit( $actions, $post ) {
    // Check for your post type.
    if ( $post->post_type == "advert" && $post->post_status == 'draft' ) {
    	unset( $actions['inline hide-if-no-js'] );
    }
 
    return $actions;
}
add_filter( 'post_row_actions', 'adifier_manual_approval_remove_quick_edit', 10, 2 );
}

/*
* If the advert is deleted then it is not approved
*/
if( !function_exists('adifier_declined_ad') ){
function adifier_declined_ad( $post_id ){
	if( empty( $_POST['adifier_manual_approve_cleanup'] ) ){
	    $post = get_post( $post_id );
	    if ( $post->post_type == 'advert' ){
	    	if( get_post_meta( $post_id, 'adifier_manual_approve', true ) ){
	    		if( $post->post_parent != '0' ){

	    			adifier_manual_approval_image_selection( $post_id, $post->post_parent );

					$email_title = esc_html__( 'Ad Update Declined', 'adifier' );
					$email_message = sprintf( __('Unfortunately, your submitted ad update <b>%s</b> is declined since it does not meet our terms & conditions', 'adifier' ), $post->post_title );
	    		}
	    		else{
					$email_title = esc_html__( 'Ad Declined', 'adifier' );
					$email_message = sprintf( __('Unfortunately, your submitted ad <b>%s</b> is declined since it does not meet our terms & conditions', 'adifier' ), $post->post_title );
	    		}    		
				ob_start();
				include( get_theme_file_path( 'includes/emails/advert-approval-status-user.php' ) );
				$message = ob_get_contents();
				ob_end_clean();		
				adifier_send_mail( get_the_author_meta( 'user_email', $post->post_author ), esc_html__( 'Ad Declined', 'adifier' ), $message );    		
	    	}
	    }
	}
}
add_action( 'before_delete_post', 'adifier_declined_ad', 10);
}

/*
* Remove promotion meta boxes on child adverts since those are updates
*/
if( !function_exists('adifier_remove_promotions_on_child') ){
function adifier_remove_promotions_on_child(){
	global $post;
	if( $post && $post->post_type == 'advert' && $post->post_parent != '0' ){
		remove_meta_box( 'promotions', 'advert', 'side' );
	}
}
add_action( 'do_meta_boxes' , 'adifier_remove_promotions_on_child' );
}

/*
* once the advert update is approved ( rom child ) redirect to parent advert
*/
if( !function_exists('adifier_redirect_update_approval') ){
function adifier_redirect_update_approval(){
	if( !empty( $_GET['af_cleanup'] ) || class_exists( 'Classic_Editor' ) ){
		$clear_update_id = get_transient( get_current_user_id()."_clear_update_id" );
		if( !empty( $clear_update_id ) ){
			$_POST['adifier_manual_approve_cleanup'] = 1;
			delete_transient( get_current_user_id()."_clear_update_id" );
			wp_delete_post( $clear_update_id, true );
			unset( $_POST['adifier_manual_approve_cleanup'] );
		}
	}

	$redirect = get_transient( get_current_user_id()."_redirect" );
	if( !empty( $redirect ) ){
		delete_transient( get_current_user_id()."_redirect" );
		if( class_exists( 'Classic_Editor' ) ){
			wp_redirect( $redirect );
			die();
		}
	}
}
add_action( 'admin_menu', 'adifier_redirect_update_approval' );
}

/*
* Add additional hidden input on admin edit form so redirect works properlly
*/
if( !function_exists( 'adifier_admin_edit_hidden_field' ) ){
function adifier_admin_edit_hidden_field( $post ){
	if( $post->post_parent !== 0 ){
		?>
		<input type="hidden" class="post_parent" value="<?php echo esc_attr( $post->post_parent ) ?>">
		<?php
	}
}
add_action( 'block_editor_meta_box_hidden_fields', 'adifier_admin_edit_hidden_field' );
}

/*
* Add placeholder image if not image is created
*/
if( !function_exists('adifier_get_advert_image') ){
function adifier_get_advert_image( $image_size = 'thumbnail' ){
	if( has_post_thumbnail() ){
		the_post_thumbnail( $image_size );
	}
	else{
		$placeholder_thumbnail = adifier_get_option( 'placeholder_thumbnail' );
		if( !empty( $placeholder_thumbnail['id'] ) ){
			echo wp_get_attachment_image( $placeholder_thumbnail['id'], $image_size, false, array('class' => 'wp-post-image') );
		}
	}
}
}

/*
* Strip price of symbols
*/
if( !function_exists('adifier_clean_price') ){
function adifier_clean_price( $price, $currency ){
	$currencies = adifier_get_currencies();
	if( !empty( $currencies[$currency] ) ){
		$price = str_replace( array( $currencies[$currency]['thousands_separator'], $currencies[$currency]['decimal_separator'] ), '', $price );
	}

	return $price;
}
}

/*
* Get currencies
*/
if( !function_exists('adifier_get_currencies') ){
function adifier_get_currencies(){
	global $adifier_options;
	$main_abbr = adifier_get_option( 'currency_abbr' );
	$list[$main_abbr] = array(
		'abbr' 					=> $main_abbr,
		'sign' 					=> adifier_get_option( 'currency_symbol' ),
		'form' 					=> adifier_get_option( 'currency_location' ),
		'rate' 					=> 1,
		'thousands_separator' 	=> adifier_get_option( 'thousands_separator' ),
		'decimal_separator' 	=> adifier_get_option( 'decimal_separator' ),
		'show_decimals' 		=> adifier_get_option( 'show_decimals' ),
	);
	$currencies = adifier_get_option( 'currencies' );
	if( !empty( $currencies ) ){
		$groups = explode( '+', $currencies );
		if( !empty( $groups ) ){
			foreach( $groups as $group ){
				$values = explode( '|', $group );
				if( !empty( $values[0] ) ){
					$list[$values[0]] = array(
						'abbr' 					=> $values[0],
						'sign' 					=> $values[1],
						'form' 					=> !empty( $values[2] ) ? ( $values[2] == 'F' ? 'front' : 'back' ) : adifier_get_option( 'currency_location' ),
						'rate' 					=> !empty( $values[3] ) ? $values[3] : 1,
						'thousands_separator' 	=> !empty( $values[4] ) ? $values[4] : '',
						'decimal_separator' 	=> !empty( $values[5] ) ? $values[5] : '',
						'show_decimals' 		=> ( !empty( $values[6] ) && $values[6] == 'Y' ) ? 'yes' : 'no'
					);
				}
			}
		}
	}

	return $list;
}
}

/*
* Get currency raw array
*/
if( !function_exists( 'adifier_get_currencies_raw_list' ) ){
function adifier_get_currencies_raw_list(){
	$list = array();
	$currencies = adifier_get_currencies();
	if( count( $currencies ) > 1 ) {
		foreach( $currencies as $key => $data ){
			$list[$key] = $data['abbr'].' ('.$data['sign'].')';
		}
	}

	return $list;
}
}

/*
* Form currency selector
*/
if( !function_exists('adifier_currency_select') ){
function adifier_currency_select( $currencies, $selected = 0, $show_name = true ){
	?>
	<div class="form-group">
		<div class="styled-select">
			<label for="currency"><?php esc_html_e( 'Currency', 'adifier' ) ?></label>
			<select name="<?php echo $show_name ? esc_attr('currency') : esc_attr('') ?>" id="currency" class="currency-swap">
				<?php
				foreach( $currencies as $key => $abbr ){
					?>
					<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $selected, $key ) ?>><?php echo esc_html( $abbr ) ?></option>
					<?php
				}
				?>
			</select>
		</div>
	</div>
	<?php
}
}

/*
* Normalise price to base currency
*/
if( !function_exists('adifier_normalize_currency') ){
function adifier_normalize_currency( $value, $currency ){
	$currencies = adifier_get_currencies();
	
	if( count( $currencies ) > 1 ){
		return $value * $currencies[$currency]['rate'];
	}
	else{
		return $value;
	}
}
}

/*
* Inform users about expired ads
*/
if( !function_exists('adifier_process_expired_ads') ){
function adifier_process_expired_ads(){
	global $wpdb;
	$adifier_exp_ads_processing_ids = get_transient( 'adifier_exp_ads_processing_ids' );
	$query = $wpdb->prepare("SELECT p.ID, post_author, post_title FROM {$wpdb->posts} as p LEFT JOIN {$wpdb->prefix}adifier_advert_data AS aad ON p.ID = aad.post_id WHERE aad.expire > 0 AND aad.expire < %d AND type <> 2 AND exp_info <> 1", current_time('timestamp'));
	if( !empty( $adifier_exp_ads_processing_ids ) ){
		$query .= " AND p.ID NOT IN (".esc_sql( join(',', $adifier_exp_ads_processing_ids) ).")";
	}
	$query .= " ORDER BY expire ASC LIMIT 30";
	$adverts = $wpdb->get_results( $query );

	if( !empty( $adverts ) ){
		if( empty( $adifier_exp_ads_processing_ids ) ){
			$ids = wp_list_pluck( $adverts, 'ID' );
			set_transient( 'adifier_exp_ads_processing_ids', $ids, 60 );
		}
		
		$taxonomies = get_object_taxonomies( 'advert' );
		$terms = array();

		foreach( $adverts as $advert ){
			/* update category and locations count to exclude expired */
			$terms = array_merge( $terms, wp_get_object_terms( $advert->ID, $taxonomies ) );

			$seller = get_user_by( 'ID', $advert->post_author );
			ob_start();
			include( get_theme_file_path( 'includes/emails/advert-expired.php' ) );
			$message = ob_get_contents();
			ob_end_clean();
			adifier_send_mail( $seller->user_email, esc_html__( 'Advert Expired - Renew', 'adifier' ), $message );

			$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}adifier_advert_data SET exp_info = 1 WHERE post_id = %d", $advert->ID));
		}

		adifier_update_taxonomy_count( $terms );
	}
}
add_action( 'wp_head', 'adifier_process_expired_ads' );
}


/* 
Rent periods
*/
if( !function_exists( 'adifier_get_rent_periods' ) ){
function adifier_get_rent_periods(){
	return array(
		'5' => esc_html__( 'Year', 'adifier' ),
		'7' => esc_html__( 'Half Year', 'adifier' ),
		'6' => esc_html__( 'Quarterly', 'adifier' ),
		'1' => esc_html__( 'Month', 'adifier' ),
		'2' => esc_html__( 'Week', 'adifier' ),
		'3' => esc_html__( 'Day', 'adifier' ),
		'4' => esc_html__( 'Hour', 'adifier' ),		
	);
}
}

/*
* Check if rent period
*/
if( !function_exists('adifier_is_allowed_rent_period') ){
function adifier_is_allowed_rent_period( $rent_period ){
	$rent_periods = adifier_get_option( 'rent_periods' );
	if( empty( $rent_periods ) ){
		return true;
	}
	$allowed_rents = array();
	foreach( $rent_periods as $key => $value ){
		if( !empty( $value ) && $value == '1' ){
			$allowed_rents[] = $key;
		}
	}
	if( empty( $allowed_rents ) ){
		return true;
	}
	else if( in_array( $rent_period, $allowed_rents ) ){
		return true;
	}
	else{
		return false;
	}
}
}

?>