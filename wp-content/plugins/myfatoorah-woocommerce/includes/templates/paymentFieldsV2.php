<?php
//------------------------------------------------------------------- myfatoorah
if ($this->listOptions === 'myfatoorah') {
    return;
}

//------------------------------------------------------------------- count == 0
if ($this->count == 0) {
    ?>
    <script>
        jQuery('.payment_method_myfatoorah_v2').remove();
    </script>
    <?php
    return;
}

//------------------------------------------------------------------- count == 1
$embedOptions      = get_option('woocommerce_myfatoorah_embedded_settings');
$isEmbeddedEnabled = (isset($embedOptions['enabled']) && $embedOptions['enabled'] == 'yes');

if ($this->count == 1) {
    $gateway = $this->gateways['all'][0];
    if ($isEmbeddedEnabled && $gateway->IsEmbeddedSupported && $gateway->PaymentMethodCode != 'gp' && $gateway->PaymentMethodCode != 'ap') {
        ?>
        <script>
            jQuery('.payment_method_myfatoorah_v2').remove();
        </script>
        <?php
    } else {
        ?>
        <input type="hidden" name="mfCardData" value="<?php echo $gateway->PaymentMethodId; ?>"/>
        <?php if ($gateway->PaymentMethodCode == 'ap') { ?>
            <script>
                if (!window.ApplePaySession) {
                    jQuery('.payment_method_myfatoorah_v2').remove();
                }
            </script>
            <?php
        }
    }
    return;
}

//-------------------------------------------------------------------- count > 1
foreach ($this->gateways['all'] as $gateway) {
    if ($isEmbeddedEnabled && $gateway->IsEmbeddedSupported && $gateway->PaymentMethodCode != 'gp' && $gateway->PaymentMethodCode != 'ap') {
        continue;
    }

    $label   = ($this->lang == 'ar') ? $gateway->PaymentMethodAr : $gateway->PaymentMethodEn;
    $radioId = 'mf-radio-' . $gateway->PaymentMethodId;
    ?>
    <span class="mf-div mf-div-<?php echo $gateway->PaymentMethodCode; ?>" style="margin: 20px; display: inline-flex;">
        <input type="radio" class="mf-radio" id="<?php echo $radioId; ?>" name="mfCardData" value="<?php echo $gateway->PaymentMethodId; ?>" style="margin: 5px; vertical-align: top;"/>
        <label class="mf-label" for="<?php echo $radioId; ?>">
            <?php echo $label; ?>&nbsp;
            <img class="mf-img" src="<?php echo $gateway->ImageUrl; ?>" alt="<?php echo $label; ?>" style="margin: 0px; width: 50px; height: 30px;"/>
        </label>
    </span>
    <?php
}
?>
<script>
    if (!window.ApplePaySession) {
        jQuery('.mf-div-ap').remove();
    }

    //after removing the ap, is there any cards left?
    if (jQuery('.mf-radio').length === 0) {
        jQuery('.payment_method_myfatoorah_v2').remove();
    }

    jQuery('.mf-radio:first').attr('checked', true);
    if (jQuery('.mf-radio').length === 1) {
        jQuery('.payment_method_myfatoorah_v2').find('label').html(jQuery('.mf-label').html());
        jQuery('.mf-div').replaceWith('<input type="hidden" name="mfCardData" value="' + jQuery('.mf-radio').val() + '"/>');
    }
</script>
