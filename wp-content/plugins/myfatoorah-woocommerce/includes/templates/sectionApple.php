<div id="mf-sectionAP">
    <div id="mf-ap-element" style="height: 40px;"></div>
    <script>
        jQuery(document).ready(function ($) {
            var mfApConfig = {
                countryCode: "<?php echo $this->session->CountryCode; ?>",
                sessionId: "<?php echo $this->session->SessionId; ?>",
                currencyCode: "<?php echo $this->gateways['ap']->GatewayData['GatewayCurrency']; ?>", // Here, add your Currency Code.
                amount: "<?php echo $this->gateways['ap']->PaymentTotalAmount ?>", // Add the invoice amount.
                cardViewId: "mf-ap-element",
                callback: mfPaymentCallback
            };

            myFatoorahAP.init(mfApConfig);

            function mfPaymentCallback(response) {
                $(mfWooForm).append('<input type="hidden" id="mfData" name="mfData" value="' + response.sessionId + '">');
                $(mfWooForm).submit();
            }
        });
    </script>
</div>