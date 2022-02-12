<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="wrap">
    <h2 class="nav-tab-wrapper">
		<?php _e( 'Simple Affiliate Settings', 'simple_affiliate' ) ?>
    </h2>

    <form method="post" action="options.php">
        <?php settings_fields( 'simple_affiliate' ); ?>
        <?php do_settings_sections( 'simple_affiliate' ); ?>
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="default_discount_rate">
					    <?php _e( 'Default discount rate', 'simple_affiliate' ); ?>
                    </label>
                </th>
                <td class="forminp">
                    <fieldset>
                        <legend class="screen-reader-text">
                            <span><?php _e( 'Default discount rate', 'simple_affiliate' ); ?></span>
                        </legend>
                        <label for="default_discount_rate">
                            <input class="input-text regular-input"
                                   type="text"
                                   name="default_discount_rate"
                                   id="default_discount_rate"
                                   value="<?php echo esc_attr( get_option( 'default_discount_rate', 5 ) ); ?>" />
                        </label>
                        <br/>
                    </fieldset>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="default_commission_rate">
					    <?php _e( 'Default commission rate', 'simple_affiliate' ); ?>
                    </label>
                </th>
                <td class="forminp">
                    <fieldset>
                        <legend class="screen-reader-text">
                            <span><?php _e( 'Default commission rate', 'simple_affiliate' ); ?></span>
                        </legend>
                        <label for="default_commission_rate">
                            <input class="input-text regular-input"
                                   type="text"
                                   name="default_commission_rate"
                                   id="default_commission_rate"
                                   value="<?php echo esc_attr( get_option( 'default_commission_rate', 5 ) ); ?>" />
                        </label>
                        <br/>
                    </fieldset>
                </td>
            </tr>
            </tbody>
        </table>

	    <?php submit_button(); ?>
    </form>
</div>
