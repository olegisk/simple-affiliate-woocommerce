<?php
/** @var string $reference  */
/** @var float $balance  */
/** @var int $user_id */
/** @var string $currency  */
?>
<div class="affiliate-dashboard">
    <h2><?php _e( 'Info', 'simple-affiliate' ); ?></h2>
    <table class="shop_table shop_table_responsive my_account_orders">
        <tbody>
        <?php if ( ! empty( $reference ) ): ?>
        <tr>
            <td>
                <?php _e( 'Affiliate ID', 'simple-affiliate' ); ?>
            </td>
            <td>
                <?php echo esc_attr( $reference ); ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php _e( 'Affiliate link', 'simple-affiliate' ); ?>
            </td>
            <td>
                <?php
                echo esc_html( add_query_arg( array( 'affid' => $reference  ), site_url() ) );
                ?>
            </td>
        </tr>
        <?php endif; ?>
        <!-- <tr>
            <td>
                <?php _e( 'Earning - Total', 'simple-affiliate' ); ?>
            </td>
            <td>
                0
            </td>
        </tr>
        <tr>
            <td>
                <?php _e( 'Earning - Spent', 'simple-affiliate' ); ?>
            </td>
            <td>
                0
            </td>
        </tr> -->
        <tr>
            <td>
                <?php _e( 'Earning Available', 'simple-affiliate' ); ?>
            </td>
            <td>
                <?php echo esc_attr( round( $balance, 2 ) . ' ' . $currency ); ?>
            </td>
        </tr>
        </tbody>
    </table>

    <h2><?php _e( 'Settings', 'simple-affiliate' ); ?></h2>
    <form class="woocommerce-AffiliateDashboardForm aff-dashboard" action="" method="post">
        <fieldset>
            <!-- <legend><?php _e( 'Settings', 'simple-affiliate' ); ?></legend> -->
            <p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
                <label for="currency">
                    <?php _e( 'Preferred currency', 'simple-affiliate' ); ?> <span class="required">*</span>
                </label>
                <?php
                $currencies = array( 'USD', 'EUR' );
                ?>
                <select name="currency" id="currency" class="woocommerce-select">
                    <?php foreach ($currencies as $item): ?>
                        <option <?php echo $currency === $item ? 'selected' : ' '  ?> value="<?php echo $item; ?>">
                            <?php echo esc_html( $item ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            <div class="clear"></div>

            <p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
                <label for="reference">
                    <?php _e( 'Affiliate ID', 'simple-affiliate' ); ?> <span class="required">*</span>
                </label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="reference" id="reference" value="<?php echo esc_attr( $reference ); ?>" />
            <p>
            <div class="clear"></div>

            <p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
                <?php wp_nonce_field( 'save_affiliate_details' ); ?>
                <input type="submit" class="button" name="save_affiliate_details" id="save_affiliate_details" value="<?php esc_attr_e( 'Save', 'woocommerce' ); ?>" />
                <input type="hidden" name="action" value="save_affiliate_details" />
            <p>
            <div class="clear"></div>
        </fieldset>
    </form>
    <h2><?php _e( 'Earning history', 'simple-affiliate' ); ?></h2>
    <?php echo do_shortcode( '[affiliate_history user_id="' . esc_html( (int) $user_id ) . '"]' ); ?>
</div>