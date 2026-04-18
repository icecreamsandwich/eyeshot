<?php
$height    = ($this->saveCard == 'yes' && get_current_user_id()) ? 160 : 130;
$direction = ($this->lang == 'ar') ? 'rtl' : '';

$cardHolder = __('Name On Card', 'myfatoorah-woocommerce');
$cardNumber = __('Number', 'myfatoorah-woocommerce');
$cardDate   = __('MM / YY', 'myfatoorah-woocommerce');
$cardCVV    = __('CVV', 'myfatoorah-woocommerce');

$hideCardIcons = ($this->cardIcons === 'yes') ? 'true' : 'false';
?>
<div id="mf-form-element" style="width:99%; max-width:800px; padding: 0rem 0.2rem"></div>
<script>
    jQuery(document).ready(function ($) {
        var mfConfig = {
            countryCode: "<?php echo $this->session->CountryCode; ?>",
            sessionId: "<?php echo $this->session->SessionId; ?>",
            cardViewId: "mf-form-element",

            // The following style is optional.
            style: {
                hideCardIcons: <?php echo $hideCardIcons; ?>,
                cardHeight: <?php echo $height; ?>,
                direction: "<?php echo $direction; ?>",
                input: {
                    color: "<?php echo $this->designColor; ?>",
                    fontSize: "<?php echo $this->designFontSize; ?>px",
                    fontFamily: "<?php echo $this->designFont; ?>",
                    inputHeight: "32px",
                    inputMargin: "-1px",
                    borderColor: "<?php echo $this->designColor; ?>",
                    borderWidth: "1px",
                    borderRadius: "0px",
                    boxShadow: "",
                    placeHolder: {
                        holderName: "<?php echo $cardHolder; ?>", // translation
                        cardNumber: "<?php echo $cardNumber; ?>",
                        expiryDate: "<?php echo $cardDate; ?>",
                        securityCode: "<?php echo $cardCVV; ?>"
                    }
                },
                error: {
                    borderColor: "red",
                    borderRadius: "8px",
                    boxShadow: "0px"
                },
                text: {
                    saveCard: "<?php echo __('Save card number for future payments', 'myfatoorah-woocommerce'); ?>",
                    addCard: "<?php echo __('Use another card', 'myfatoorah-woocommerce'); ?>",
                    deleteAlert: {
                        tilte: "<?php echo __('Delete Card', 'myfatoorah-woocommerce'); ?>",
                        message: "<?php echo __('Are you sure you want to remove this card?', 'myfatoorah-woocommerce'); ?>",
                        confirm: "<?php echo __('Yes', 'myfatoorah-woocommerce'); ?>",
                        cancel: "<?php echo __('No', 'myfatoorah-woocommerce'); ?>"
                    }
                }
            }
        };

        myFatoorah.init(mfConfig);
        window.addEventListener("message", myFatoorah.recievedMessage);

        $(mfWooBtn).on('click', function (e) {
            if ($('#payment_method_myfatoorah_<?php echo $this->code; ?>').is(':checked')) {
                MFPayNow(e);
            }
        });

        $('.mf-pay-now-btn').on('click', function (e) {
            if ($('#payment_method_myfatoorah_<?php echo $this->code; ?>').is(':checked')) {
                MFPayNow(e);
            }
        });

        function MFPayNow(e) {

            e.preventDefault(); // Disable "Place Order" button

            $(mfWooForm).block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });

            myFatoorah.submit("<?php echo get_woocommerce_currency(); ?>").then(
                    function (response) {
                        // On success
                        $(mfWooForm).unblock(); //important to stop the block on the form

                        $(mfWooForm).append('<input type="hidden" id="mfData" name="mfData" value="' + response.sessionId + '">');
                        $(mfWooForm).submit();

                    },
                    function (error) {
                        // In case of errors
                        $(mfWooForm).unblock();

                        $('.woocommerce-notices-wrapper').last().html('<ul class="woocommerce-error"><li>' + error + '</li></ul>');
                        $([document.documentElement, document.body]).animate({
                            scrollTop: $('.woocommerce-notices-wrapper').first().offset().top
                        }, 2000);
                        $(mfWooForm).find('.input-text, select, input:checkbox').trigger('validate').trigger('blur');
                    }
            );
        }
    });
</script>