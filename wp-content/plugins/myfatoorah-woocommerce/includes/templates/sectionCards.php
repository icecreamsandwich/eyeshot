<?php
$designFontSize = $this->designFontSize . 'px';
$styleCard      = "font-family: $this->designFont; font-size: $designFontSize; color: $this->themeColor;";

$langIndex = ($this->lang == 'ar') ? 'ar' : 'en';

foreach ($this->gateways['cards'] as $mfCard) {
    ?>
    <?php $mfPaymentTitle = ($this->lang == 'ar') ? $mfCard->PaymentMethodAr : $mfCard->PaymentMethodEn; ?>
    <div class="mf-card-container mf-div-<?php echo $mfCard->PaymentMethodCode; ?>" style="width: unset;" data-mfCardId="<?php echo $mfCard->PaymentMethodId; ?>" title="<?php echo ($mfPaymentTitle); ?>">
        <div class="mf-row-container">
            <img class="mf-payment-logo" src="<?php echo $mfCard->ImageUrl; ?>" alt="<?php echo $mfPaymentTitle; ?>">
            <span class="mf-card-title" style="<?php echo $styleCard; ?>"><?php echo $mfPaymentTitle; ?></span>
        </div>
        <span class="mf-price-tag" style='text-align: end; <?php echo $styleCard; ?>'>
            <?php echo $mfCard->GatewayData['GatewayTotalAmount']; ?>&nbsp;<?php echo $mfCard->GatewayData['GatewayTransCurrency'][$langIndex]; ?>
        </span>
    </div>
<?php }
?>
<script>
    jQuery(document).ready(function ($) {
        //card button clicked
        $("[data-mfCardId]").on('click', function (e) {
            e.preventDefault();

            if ($('#mfData').length){
                    $('#mfData').remove();
            }
            $(mfWooForm).append('<input type="hidden" name="mfCardData" value="' + $(this).attr('data-mfCardId') + '">');
            $(mfWooForm).submit();
        });
    });
</script>