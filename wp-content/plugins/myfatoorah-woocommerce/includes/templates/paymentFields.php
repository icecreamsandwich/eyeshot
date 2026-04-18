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
if ($this->count == 1 && count($this->gateways['form']) == 0) {
    $mfGateway = $this->gateways['all'][0];
    ?>
    <input type="hidden" name="mfCardData" value="<?php echo $mfGateway->PaymentMethodId; ?>"/>

    <?php
    if ($mfGateway->PaymentMethodCode == 'gp') {
        include_once('sectionGoogle.php');
    }

    //if Apple Pay registered
    if (!empty($this->gateways['ap'])) {
        include_once('sectionApple.php');
    }

    return;
}

//-------------------------------------------------------------------- count > 1
$isSectionCard = !empty($this->gateways['cards']);
$isSectionForm = !empty($this->gateways['form']);
$isSectionGP   = !empty($this->gateways['gp']);
$isSectionAP   = !empty($this->gateways['ap']);

$styleFontFamily = "font-family: $this->designFont;";

$fontSize      = ($this->designFontSize + 2) . 'px';
$styleFontSize = "font-size: $fontSize;";

$styleDesignColor = "color: $this->designColor;";

$txtOr = __('Or ', 'myfatoorah-woocommerce');
?>

<div class="mf-payment-methods-container" style="<?php echo "$styleFontFamily $styleFontSize $styleDesignColor"; ?>">
    <div class="mf-grey-text" style="<?php echo "$styleFontFamily $styleFontSize"; ?>">
        <?php echo __('How would you like to pay?', 'myfatoorah-woocommerce'); ?>
    </div>
    <?php if ($isSectionAP || $isSectionGP) {
        ?>
        <div id="mf-sectionButtons" style="margin-top: 14px;">
            <?php
            if ($isSectionAP) {
                include_once('sectionApple.php');
            }
            if ($isSectionGP) {
                include_once('sectionGoogle.php');
            }
            ?>
        </div>
        <?php
    }
    if ($isSectionCard) {
        ?>
        <div id="mf-sectionCard">
            <div class="mf-divider card-divider">
                <span class="mf-divider-span">
                    <span id="mf-or-cardsDivider">
                        <?php
                        if ($isSectionAP || $isSectionGP) {
                            echo $txtOr;
                        }
                        ?>
                    </span>
                    <?php echo __('Pay With', 'myfatoorah-woocommerce'); ?>
                </span>
            </div>
            <?php include_once('sectionCards.php'); ?>
        </div>
        <?php
    }
    if ($isSectionForm) {
        ?>
        <div class="mf-divider">
            <span class="mf-divider-span">
                <span id="mf-or-formDivider">
                    <?php
                    if ($isSectionAP || $isSectionGP || $isSectionCard) {
                        echo $txtOr;
                    }
                    ?>
                </span>
                <?php echo __('Insert Card Details', 'myfatoorah-woocommerce'); ?>
            </span>
        </div>
        <?php
        include_once('sectionForm.php');
        ?>
        <button class="mf-pay-now-btn" type="button" style="background-color:<?php echo $this->themeColor; ?>;
                border: none; border-radius: 8px;
                padding: 7px 3px;">
            <span class="mf-pay-now-span" style='font-size:<?php echo $this->designFontSize; ?>px; font-family:<?php echo $this->designFont; ?>;'>
                <?php echo __('Pay Now', 'myfatoorah-woocommerce'); ?>
            </span>
        </button>
        <?php
    }
    ?>
</div>