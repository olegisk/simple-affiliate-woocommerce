<?php
/** @var string $affiliate  */
/** @var bool $permanent_token  */
?>
<div class="simple-affiliate-form woocommerce">
    <?php
    $info_message = apply_filters( 'simple_affiliate_set_referrer_message', __( 'Did anyone suggest our site to you?', 'simple-affiliate' ) . ' <a href="#" class="show-referrer-form">' . __( 'Click here to enter his/her affiliate code', 'simple-affiliate' ) . '</a>' );
    wc_print_notice( $info_message, 'notice' );
    ?>

    <form class="referrer-form" method="post" style="display:none">

        <p class="form-row form-row-first">
            <input type="text" name="referrer_code" class="input-text" placeholder="<?php esc_attr_e( 'Affiliate code', 'simple-affiliate' ); ?>" id="coupon_code" value="<?php echo esc_attr( $affiliate )?>" <?php echo ( $permanent_token && $affiliate ) ? 'readonly="readonly"' : '' ?> />
        </p>

        <p class="form-row form-row-last">
            <input type="submit" class="button" name="set_referrer" value="<?php esc_attr_e( 'Set Affiliate', 'simple-affiliate' ); ?>" <?php echo ( $permanent_token && $affiliate ) ? 'disabled="disabled"' : '' ?> />
        </p>

        <div class="clear"></div>

    </form>
</div>