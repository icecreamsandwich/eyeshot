<div id="mf-sectionGP">
    <div id="mf-gp-element" style="height: 40px;"></div>
    <script>
        jQuery(document).ready(function ($) {
            var mfGpConfig = {
                countryCode: "<?php echo $this->session->CountryCode; ?>",
                sessionId: "<?php echo $this->session->SessionId; ?>",
                currencyCode: "<?php echo $this->gateways['gp']->GatewayData['GatewayCurrency']; ?>", // Here, add your Currency Code.
                amount: "<?php echo $this->gateways['gp']->GatewayData['GatewayTotalAmount']; ?>", // Add the invoice amount.
                cardViewId: "mf-gp-element",
                isProduction: <?php echo ($this->testMode === 'yes') ? 'false' : 'true' ?>,
                callback: mfPaymentCallback
            };

            myFatoorahGP.init(mfGpConfig);

            function mfPaymentCallback(response) {
                $(mfWooForm).append('<input type="hidden" id="mfData" name="mfData" value="' + response.sessionId + '">');
                $(mfWooForm).submit();
            }
        });
    </script>
</div>