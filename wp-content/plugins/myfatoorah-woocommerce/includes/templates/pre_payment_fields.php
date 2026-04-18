<!-- MyFatoorah version <?php echo MYFATOORAH_WOO_PLUGIN_VERSION; ?> -->
<input type="hidden" disabled data-mfVersion="<?php echo MYFATOORAH_WOO_PLUGIN_VERSION; ?>"/>
<script>
    var mfWooForm = '#order_review';
    var mfWooBtn = '#place_order';
    if (jQuery('form.checkout').length) {
        mfWooForm = 'form.checkout';
        mfWooBtn = 'button[type="submit"][name="woocommerce_checkout_place_order"]';
    }
</script>