<?php
/** @var float $balance  */
/** @var float $to_use  */
/** @var float $max_allowed_balance */
/** @var string $currency  */
?>
<?php if ( $balance > 0 ): ?>
    <div id="affliate-balance">
        <h3 class="order_review_heading">
            <?php echo __( 'Affiliate Balance', 'simple-affiliate' ); ?>
        </h3>

		<?php if ( $to_use > 0 ): ?>
            <div class="balance-form">
                <p class="form-row form-row-widet">
                    <label for="apply_balance">
						<?php echo esc_html( sprintf(
						/* translators: 1: price 2: currency */                            __( 'You are applied %s %s affiliate credits now.', 'simple-affiliate' ),
                                round( $to_use, 2 ),
                                $currency
                        ) ); ?>
                    </label>
                    <input type="submit" class="button" name="remove_balance" id="remove_balance"
                           value="<?php esc_attr_e( 'Remove', 'simple-affiliate' ); ?>"/>
                </p>
                <div class="clear"></div>
            </div>

            <script type="application/javascript">
                jQuery(document).ready(function ($) {
                    $(document).on('click', '#remove_balance', function (e) {
                        e.preventDefault();

                        var form = $('form.checkout');
                        form.addClass('processing').block({
                            message: null,
                            overlayCSS: {
                                background: '#fff',
                                opacity: 0.6
                            }
                        });

                        $.ajax({
                            type: 'POST',
                            url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                            data: {
                                action: 'simple_aff_balance_remove',
                                nonce: '<?php echo wp_create_nonce( 'simple-affiliate' ); ?>'
                            },
                            success: function (response) {
                                form.removeClass('processing').unblock();
                                $(document.body).trigger('update_checkout');
                                window.location.reload();
                            },
                            dataType: 'html'
                        });
                    });
                });
            </script>
		<?php else: ?>
            <div class="balance-form">
                <p>
					<?php echo esc_html( sprintf(
					/* translators: 1: price 2: currency */                            __( 'You have %s %s affiliate credits now.', 'simple-affiliate' ),
                            round( $balance, 2 ),
                            $currency
                    ) ); ?>
                </p>

                <p class="form-row form-row-first">
                    <label for="aff_balance">
						<?php echo __( 'Enter the amount of credit to use for this purchase', 'simple-affiliate' ) ?>
                    </label>
                    <input name="aff_balance" class="input-text" id="aff_balance" type="number" step="0.01" min="0"
                           max="<?php echo esc_attr( round( $max_allowed_balance, 2 ) ) ?>"
                           value="<?php echo esc_attr( round( $max_allowed_balance, 2 ) ) ?>"/>
                </p>

                <p class="form-row form-row-last">
                    <label for="apply_balance">
                        &nbsp;
                    </label>
                    <input type="submit" class="button" name="apply_balance" id="apply_balance"
                           value="<?php esc_attr_e( 'Apply', 'simple-affiliate' ); ?>"/>
                </p>

                <div class="clear"></div>
            </div>

            <script type="application/javascript">
                jQuery(document).ready(function ($) {
                    $(document).on('click', '#apply_balance', function (e) {
                        e.preventDefault();

                        var form = $('form.checkout');
                        form.addClass('processing').block({
                            message: null,
                            overlayCSS: {
                                background: '#fff',
                                opacity: 0.6
                            }
                        });

                        $.ajax({
                            type: 'POST',
                            url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                            data: {
                                action: 'simple_aff_balance_apply',
                                amount: form.find('input[name="aff_balance"]').val(),
                                nonce: '<?php echo wp_create_nonce( 'simple-affiliate' ); ?>'
                            },
                            success: function (response) {
                                form.removeClass('processing').unblock();
                                $(document.body).trigger('update_checkout');
                                window.location.reload();
                            },
                            dataType: 'html'
                        });
                    });
                });
            </script>
		<?php endif; ?>
    </div>
<?php endif; ?>
