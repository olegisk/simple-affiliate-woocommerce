<?php
/** @var string $reference  */
/** @var float $discount_rate  */
/** @var float $commission_rate */
/** @var string $currency  */
/** @var float $balance */
?>
<h2><?php _e( 'Affiliate', 'simple-affiliate' ); ?></h2>
<table class="form-table">
    <tr>
        <th>
            <label for="affiliate_reference">
				<?php _e( 'Affiliate Reference (Code)', 'simple-affiliate' ); ?>
            </label></th>
        <td>
            <input type="text" name="affiliate_reference" id="affiliate_reference"
                   value="<?php echo esc_attr( $reference ); ?>" class="regular-text"/>
        </td>
    </tr>
    <tr>
        <th>
            <label for="affiliate_discount_rate">
				<?php _e( 'Discount Rate', 'simple-affiliate' ); ?>
            </label></th>
        <td>
            <input type="text" name="affiliate_discount_rate" id="affiliate_discount_rate"
                   value="<?php echo esc_attr( $discount_rate ); ?>" class="regular-text"/>
        </td>
    </tr>
    <tr>
        <th>
            <label for="affiliate_commission_rate">
				<?php _e( 'Commission Rate', 'simple-affiliate' ); ?>
            </label></th>
        <td>
            <input type="text" name="affiliate_commission_rate" id="affiliate_commission_rate"
                   value="<?php echo esc_attr( $commission_rate ); ?>" class="regular-text"/>
        </td>
    </tr>
    <tr>
        <th>
            <label for="affiliate_currency">
				<?php _e( 'Currency of Balance', 'simple-affiliate' ); ?>
            </label></th>
        <td>
            <input type="text" name="affiliate_currency" id="affiliate_currency"
                   value="<?php echo esc_attr( $currency ); ?>" class="regular-text"/>
        </td>
    </tr>
    <tr>
        <th>
            <label for="affiliate_balance">
				<?php _e( 'Balance', 'simple-affiliate' ); ?>
            </label></th>
        <td>
            <input type="text" name="affiliate_balance" id="affiliate_balance"
                   value="<?php echo esc_attr( $balance ); ?>" class="regular-text" readonly/>
        </td>
    </tr>
</table>
