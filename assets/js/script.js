jQuery(document).ready(function ($) {
    $.fn.setAffiliateHandler = function () {
        var t = $(this);

        t.on('click', 'a.show-referrer-form', function (ev) {
            ev.preventDefault();

            t.find('form.referrer-form').slideToggle();
        });

        t.on('submit', 'form.referrer-form', function (ev) {
            var form = $(this),
                data = {
                    action: 'simple_affiliate_apply',
                    referrer_token: form.find('input[name="referrer_code"]').val(),
                    nonce: SimpleAffiliatePlugin.nonce
                };

            ev.preventDefault();

            form.addClass('processing').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });

            $.ajax({
                type: 'POST',
                url: SimpleAffiliatePlugin.ajax_url,
                data: data,
                success: function (code) {
                    $('.woocommerce-error, .woocommerce-message').remove();
                    form.removeClass('processing').unblock();

                    if (code) {
                        form
                            .before(code)
                            .find('input[name="referrer_code"]').prop('disabled');
                        form.slideUp();

                        $(document.body).trigger('update_checkout');
                    }
                },
                dataType: 'html'
            });

            return false;
        });
    };

    $('.simple-affiliate-form').setAffiliateHandler();
});