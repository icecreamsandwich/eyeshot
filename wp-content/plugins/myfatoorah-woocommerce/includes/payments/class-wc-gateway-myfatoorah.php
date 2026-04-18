<?php

/**
 * WC_Gateway_Myfatoorah class.
 *
 * handle payments.
 *
 * @extends     WC_Payment_Gateway
 */
require_once MYFATOORAH_WOO_PLUGIN_PATH . 'includes/libraries/MyfatoorahLoader.php';
require_once MYFATOORAH_WOO_PLUGIN_PATH . 'includes/libraries/MyfatoorahLibrary.php';

class WC_Gateway_Myfatoorah extends WC_Payment_Gateway {

//-----------------------------------------------------------------------------------------------------------------------------

    public $id;
    public $lang;
    public $pluginlog;
    public $mfCountries      = [];
    public $gateways         = [];
    public $myFatoorahConfig = [];
    protected $newMfConfig   = [];
    protected $mfError;
    public $enabled, $title, $description, $icon;
    public $countryMode, $testMode, $apiKey;
    public $webhookSecretKey, $debug, $saveCard, $supplierCode, $invoiceItems;
    public $orderStatus, $success_url, $fail_url;
    public $listOptions, $newDesign, $registerApplePay;
    public $designFont, $designFontSize, $designColor, $themeColor, $cardIcons;
    //Settings Titles
    public $frontend, $theme, $resetTheme, $configuration, $options;

    /**
     * Constructor for your payment class
     *
     * @access public
     * @return void
     */
    public function __construct() {
        $this->id = 'myfatoorah_' . $this->code;

        $this->pluginlog = WC_LOG_DIR . $this->id . '.log';

        //this will appeare in the setting details page. For more customize page you override function admin_options()
        $this->supports = array(
            'products',
            'refunds',
        );

        //Get setting values
        $this->init_settings();

        //for example: enabled, title, description, countryMode, testMode, apiKey, listOptions, orderStatus, success_url, fail_url, debug, icon, 
//        foreach ($this->settings as $key => $val) {
////            $this->$key = $val;
//            var_dump($key, $val, '<br>');
//        }

        $this->init_myfatoorah_options();

        //lookup
        $this->lang = substr(determine_locale(), 0, 2);
        $countries  = MyFatoorah::getMFCountries();
        if (is_array($countries)) {
            $langIndex = ($this->lang == 'ar') ? 'Ar' : 'En';
            $nameIndex = 'countryName' . $langIndex;
            foreach ($countries as $key => $obj) {
                $this->mfCountries[$key] = $obj[$nameIndex];
            }
        } else {
            $countries = [];
            //$this->mfCountries = []; //to do
        }

        $this->myFatoorahConfig = [
            'apiKey'      => $this->apiKey,
            'countryCode' => $this->countryMode,
            'isTest'      => ($this->testMode === 'yes'),
            'loggerObj'   => ($this->debug === 'yes') ? $this->pluginlog : false
        ];

        //Create plugin admin fields
        $this->init_form_fields();

        //save admin setting action
        add_action('pre_update_option_woocommerce_myfatoorah_' . $this->id . '_settings', array($this, 'pre_update_option'));
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * initiate MyFatoorah from V2 Options
     * @return void 
     */
    function init_myfatoorah_options() {

        //if (empty($this->apiKey)) {
        $options = get_option('woocommerce_' . $this->id . '_settings');

        /* payment info */
        $this->enabled     = !empty($options['enabled']) ? trim($options['enabled']) : '';
        $this->title       = !empty($options['title']) ? trim($options['title']) : '';
        $this->description = !empty($options['description']) ? trim($options['description']) : '';
        $this->icon        = !empty($options['icon']) ? trim($options['icon']) : '';

        /* myfatoorah info */
        $v2Options = get_option('woocommerce_myfatoorah_v2_settings');

        $this->apiKey      = !empty($v2Options['apiKey']) ? trim($v2Options['apiKey']) : '';
        $this->countryMode = !empty($v2Options['countryMode']) ? $v2Options['countryMode'] : 'KWT';
        $this->testMode    = !empty($v2Options['testMode']) ? $v2Options['testMode'] : 'no';

        /* mf features info */
        $this->debug            = !empty($v2Options['debug']) ? $v2Options['debug'] : 'yes';
        $this->webhookSecretKey = !empty($v2Options['webhookSecretKey']) ? trim($v2Options['webhookSecretKey']) : '';
        $this->saveCard         = !empty($v2Options['saveCard']) ? $v2Options['saveCard'] : 'no';
        $this->invoiceItems     = !empty($v2Options['invoiceItems']) ? $v2Options['invoiceItems'] : 'yes';
        $this->supplierCode     = !empty($v2Options['supplierCode']) ? $v2Options['supplierCode'] : 0;

        /* woo features info */
        $this->orderStatus = !empty($v2Options['orderStatus']) ? $v2Options['orderStatus'] : 'processing';
        $this->success_url = !empty($v2Options['success_url']) ? trim($v2Options['success_url']) : '';
        $this->fail_url    = !empty($v2Options['fail_url']) ? trim($v2Options['fail_url']) : '';

        /* v2 info */
        $this->listOptions      = !empty($v2Options['listOptions']) ? $v2Options['listOptions'] : 'multigateways';
        $this->newDesign        = !empty($v2Options['newDesign']) ? $v2Options['newDesign'] : 'yes';
        $this->registerApplePay = !empty($v2Options['registerApplePay']) ? $v2Options['registerApplePay'] : 'no';

        /* theme info */
        $this->designColor    = !empty($v2Options['designColor']) ? $v2Options['designColor'] : '#888484';
        $this->themeColor     = !empty($v2Options['themeColor']) ? $v2Options['themeColor'] : '#0293cc';
        $this->designFont     = !empty($v2Options['designFont']) ? $v2Options['designFont'] : 'sans-serif';
        $this->designFontSize = !empty($v2Options['designFontSize']) ? $v2Options['designFontSize'] : '12';
        $this->cardIcons      = !empty($v2Options['cardIcons']) ? $v2Options['cardIcons'] : 'no';
        //}
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Process a refund if supported
     *
     * @param  int $orderId
     * @param  float $displayAmount
     * @param  string $reason
     * @return  bool|wp_error True or false based on success, or a WP_Error object
     */
    public function process_refund($orderId, $displayAmount = null, $reason = '') {

        $msgTitle = __('MyFatoorah Refund: ');

        $order     = wc_get_order($orderId);
        if (!$paymentId = $order->get_meta('PaymentId', true)) {
            return new WP_Error('mfMakeRefund', $msgTitle . __('please, refund manually for this order', 'myfatoorah-woocommerce'));
        }

        $status = $order->get_status();
        if ($status != 'processing' && $status != 'completed') {
            return new WP_Error('mfMakeRefund', $msgTitle . __('system can\'t refund order with status ', 'myfatoorah-woocommerce') . $status);
        }

        $note = '<b>MyFatoorah Refund Details:</b><br>';
        try {
            $displayCurrency = $order->get_currency();

            //request refund
            $mfRObj = new MyFatoorahRefund($this->myFatoorahConfig);
            $data   = $mfRObj->refund($paymentId, $displayAmount, $displayCurrency, $reason, $orderId);

            //save RefundData
            $refundData = $order->get_meta('RefundData', true) ?: [];

            $data->DisplayAmount   = $displayAmount;
            $data->DisplayCurrency = $displayCurrency;

            $refundData[$data->RefundId] = $data;
            $order->update_meta_data('RefundData', $refundData);
            $order->save();

            //add the note
            if ($displayAmount < $order->get_total()) {
                $note .= '<font color="chocolate"><b>Refund request is partial</b></font><br>';
            }

            $note .= 'RefundStatus: <font color="darkgoldenrod">PENDING</font><br>';
            $note .= 'RefundId: ' . $data->RefundId . '<br>';
            $note .= 'RefundReference: ' . $data->RefundReference . '<br>';
            $note .= 'RefundInvoiceId: ' . $data->RefundInvoiceId . '<br>';

            $baseCurrency = $order->get_meta('InvoiceBaseCurrency', true) ?: '';
            $note         .= 'BaseAmount: ' . $data->Amount . ' ' . $baseCurrency . '<br>';
            $note         .= 'DisplayAmount: ' . $displayAmount . ' ' . $displayCurrency . '<br>';

            $note .= 'Comment: ' . $data->Comment . '<br>';

            $msg = __('please, wait until the refund request is confirmed.');
        } catch (Exception $exc) {
            $msg  = $exc->getMessage();
            $note .= $msg;
        }

        $order->add_order_note($note);
        return new WP_Error('error', $msgTitle . $msg);
    }

//-----------------------------------------------------------------------------------------------------------------------------
    function getPayLoadData($orderId) {
        $order = new WC_Order($orderId); //todo switch to wc_get_order

        $fName = $order->get_billing_first_name();
        if (!$fName) {
            $fName = $order->get_shipping_first_name();
        }

        $lname = $order->get_billing_last_name();
        if (!$lname) {
            $lname = $order->get_shipping_last_name();
        }

        //phone & email are not exist in shipping address!!
        $email1 = $order->get_billing_email();
        $email  = empty($email1) ? null : $email1;

        $phone    = $order->get_billing_phone();
        $phoneArr = MyFatoorah::getPhone($phone);

        $civilId = $order->get_meta('billing_cid', true);

        $userDefinedField = ($this->saveCard == 'yes' && get_current_user_id()) ? 'CK-' . get_current_user_id() : '';

        //get $expiryMinutes
        $expiryMinutes = class_exists('WC_Admin_Settings') ? get_option('woocommerce_hold_stock_minutes') : null;

        //callback url
        $args = [
            'wc-api' => 'myfatoorah_process',
            'oid'    => base64_encode($orderId),
        ];

        if (MyFatoorah::filterInputField('pay_for_order') == 'true') {
            $args['pay_for_order'] = 'true';
        }

        $sucess_url  = add_query_arg($args, home_url());
//        $sucess_url  = home_url() . 'wc-api/myfatoorah_process?oid=' . base64_encode($orderId);
//        if (MyFatoorah::filterInputField('pay_for_order') == 'true') {
//            $sucess_url = 'pay_for_order=true';
//        }
        //$sucess_url = $order->get_checkout_order_received_url();
        //$err_url    = $order->get_cancel_order_url_raw();
        //$err_url    = wc_get_checkout_url();
        //currency
        $currencyIso = $order->get_currency();
        //if the WPML is accivate (need better sol????????)
//        if ($currencyIso = 'CLOUDWAYS') {
//            $currencyIso = get_woocommerce_currency_symbol($currencyIso);
//        }

        $shipingMethod = $this->getShippingMethod($order);
//        $amount       = $order->get_total();
//        $invoiceItems = [['ItemName' => 'Total amount', 'Quantity' => 1, 'UnitPrice' => "$amount"]];

        $amount = 0;
        if ($this->invoiceItems == 'yes') {
            $invoiceItems = $this->getInvoiceItems($order, $amount, $shipingMethod);
        } else {
            $amount         = $order->get_total();
            $invoiceItems[] = [
                'ItemName'  => __('Total amount for order #', 'myfatoorah-woocommerce') . $orderId,
                'Quantity'  => 1,
                'UnitPrice' => "$amount"];
        }

        //$address = $order->get_shipping_address_1();
        //$city = $order->get_shipping_city();
        //$country = $order->get_shipping_country();

        $address = WC()->customer->get_shipping_address_1() . ' ' . WC()->customer->get_shipping_address_2();

        // custom fields
        /* if(empty($address)){
          $block = $order->get_meta('billing_block', true);
          $street = $order->get_meta('billing_street', true);
          $gada = $order->get_meta('billing_gada', true);
          $house = $order->get_meta('billing_house', true);
          $address =$block. ' , ' .$street . ' , '. $house. ' , '. $gada ;
          } */

        $customerAddress = array(
            'Block'               => 'string',
            'Street'              => 'string',
            'HouseBuildingNo'     => 'string',
            'Address'             => $address,
            'AddressInstructions' => 'string'
        );

        $shippingConsignee = array(
            'PersonName'   => "$fName $lname",
            'Mobile'       => $phoneArr[1],
            'EmailAddress' => $email,
            'LineAddress'  => $address,
            'CityName'     => WC()->customer->get_shipping_city(),
            'PostalCode'   => WC()->customer->get_shipping_postcode(),
            'CountryCode'  => WC()->customer->get_shipping_country()
        );

        //SourceInfo
        $v2Options = get_option('woocommerce_myfatoorah_v2_settings');

        $testEmbdedWithNewDesign = (isset($v2Options['enabled']) && $v2Options['enabled'] == 'yes') &&
                (isset($v2Options['newDesign']) && $v2Options['newDesign'] == 'yes') &&
                (isset($v2Options['listOptions']) && $v2Options['listOptions'] == 'multigateways');

        $design = ($testEmbdedWithNewDesign) ? ' - New Design' : null;

        return [
            'CustomerName'       => "$fName $lname",
            'InvoiceValue'       => "$amount",
            'DisplayCurrencyIso' => $currencyIso,
            'CustomerEmail'      => $email,
            'CallBackUrl'        => $sucess_url,
            'ErrorUrl'           => $sucess_url,
            'MobileCountryCode'  => $phoneArr[0],
            'CustomerMobile'     => $phoneArr[1],
            'Language'           => ($this->lang == 'ar') ? 'ar' : 'en',
            'CustomerReference'  => $orderId,
            'CustomerCivilId'    => $civilId,
            'UserDefinedField'   => $userDefinedField,
            'ExpiryMinutes'      => $expiryMinutes,
            'SourceInfo'         => 'WooCommerce ' . WC_VERSION . ' - ' . $this->id . ' ' . MYFATOORAH_WOO_PLUGIN_VERSION . $design,
            'CustomerAddress'    => $customerAddress,
            'ShippingConsignee'  => ($shipingMethod) ? $shippingConsignee : null,
            'ShippingMethod'     => $shipingMethod,
            'InvoiceItems'       => $invoiceItems,
            'Suppliers'          => $this->getSupplierInfo($amount),
            'WebhookUrl'         => empty($this->supplierCode) ? null : home_url() . '?wc-api=myfatoorah_webhook'
        ];
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * 
     * @param WC_Order $order
     * @param type $amount
     * @param type $shipingMethod
     * @return string
     */
    function getInvoiceItems($order, &$amount, $shipingMethod) {
        $weightRate    = MyFatoorah::getWeightRate(get_option('woocommerce_weight_unit'));
        $dimensionRate = MyFatoorah::getDimensionRate(get_option('woocommerce_dimension_unit'));

        $forceEnglishItemName = ($this->lang == 'ar' && $shipingMethod);

        $invoiceItemsArr = [];
        //Product items
        /** @var WC_Order_Item[] $items */
        $items           = $order->get_items();
        foreach ($items as $item) {
            $product = wc_get_product($item->get_product_id());

            $itemName = $item->get_name();
            if ($shipingMethod && $product->get_attribute('mf_shipping_english_name')) {
                $itemName = $product->get_attribute('mf_shipping_english_name');
            }
            $productFromItem = $item->get_product();

            //check if $productFromItem is not bool and It is a variation using is_type
            if (gettype($productFromItem) == 'object' && $productFromItem->is_type('variation')) {
                $variation_id = $item->get_variation_id();
                $product      = wc_get_product_object('variation', $variation_id);
            }

            $itemSubtotalPrice = $order->get_line_subtotal($item, false);
            if (is_nan($itemSubtotalPrice)) {
                /* translators: %s: the product name */
                $errMsg = sprintf(__('The "%s" Item has non number unit price.', 'myfatoorah-woocommerce'), $itemName);
                throw new Exception($errMsg);
            }

            $itemPrice = $itemSubtotalPrice / $item->get_quantity();
            $amount    += $itemSubtotalPrice;

            $invoiceItemsArr[] = [
                'ItemName'  => strip_tags($itemName),
                'Quantity'  => $item->get_quantity(),
                'UnitPrice' => "$itemPrice",
                'weight'    => ($shipingMethod) ? (float) ($product->get_weight()) * $weightRate : null,
                'Width'     => ($shipingMethod) ? (float) ($product->get_width()) * $dimensionRate : null,
                'Height'    => ($shipingMethod) ? (float) ($product->get_height()) * $dimensionRate : null,
                'Depth'     => ($shipingMethod) ? (float) ($product->get_length()) * $dimensionRate : null,
            ];
        }


        //------------------------------
        //Shippings
        $shipping = round($order->get_shipping_total(), wc_get_price_decimals());
        if ($shipping && $shipingMethod === null) {

            $rateLabel = $order->get_shipping_method();
            foreach (WC()->session->get('shipping_for_package_0')['rates'] as $method_id => $rate) {
                if (WC()->session->get('chosen_shipping_methods')[0] == $method_id) {
                    $rateLabel = $rate->label; // The shipping method label name
                    break;
                }
            }

            $itemName = $forceEnglishItemName ? $rateLabel : __($rateLabel, 'woocommerce');

            $amount            += $shipping;
            $invoiceItemsArr[] = ['ItemName' => $itemName, 'Quantity' => '1', 'UnitPrice' => "$shipping", 'Weight' => '0', 'Width' => '0', 'Height' => '0', 'Depth' => '0'];
        }


        //------------------------------
        //Discounds and Coupon
        $discount = round($order->get_discount_total(), wc_get_price_decimals());
        if ($discount) {
            $itemName = $forceEnglishItemName ? 'Discount' : __('Discount', 'woocommerce');

            $amount            -= $discount;
            $invoiceItemsArr[] = ['ItemName' => $itemName, 'Quantity' => '1', 'UnitPrice' => "-$discount", 'Weight' => '0', 'Width' => '0', 'Height' => '0', 'Depth' => '0'];
        }


        //------------------------------
        //Other fees
        foreach ($order->get_items('fee') as $item => $item_fee) {
            $total_fees = $item_fee->get_total();
            $itemName   = $forceEnglishItemName ? $item_fee->get_name() : __($item_fee->get_name(), 'woocommerce');

            $amount            += $total_fees;
            $invoiceItemsArr[] = ['ItemName' => $itemName, 'Quantity' => '1', 'UnitPrice' => "$total_fees", 'Weight' => '0', 'Width' => '0', 'Height' => '0', 'Depth' => '0'];
        }


        //------------------------------
        //for pw-woocommerce-gift-cards plugin
        foreach ($order->get_items('pw_gift_card') as $line) {
            $gifPrice = $line->get_amount();
            $itemName = $forceEnglishItemName ? 'Gift Card' : __('Gift Card', 'woocommerce');

            $amount            -= $gifPrice;
            $invoiceItemsArr[] = ['ItemName' => $itemName, 'Quantity' => '1', 'UnitPrice' => "-$gifPrice", 'Weight' => '0', 'Width' => '0', 'Height' => '0', 'Depth' => '0'];
        }


        //------------------------------
        //Tax
        $tax = $order->get_total_tax();
        if ($tax > 0) {
            //error_log(PHP_EOL . date('d.m.Y h:i:s') . ' - In Tax section' . $tax, 3, WC_LOG_DIR . 'myfatoorah_tax_section.log');
            $MFShipping = 0;
            if ($shipingMethod) {
                $cartTotals = WC()->cart->get_totals();
                $MFShipping = $cartTotals['shipping_total'];
            }

            $tax = round($order->get_total() - $amount - $MFShipping, wc_get_price_decimals()); // IMP MF Shipping 
            if ($tax) {
                $itemName = $forceEnglishItemName ? 'Taxes' : __('Taxes', 'woocommerce');

                $amount            += $tax;
                $invoiceItemsArr[] = ['ItemName' => $itemName, 'Quantity' => '1', 'UnitPrice' => "$tax", 'Weight' => '0', 'Width' => '0', 'Height' => '0', 'Depth' => '0'];
            }
        }

        //------------------------------
        //total
        $amount = round($amount, 3);
        return $invoiceItemsArr;
    }

//-----------------------------------------------------------------------------------------------------------------------------
    private function getShippingMethod($order) {

        $chosen_methods = WC()->session->get('chosen_shipping_methods');

        //sometimes, shipping is cached in session, use $order->get_shipping_method()
        if (isset($chosen_methods[0]) && $order->get_shipping_method()) {
            if ($chosen_methods[0] == 'myfatoorah_shipping:1') {
                return 1;
            } else if ($chosen_methods[0] == 'myfatoorah_shipping:2') {
                return 2;
            }
        }
        return null;
    }

    private function getSupplierInfo($amount) {
        if (empty($this->supplierCode)) {
            return null;
        }

        return [[
        'SupplierCode'  => $this->supplierCode,
        'ProposedShare' => null,
        'InvoiceShare'  => $amount
        ]];
    }

//-----------------------------------------------------------------------------------------------------------------------------

    public function updatePostMeta(&$order, $data) {

        $order->update_meta_data('InvoiceId', $data->InvoiceId);
        $order->update_meta_data('InvoiceReference', $data->InvoiceReference);
        $order->update_meta_data('InvoiceDisplayCurrencyValue', $data->InvoiceDisplayValue);
        $order->update_meta_data('InvoiceBaseValue', $data->InvoiceValue);

        //focusTransaction
        $order->update_meta_data('InvoiceBaseCurrency', $data->focusTransaction->Currency);
        $order->update_meta_data('PaymentGateway', $data->focusTransaction->PaymentGateway);
        $order->update_meta_data('PaymentId', $data->focusTransaction->PaymentId);
        $order->update_meta_data('ReferenceId', $data->focusTransaction->ReferenceId);
        $order->update_meta_data('TransactionId', $data->focusTransaction->TransactionId);
    }

//-----------------------------------------------------------------------------------------------------------------------------

    public function addOrderNote(&$order, $data, $source) {
        $note = "<b>MyFatoorah$source Payment Details:</b><br>";

        $note .= 'InvoiceStatus: ' . $data->InvoiceStatus . '<br>';
        if ($data->InvoiceStatus == 'Failed') {
            $note .= 'InvoiceError: ' . $data->InvoiceError . '<br>';
        }

        $note .= 'InvoiceId: ' . $data->InvoiceId . '<br>';
        $note .= 'InvoiceReference: ' . $data->InvoiceReference . '<br>';
        $note .= 'InvoiceDisplayValue: ' . $data->InvoiceDisplayValue . '<br>';
        $note .= 'InvoiceBaseValue: ' . $data->InvoiceValue . '<br>';

        //focusTransaction
        $note .= 'InvoiceBaseCurrency: ' . $data->focusTransaction->Currency . '<br>';
        $note .= 'PaymentGateway: ' . $data->focusTransaction->PaymentGateway . '<br>';
        $note .= 'PaymentId: ' . $data->focusTransaction->PaymentId . '<br>';
        $note .= 'ReferenceId: ' . $data->focusTransaction->ReferenceId . '<br>';
        $note .= 'TransactionId: ' . $data->focusTransaction->TransactionId . '<br>';

        $order->add_order_note($note);
    }

//-----------------------------------------------------------------------------------------------------------------------------
    public function updateOrderData(&$order, $status, $data, $source) {

        //update meta data
        $this->updatePostMeta($order, $data);
        $order->set_transaction_id($data->focusTransaction->PaymentId);

        //update status
        $order->update_status($status, "<b>MyFatoorah$source:</b><br/>", true);

        //add notes
        $this->addOrderNote($order, $data, $source);
    }

//-----------------------------------------------------------------------------------------------------------------------------
    public function checkStatus($keyId, $KeyType, $order, $source = '') {

        $orderId     = $order->get_id();
        $mfPayStatus = new MyFatoorahPaymentStatus($this->myFatoorahConfig);
        $data        = $mfPayStatus->getPaymentStatus($keyId, $KeyType, $orderId);

        if ($data->InvoiceStatus == 'Paid') {
            //pending, processing, on-hold, completed, cancelled, refunded, failed, or customed
            $status = $order->get_status();
            //go back if NOT pending, failed, on-hold
            if ($status != 'pending' && $status != 'failed' && $status != 'on-hold') {
                return '';
            }
            $this->updateOrderData($order, $this->orderStatus, $data, $source);
        } else if ($data->InvoiceStatus == 'Failed') {

            $this->updateOrderData($order, 'failed', $data, $source);
        } else if ($data->InvoiceStatus == 'Expired') {
            $noteTitle = "<b>MyFatoorah$source:</b><br/>";
            $order->update_status('cancelled', $noteTitle);
            $order->add_order_note($noteTitle . $data->InvoiceError);
        }
        //Calling the save() method is a relatively expensive operation, so you may wish to avoid calling it more times than necessary (for example, if you know it will be called later in the same flow, you may wish to avoid additional earlier calls when operating on the same object).
        $order->update_meta_data('myfatoorah_status', $data->InvoiceStatus);
        $order->save();

        return $data->InvoiceError;
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Processes and saves options.
     * If there is an error thrown, will continue to save and validate fields, but will leave the erroring field out.
     *
     * @return bool was anything saved?
     */
    public function process_admin_options() {
        if (file_exists(MyFatoorahPayment::$pmCachedFile)) {
            unlink(MyFatoorahPayment::$pmCachedFile);
        }
        parent::process_admin_options();
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Update a single option.
     *
     * @since 3.4.0
     * @param string $key Option key.
     * @param mixed  $value Value to set.
     * @return bool was anything saved?
     */
    public function update_option($key, $value = '') {
        if ($key == 'enabled' && $value == 'no') {
            $this->disableShipping($this->code);
        }
        parent::update_option($key, $value);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    function payment_fields() {
        include(MYFATOORAH_WOO_TEMPLATES_PATH . 'pre_payment_fields.php');

        try {
            if (!wc_checkout_is_https()) {
                throw new Exception(__('MyFatoorah forces SSL checkout Payment. Your checkout is not secure! Please, contact the site admin to enable SSL and ensure that the server has a valid SSL certificate.', 'myfatoorah-woocommerce'));
            }

            $this->{'payment_fields_' . $this->code}();
        } catch (Exception $ex) {
            $this->mfError = $ex->getMessage();
            include(MYFATOORAH_WOO_TEMPLATES_PATH . 'error.php');
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function get_parent_payment_fields() {
        parent::payment_fields();
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function disablePaymentsAndShipping() {

        $gateways = apply_filters('myfatoorah_woocommerce_payment_gateways', []);
        foreach ($gateways as $key => $value) {
            $options   = get_option('woocommerce_myfatoorah_' . $key . '_settings');
            $isEnabled = (isset($options['enabled']) && $options['enabled'] == 'yes');

            if ($isEnabled) {
                $options['enabled'] = 'no';
                update_option('woocommerce_myfatoorah_' . $key . '_settings', apply_filters('woocommerce_settings_api_sanitized_fields_myfatoorah_' . $key, $options), 'yes');

                /* translators: %s: Payment Code */
                WC_Admin_Settings::add_error(sprintf(__('MyFatoorah %s has been disabled.', 'myfatoorah-woocommerce'), $key));
            }
        }

        $shipOptions = get_option('woocommerce_myfatoorah_shipping_settings');
        $isShEnabled = (isset($shipOptions['enabled']) && $shipOptions['enabled'] == 'yes');
        if ($isShEnabled) {
            $shipOptions['enabled'] = 'no';
            update_option('woocommerce_myfatoorah_shipping_settings', apply_filters('woocommerce_settings_api_sanitized_fields_myfatoorah_shipping', $shipOptions), 'yes');
            WC_Admin_Settings::add_error(__('MyFatoorah Shipping has been disabled.', 'myfatoorah-woocommerce'));
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function disableShipping($code) {

        $shipOptions = get_option('woocommerce_myfatoorah_shipping_settings');
        $isShEnabled = (isset($shipOptions['enabled']) && $shipOptions['enabled'] == 'yes');
        if (!$isShEnabled) {
            return;
        }

        $gateways = apply_filters('myfatoorah_woocommerce_payment_gateways', []);
        unset($gateways[$code]);

        foreach ($gateways as $key => $value) {
            $codeOptions = get_option('woocommerce_myfatoorah_' . $key . '_settings');
            $isCoEnabled = (isset($codeOptions['enabled']) && $codeOptions['enabled'] == 'yes');

            if ($isCoEnabled) {
                return;
            }
        }

        $shipOptions['enabled'] = 'no';
        update_option('woocommerce_myfatoorah_shipping_settings', apply_filters('woocommerce_settings_api_sanitized_fields_myfatoorah_shipping', $shipOptions), 'yes');
        WC_Admin_Settings::add_error(__('MyFatoorah Shipping has been disabled.', 'myfatoorah-woocommerce'));
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function getMfDesWithIcon($desc, $icon = 'dashicons-info') {
        return '<font style="color:#0093c9;"><span class="dashicon dashicons ' . $icon . '"></span>' . $desc . '</font>';
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function getWebhookDesc() {
        $url  = get_site_url(null, '', 'https') . '/?wc-api=myfatoorah_webhook';
        $desc = __('Copy this link to your MyFatoorah Account. After that, Copy your Webhook Secret Key from MyFatoorah Account in the above field.', 'myfatoorah-woocommerce');

        return $this->getMfDesWithIcon($url . '<br>' . $desc);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function isMfConfigDataValid($options) {

        //don't validate if there is no API key
        if (empty($options['apiKey'])) {
            $this->disablePaymentsAndShipping();

            WC_Admin_Settings::add_error(__('You should add the API key in the "MyFatoorah - Cards" payment Settings first.', 'myfatoorah-woocommerce'));
            error_log(PHP_EOL . date('d.m.Y h:i:s') . ' - Empty API key', 3, $this->pluginlog);
            return false;
        }

        //don't validate if config is wrong
        try {
            $this->newMfConfig = [
                'apiKey'      => $options['apiKey'],
                'countryCode' => $options['countryMode'],
                'isTest'      => ($options['testMode'] === 'yes'),
                'loggerObj'   => $this->pluginlog
            ];

            $mfPayObj = new MyFatoorahPayment($this->newMfConfig);
            $mfPayObj->initiatePayment();

            return true;
        } catch (Exception $ex) {
            //Unset this due to the plugin is not enabeled
            $this->newMfConfig = null;
            $this->disablePaymentsAndShipping();

            WC_Admin_Settings::add_error(__($ex->getMessage(), 'myfatoorah-woocommerce'));
            error_log(PHP_EOL . date('d.m.Y h:i:s') . ' - Exception: isMfConfigDataValid - ' . $ex->getMessage(), 3, $this->pluginlog);
            return false;
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------    

    /**
     * Return whether or not this gateway still requires setup to function.
     *
     * When this gateway is toggled on via AJAX, if this returns true a
     * redirect will occur to the settings page instead.
     *
     * @return bool
     */
    public function needs_setup() {
        if (isset($this->enabled) && $this->enabled === 'yes') {
            return false;
        }

        $options = [
            'apiKey'      => $this->apiKey,
            'countryMode' => $this->countryMode,
            'testMode'    => $this->testMode,
        ];
        return !$this->isMfConfigDataValid($options);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function load_css_js() {

        $v2Options = get_option('woocommerce_myfatoorah_v2_settings');

        $isTest = isset($v2Options['testMode']) && $v2Options['testMode'] == 'yes';
        $vcCode = empty($v2Options['countryMode']) ? 'KWT' : $v2Options['countryMode'];

        $countries = MyFatoorah::getMFCountries();
        $domain    = ($isTest) ? $countries[$vcCode]['testPortal'] : $countries[$vcCode]['portal'];

        wp_enqueue_script('myfatoorah-cardview', "$domain/cardview/v2/session.js", [], MYFATOORAH_WOO_PLUGIN_VERSION, false);

        $isApRegisterd = (isset($v2Options['registerApplePay']) && $v2Options['registerApplePay'] == 'yes');
        if ($isApRegisterd) {
            wp_enqueue_script('myfatoorah-applepay', "$domain/applepay/v3/applepay.js", [], MYFATOORAH_WOO_PLUGIN_VERSION, false);
        }

        wp_enqueue_script('myfatoorah-googlepay', "$domain/googlepay/v1/googlepay.js", [], MYFATOORAH_WOO_PLUGIN_VERSION, false);

        wp_enqueue_style('myfatoorah-style', MYFATOORAH_WOO_ASSETS_URL . '/css/myfatoorah.css', [], MYFATOORAH_WOO_PLUGIN_VERSION);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
}
