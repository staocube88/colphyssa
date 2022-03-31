<?php
/*
* Get number of favorite adverts
*/
if( !function_exists('adifier_count_favorited') ){
function adifier_count_favorited(){
	global $wpdb;
	$count = 0;
	$favorited = get_user_meta( get_current_user_id(), 'favorites_ads', true);
	if( !empty( $favorited ) ){
		$count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE ID IN (".esc_sql( join( ',', $favorited ) ).") AND post_type = 'advert'");
	}

	return $count;
}
}

/*
* Get id and title of user ad
*/
if( !function_exists('adifier_id_title_ad_chart') ){
function adifier_id_title_ad_chart(){
	global $wpdb;
	$items = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_author = %d AND post_type = 'advert' AND post_status = 'publish'", get_current_user_id()));

	$data = array();
	if( !empty( $items ) ){
		foreach( $items as $item  ){
			$data[$item->ID] = $item->post_title;
		}
	}

	return $data;
}
}

/*
* Retrive chart data by post ID
*/
if( !function_exists('adifier_chart_data_by_id') ){
function adifier_chart_data_by_id(){
	if( !isset( $_REQUEST['adifier_nonce'] ) || !wp_verify_nonce( $_REQUEST['adifier_nonce'], 'adifier_nonce' ) ){
		die();
	}
	global $wpdb;
	$labels = array();
	$data = array();
	$views_data = $wpdb->get_var($wpdb->prepare("SELECT views_data FROM {$wpdb->prefix}adifier_advert_data WHERE post_id = %d", sanitize_text_field(  $_POST['advert_id'] )));
	if( !empty( $views_data ) ){
		$views_data = json_decode( $views_data );
		foreach( $views_data as $timestamp => $count ){
			if( sizeof( $labels ) < 30 ){
				$labels[] = date_i18n( get_option( 'date_format' ), $timestamp );
				$data[] = absint( $count );
			}
			else{
				break;
			}
		}
	}

	$response = array(
		'empty' 	=> empty( $data ),
		'labels' 	=> $labels,
		'data'		=> $data,
		'max'		=> !empty( $data ) ? max(array_values( $data )) : 0
	);
	echo json_encode( $response );
	die();
}
add_action('wp_ajax_chart_data', 'adifier_chart_data_by_id');
}


/*
* Update top parent comment as unread
*/
if( !function_exists('adifier_set_top_comment_unread') ){
function adifier_set_top_comment_unread( $comment ){
	$post_author = get_post_field( 'post_author', $comment->comment_post_ID );
	if( $comment->user_id != $post_author ){
		$parent_id = adifier_get_top_comment_id( $comment );		
		update_comment_meta( $parent_id, 'af_unread', 1 );

		$author = get_user_by( 'ID', $post_author );

		$sender_name = $comment->comment_author;
		$advert_url = get_the_permalink( $comment->comment_post_ID );
		$advert_title = get_the_title( $comment->comment_post_ID );
		$comment_content = $comment->comment_content;
		$dashboard_url = get_author_posts_url( $post_author );

		ob_start();
		include( get_theme_file_path( 'includes/emails/unread-comment.php' ) );
		$message_email = ob_get_contents();
		ob_end_clean();
		adifier_send_mail( $author->user_email, esc_html__( 'New Comment Waiting', 'adifier' ), $message_email );
	}
}
}

/*
* Mark top parent comment as unread on comment save
*/
if( !function_exists('adifier_top_comment_unread') ){
function adifier_top_comment_unread( $comment_id, $comment_status ){
	$comment = get_comment( $comment_id );
	if( in_array( $comment_status, array('1', 'approved', 'approve') ) ){
		adifier_set_top_comment_unread( $comment );
	}

}
add_action( 'wp_set_comment_status', 'adifier_top_comment_unread', 10, 2 );
}

if( !function_exists( 'adifier_on_comment_insert' ) ){
function adifier_on_comment_insert( $comment_id, $comment_approved, $commentdata ){
	if( $comment_approved === 1 ){
		$commentdata['comment_ID'] = $comment_id;
		adifier_set_top_comment_unread( (object)$commentdata );
	}
}
add_action( 'comment_post', 'adifier_on_comment_insert', 10, 3 );
}

/*
* Get top parent comment
*/
if( !function_exists('adifier_get_top_comment_id') ){
function adifier_get_top_comment_id( $comment ){
	if( $comment->comment_parent == 0 ){
		$parent = $comment->comment_ID;
	}
	else{
		$comment = get_comment( $comment->comment_parent );
		$parent = adifier_get_top_comment_id( $comment );
	}

	return $parent;
}
}


/*
* Mark top comment parent as read if it is clicked to mark as read
*/
if( !function_exists('adifier_top_comment_mark_read') ){
function adifier_top_comment_mark_read(){
	if( !isset( $_REQUEST['adifier_nonce'] ) || !wp_verify_nonce( $_REQUEST['adifier_nonce'], 'adifier_nonce' ) ){
		die();
	}
	$response = array();
	if( adifier_check_comment_post_owner() ){
		delete_comment_meta( sanitize_text_field( $_POST['parent_id'] ), 'af_unread' );
	}
	else{
		$response['error'] = esc_html__( 'Wrong author', 'adifier' );
	}

	echo json_encode( $response );

	die();
}
add_action( 'wp_ajax_adifier_top_comment_mark_read', 'adifier_top_comment_mark_read' );
}

/*
* Check if the author is owner of the post and comment
*/
if( !function_exists('adifier_check_comment_post_owner') ){
function adifier_check_comment_post_owner(){
	$parent_comment = get_comment( $_POST['parent_id'] );
	if( !is_wp_error( $parent_comment ) ){
		$post_id = $parent_comment->comment_post_ID;
			$post = get_post( $post_id );
			$user_id = get_current_user_id();
			if( $post->post_author == $user_id ){
				return $user_id;
			}
	}

	return false;
}
}

/*
* Mark top comment as unread if it is answered
*/
if( !function_exists('adifier_save_reply_comment') ){
function adifier_save_reply_comment(){
	if( !isset( $_REQUEST['adifier_nonce'] ) || !wp_verify_nonce( $_REQUEST['adifier_nonce'], 'adifier_nonce' ) ){
		die();
	}
	if( empty( $_POST['comment_message'] ) ){
		$response['error'] = esc_html__( 'Comment is empty', 'adifier' );
	}
	else{
		$parent_id_reply = sanitize_text_field( $_POST['parent_id_reply'] );
		$comment_parent = sanitize_text_field( $_POST['parent_id'] );
		$comment_message = sanitize_textarea_field( $_POST['comment_message'] );
		$post_id = sanitize_text_field( $_POST['post_id'] );


		$user_id = adifier_check_comment_post_owner();
		if( $user_id ){
			$user = get_userdata( $user_id );
			$id = wp_insert_comment(array(
				'comment_approved' 	=> '1',
				'comment_author' 	=> adifier_author_name( $user ),
				'comment_parent'	=> $parent_id_reply,
				'comment_content'	=> $comment_message,
				'comment_post_ID'	=> $post_id,
				'user_id' 			=> $user_id
			));

			if( $id ){
				delete_comment_meta( $comment_parent, 'af_unread' );

				/* now let's inform sender that the author have replied */
				$comment_parent = get_comment( $parent_id_reply );
				
				$author_name = adifier_author_name( $user );
				$comment_url = get_the_permalink( $post_id ).'#comment-'.$id;
				$advert_title = get_the_title( $post_id );
				$comment_content = $comment_message;
		
				ob_start();
				include( get_theme_file_path( 'includes/emails/comment-sender.php' ) );
				$message_email = ob_get_contents();
				ob_end_clean();
				adifier_send_mail( $comment_parent->comment_author_email, sprintf( esc_html__( 'Reply From %s', 'adifier' ), adifier_author_name( $user )), $message_email );
				
				$response['comment'] = '<div class="ajax-response">
					<b class="ajax-response-title">'.esc_html__( 'Your reply', 'adifier' ).'</b>
					<p class="ajax-response-content">'.$comment_message.'</p>
				</div>';
			}
			else{
				$response['error'] = esc_html__( 'Could not save the comment, try again', 'adifier' );
			}
		}
		else{
			$response['error'] = esc_html__( 'Wrong author', 'adifier' );
		}
	}

	echo json_encode( $response );

	die();
}	
add_action( 'wp_ajax_adifier_save_reply_comment', 'adifier_save_reply_comment' );
}

/*
* Display unread comments on dashboard
*/
if( !function_exists('adifier_unread_comments') ){
function adifier_unread_comments(){
	global $wpdb;
	$results = $wpdb->get_results( $wpdb->prepare("SELECT p.post_title, p.ID, c.* FROM {$wpdb->prefix}posts AS p LEFT JOIN {$wpdb->comments} AS c ON p.ID = c.comment_post_ID LEFT JOIN {$wpdb->prefix}commentmeta AS cm ON c.comment_ID = cm.comment_id WHERE p.post_author = %d AND c.comment_parent = 0 AND cm.meta_key = 'af_unread'", get_current_user_id()) );
	if( !empty( $results ) ){
		foreach( $results as $result ){
			?>
			<div class="white-block">
				<div class="white-block-content unread-comments">
					<?php adifier_list_unread_comments( $result, true ); ?>
				</div>
			</div>
			<?php
		}
	}
}
}

/*
* List unread comments
*/
if( !function_exists('adifier_list_unread_comments') ){
function adifier_list_unread_comments( $comment, $parent = false ){
	global $wpdb;
	?>
	<div class="comment-wrap <?php echo $parent ? 'comment-parent' : esc_attr( 'comment-margin-left' ) ?>" data-parent_id="<?php echo esc_attr( $comment->comment_ID ) ?>">
		<div class="comment">
			<div class="unread-header flex-wrap">
				<div class="flex-wrap flex-start-h flex-center">
					<div class="comment-avatar">
						<?php echo get_avatar( $comment->comment_author_email ); ?>
					</div>
					<div class="comment-info">
						<?php
						echo $comment->user_id != '0' ? '<a href="'.esc_url( get_author_posts_url( $comment->user_id ) ).'" target="_blank">' : '';
							echo '<h5>'.$comment->comment_author.'</h5>';
						echo $comment->user_id != '0' ? '</a>' : '';
						?>
						<?php if( $parent ){ 
							?>
							<a href="<?php echo esc_url( get_the_permalink( $comment->ID ) ) ?>" target="_blank">
								<p><?php echo $comment->post_title ?></p>
							</a>
							<?php
						}
						?>
					</div>
				</div>
				<div>
					<?php if( $parent ){  ?>
						<a href="javascript:;" class="mark-as-read" data-parent_id="<?php echo esc_attr( $comment->comment_ID ) ?>" title="<?php esc_html_e( 'Mark as read', 'adifier' ) ?>">
							<i class="aficon-circle"></i>
						</a>
					<?php } ?>
					<?php if( $comment->user_id != get_current_user_id() ){  ?>
						<a href="javascript:;" class="reply" title="<?php esc_html_e( 'Reply', 'adifier' ) ?>">
							<i class="aficon-reply"></i>
						</a>
					<?php } ?>
				</div>
			</div>
			<div class="comment-content-wrap">
				<p class="no-margin"><?php echo $comment->comment_content ?></p>
				<div class="reply-holder"></div>				
				<div class="unread-reply-form hidden">
					<textarea class="comment_message"></textarea>
					<p class="form-submit no-margin">
						<a href="javascript:;" class="cancel-reply"><?php esc_html_e( 'Cancel reply', 'adifier' ) ?></a>
						<a href="javascript:;" class="send-reply af-button" data-post_id="<?php echo esc_attr( $comment->comment_post_ID ) ?>" data-parent_id_reply="<?php echo esc_attr( $comment->comment_ID ) ?>"><?php esc_html_e( 'Send', 'adifier' ) ?></a>
					</p>
				</div>
			</div>
		</div>	
		<div class="comment-children">	
			<?php
			$children = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}comments WHERE comment_approved = '1' AND comment_parent = %d", $comment->comment_ID) );
			if( !empty( $children ) ){
				foreach( $children as $child ){
					adifier_list_unread_comments( $child );
				}
			}
			?>
		</div>
	</div>	
	<?php

}
}

?>