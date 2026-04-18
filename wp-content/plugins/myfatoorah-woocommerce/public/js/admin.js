jQuery(document).ready(function () {
    jQuery('#woocommerce_myfatoorah_v2_designColor').wpColorPicker();
    jQuery('#woocommerce_myfatoorah_v2_themeColor').wpColorPicker();

    jQuery('#mf_reset_icon').on('click', function () {
        jQuery('#woocommerce_myfatoorah_v2_designColor').parent().parent().parent().children(':first-child').attr('style', 'background-color: #888484;');
        jQuery('#woocommerce_myfatoorah_v2_designColor').val('#888484');
        jQuery('#woocommerce_myfatoorah_v2_themeColor').parent().parent().parent().children(':first-child').attr('style', 'background-color: #40a7cf;');
        jQuery('#woocommerce_myfatoorah_v2_themeColor').val('#0293cc');
        jQuery('#woocommerce_myfatoorah_v2_designFont').val('sans-serif');
        jQuery('#woocommerce_myfatoorah_v2_designFontSize').val('12');
    });

    //shipping
    if (jQuery("#woocommerce_myfatoorah_shipping_exe_ship_countries").length) {
        jQuery('#woocommerce_myfatoorah_shipping_exe_ship_countries').selectWoo();
    }
});

