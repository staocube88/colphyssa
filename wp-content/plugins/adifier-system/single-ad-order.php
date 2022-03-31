<?php
if( !is_user_logged_in() ){
	wp_redirect( home_url( '/' ) );
}
get_header();

the_post();

$user_id = get_current_user_id();
$author_id = get_the_author_meta( 'ID' );
if( $user_id == $author_id || current_user_can('administrator') ){
	$is_refunded			= get_post_meta( get_the_ID(), 'order_refunded', true);
	$company_data 			= adifier_get_option( 'invoice_data' );
	$buyer_information 		= Adifier_Order::get_buyer_information( get_the_ID(), $author_id );
	$order_details			= Adifier_Order::get_order_details( get_the_ID() );

	$tax = !empty( $order_details['tax'] ) ? 1 + $order_details['tax'] / 100 : '';
	?>
	<div class="container">
		<div class="flex-wrap invoice-header">
			<div class="invoice-left">
				<h1><?php esc_html_e( 'Invoice', 'adifier' ) ?> <?php echo !empty( $is_refunded ) ? esc_html__( ' - Refunded', 'adifier' ) : '' ?></h1>
			</div>
			<div class="invoice-right">
				<?php echo nl2br(stripslashes( $company_data )); ?>
			</div>
		</div>
		<div class="flex-wrap invoice-middle">
			<div class="flex-left">
				<p class="small-title"><?php esc_html_e( 'Bill To', 'adifier' ) ?></p>
				<?php Adifier_Order::display_buyer_information( $buyer_information ); ?>
			</div>
			<div class="flex-middle">
				<p class="small-title"><?php esc_html_e( 'Invoice Number', 'adifier' ) ?></p>
				<p><?php echo get_post_meta( get_the_ID(), 'order_number', true ) ?></p>
				<p class="small-title"><?php esc_html_e( 'Date Created', 'adifier' ) ?></p>
				<p><?php the_time( get_option( 'date_format' ) ) ?></p>
				<p class="small-title"><?php esc_html_e( 'Payment Type', 'adifier' ) ?></p>
				<p><?php 
				$payment_type = get_post_meta( get_the_ID(), 'order_payment_type', true );
				switch ( $payment_type ){
					case 'bank' : esc_html_e( 'bank', 'adifier' ); break;
					case 'offline' : esc_html_e( 'offline', 'adifier' ); break;
					default: echo $payment_type;
				}
				?></p>			
			</div>
			<div class="flex-right text-right">
				<p class="small-title"><?php esc_html_e( 'Order Total', 'adifier' ) ?></p>
				<h3><?php echo adifier_price_format( $order_details['price'] ) ?></h3>
			</div>
		</div>
		<?php Adifier_Order::order_detail_info( $order_details ) ?>
	</div>
	<?php
}
?>
</body>
</html>