<table style="background-color:#f2f2f2" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
    <tbody>
        <tr>
           <td style="padding:40px 20px" align="center" valign="top">
               <table style="width:800px" border="0" cellpadding="0" cellspacing="0">
                    <tbody>
                        <tr>
                            <td style="padding-bottom:30px" align="center" valign="top">
                                <table style="background-color:#ffffff;border-collapse:separate!important;border-radius:4px" border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tbody>
                                        <tr>
                                           <td style="padding-top:40px;padding-right:40px;padding-bottom:0;padding-left:40px" align="center" valign="top">
                                               <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 40px;">
                                                    <tbody>
                                                        <tr>
                                                            <td align="left" valign="middle" style="width: 50%">
                                                                <h1 style="color:#202020!important;font-family:Helvetica,Arial,sans-serif;font-size:26px;font-weight:bold;letter-spacing:-1px;line-height:115%;margin:0;padding:0;"><?php esc_html_e( 'Invoice', 'adifier' ) ?></h1>
                                                            </td>                                                            
                                                            <td align="right" valign="middle" style="width: 50%">
                                                                <?php include( get_theme_file_path( 'includes/emails/email-logo.php' ) ); ?>
                                                            </td>                                                            
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 40px;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="width:50%;padding:10px 20px;color:#000;font-weight:bold;line-height:100%;background:#f8f8f8;font-size:14px;" valign="middle">
                                                                <?php esc_html_e( 'Bill To', 'adifier' ) ?>
                                                            </td>
                                                            <td style="width:50%;padding:10px 20px;color:#000;font-weight:bold;line-height:100%;background:#f8f8f8;font-size:14px;" valign="middle">
                                                                <?php esc_html_e( 'Bill From', 'adifier' ) ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding:10px 20px;width:50%;" valign="top">
                                                                <?php Adifier_Order::display_buyer_information( $buyer_information ); ?>
                                                            </td>
                                                            <td style="padding:10px 20px;width:50%;"  valign="top">
                                                                <?php
                                                                if( !empty( $invoice_data ) ){
                                                                    echo nl2br(stripslashes( $invoice_data )); 
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-right:40px;padding-bottom:40px;padding-left:40px" align="center" valign="middle">
                                                <table border="0" cellpadding="0" cellspacing="0" align="<?php echo adifier_get_option( 'direction' ) == 'ltr' ? 'left' : 'right' ?>" width="100%" style="font-family:Helvetica,Arial,sans-serif;font-size:13px;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="padding:10px 20px;color:#000;font-weight:bold;line-height:100%;background:#f8f8f8;font-size:14px;" valign="middle" colspan="2">
                                                                <?php esc_html_e( 'Order Details', 'adifier' ) ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td width="50%" style="padding:10px 20px; border-bottom: 1px solid #f8f8f8">
                                                                <?php esc_html_e( 'Number', 'adifier' ) ?>
                                                            </td>
                                                            <td width="50%" style="padding:10px 20px; border-bottom: 1px solid #f8f8f8">
                                                                <?php echo get_post_meta( $order_id, 'order_number', true ); ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td width="50%" style="padding:10px 20px; border-bottom: 1px solid #f8f8f8">
                                                                <?php esc_html_e( 'Date', 'adifier' ) ?>
                                                            </td>
                                                            <td width="50%" style="padding:10px 20px; border-bottom: 1px solid #f8f8f8">
                                                                <?php echo get_the_time( get_option( 'date_format' ), $order_id ) ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td width="50%" style="padding:10px 20px; border-bottom: 1px solid #f8f8f8">
                                                                <?php esc_html_e( 'Payment method', 'adifier' ) ?>
                                                            </td>
                                                            <td width="50%" style="padding:10px 20px; border-bottom: 1px solid #f8f8f8">
                                                                <?php 
                                                                $payment_type = get_post_meta( $order_id, 'order_payment_type', true );
                                                                switch ( $payment_type ){
                                                                    case 'bank' : esc_html_e( 'bank', 'adifier' ); break;
                                                                    case 'offline' : esc_html_e( 'offline', 'adifier' ); break;
                                                                    default: echo $payment_type;
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-right:40px;padding-bottom:40px;padding-left:40px" align="center" valign="middle">
                                                <table border="0" cellpadding="0" cellspacing="0" align="<?php echo adifier_get_option( 'direction' ) == 'ltr' ? 'left' : 'right' ?>" width="100%" style="font-family:Helvetica,Arial,sans-serif;font-size:13px;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="padding:10px 20px;color:#000;font-weight:bold;line-height:100%;background:#f8f8f8;font-size:14px;" valign="middle" width="<?php echo !empty( $tax ) ? '40%' : '70%' ?>"  <?php echo !empty( $tax ) ? '' : 'colspan="3"' ?>>
                                                                <?php esc_html_e( 'Description', 'adifier' ) ?>
                                                            </td>
                                                            <td style="padding:10px 20px;color:#000;font-weight:bold;line-height:100%;background:#f8f8f8;font-size:14px;" valign="middle" width="15%" align="right">
                                                                <?php esc_html_e( 'Quantity', 'adifier' ) ?>
                                                            </td>
                                                            <?php
                                                            if( !empty( $tax ) ){
                                                                ?>
                                                                <td style="padding:10px 20px;color:#000;font-weight:bold;line-height:100%;background:#f8f8f8;font-size:14px;" valign="middle" width="15%" align="right">
                                                                    <?php esc_html_e( 'Price', 'adifier' ) ?>
                                                                </td>                                                                    
                                                                <?php                                                                    
                                                                ?>
                                                                <td style="padding:10px 20px;color:#000;font-weight:bold;line-height:100%;background:#f8f8f8;font-size:14px;" valign="middle" width="15%" align="right">
                                                                    <?php echo $order['tax_name'] ?>
                                                                </td>                                                                    
                                                                <?php                                                                    
                                                            }
                                                            ?>
                                                            <td style="padding:10px 20px;color:#000;font-weight:bold;line-height:100%;background:#f8f8f8;font-size:14px;" valign="middle" width="15%" align="right">
                                                                <?php esc_html_e( 'Amount', 'adifier' ) ?>
                                                            </td>
                                                        </tr>
                                                        <?php echo $order_html ?>
                                                        <?php if( !empty( $tax ) ): ?>
                                                            <tr>
                                                                <td style="padding:10px 20px;color:#000;font-weight:bold;line-height:100%;background:#f8f8f8;font-size:14px;" valign="middle" width="70%" colspan="<?php echo !empty( $order['tax_name'] ) ? '4' : '3' ?>">
                                                                    <?php esc_html_e( 'Total ', 'adifier' ); echo $order['tax_name']; ?>
                                                                </td>
                                                                <td style="padding:10px 20px;color:#000;font-weight:bold;line-height:100%;background:#f8f8f8;font-size:14px;" valign="middle" width="15%" align="right">
                                                                    <?php echo adifier_price_format( $order['price'] - $order['price'] / $tax ); ?>
                                                                </td>
                                                            </tr>
                                                        <?php endif; ?>                                       
                                                        <tr>
                                                            <td style="padding:10px 20px;color:#000;font-weight:bold;line-height:100%;background:#f8f8f8;font-size:14px;" valign="middle" width="70%" colspan="<?php echo !empty( $order['tax_name'] ) ? '4' : '3' ?>">
                                                                <?php esc_html_e( 'Total Amount', 'adifier' ) ?>
                                                            </td>
                                                            <td style="padding:10px 20px;color:#000;font-weight:bold;line-height:100%;background:#f8f8f8;font-size:14px;" valign="middle" width="15%" align="right">
                                                                <?php echo adifier_price_format( $order['price'] ) ?>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>