<div style="color:#b81c23" data-mfVersion="<?php echo MYFATOORAH_WOO_PLUGIN_VERSION; ?>">
    <b><?php echo $this->mfError; ?></b>
</div>
<script>
    jQuery(document).ready(function ($) {
        $(mfWooBtn).on('click', function (e) {
            if ($('#payment_method_myfatoorah_<?php echo $this->code; ?>').is(':checked')) {
                e.preventDefault(); // Disable "Place Order" button
                $('.woocommerce-notices-wrapper').last().html('<ul class="woocommerce-error"><li><?php echo $this->mfError; ?></li></ul>');
                $([document.documentElement, document.body]).animate({
                    scrollTop: $('.woocommerce-notices-wrapper').first().offset().top
                }, 2000);
            }
        });
    });
</script>