<?php

if (!class_exists('WC_Gateway_Myfatoorah')) {
    include_once(MYFATOORAH_WOO_PLUGIN_PATH . 'includes/payments/class-wc-gateway-myfatoorah.php');
}

/**
 * Myfatoorah_V2 Payment Gateway class.
 *
 * Extended by individual payment gateways to handle payments.
 *
 * @class       WC_Gateway_Myfatoorah_v2
 * @extends     WC_Payment_Gateway
 */
class WC_Gateway_Myfatoorah_v2 extends WC_Gateway_Myfatoorah {

    protected $code;
    protected $count       = 0;
    protected $totalAmount = 0;
    protected $myfatoorah;
    public $session;

    /**
     * Constructor
     */
    public function __construct() {
        $this->code = 'v2';

        //Translate the gateway code
        __('Cards', 'myfatoorah-woocommerce');

        /* translators: %s: version number */
        $this->method_description = sprintf(__('MyFatoorah Debit/Credit Card payment version %s.', 'myfatoorah-woocommerce'), MYFATOORAH_WOO_PLUGIN_VERSION);
        $this->method_title       = __('MyFatoorah - Cards', 'myfatoorah-woocommerce');

        parent::__construct();

        $this->has_fields = true;

        add_action('admin_enqueue_scripts', array($this, 'load_admin_css_js'));
        if ($this->enabled == 'yes' && $this->listOptions == 'multigateways') {
            if ($this->newDesign == 'yes' || WC_Blocks_Utils::has_block_in_page(wc_get_page_id('checkout'), 'woocommerce/checkout')) {
                add_action('wp_enqueue_scripts', array($this, 'load_css_js'));
            }
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Initialize Gateway Settings Form Fields.
     * 
     * @return void 
     */
    function init_form_fields() {
        $this->form_fields = include(dirname(__DIR__) . '/admin/' . $this->code . '.php');
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Process the payment and return the result.
     * 
     * @param int $orderId
     * @return array
     */
    public function process_payment($orderId) {
        $curlData = $this->getPayLoadData($orderId);

        $gatewayId = MyFatoorah::filterInputField('mfCardData', 'POST') ?? MyFatoorah::filterInputField('mfcarddata', 'POST') ?? 'myfatoorah';
        $sessionId = MyFatoorah::filterInputField('mfData', 'POST') ?? MyFatoorah::filterInputField('mfdata', 'POST');

        $mfObj = new MyFatoorahPayment($this->myFatoorahConfig);
        $data  = $mfObj->getInvoiceURL($curlData, $gatewayId, $orderId, $sessionId);

        $order = wc_get_order($orderId);
        $order->update_meta_data('InvoiceId', $data['invoiceId']);

        $note = '<b>MyFatoorah Payment:</b><br>';
        $note .= 'InvoiceId: ' . $data['invoiceId'] . '<br>';
        $order->add_order_note($note);

        $order->save();

        return array(
            'result'   => 'success',
            'redirect' => $data['invoiceURL'],
        );
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    function payment_fields_v2() {
        if (!empty($this->mfError)) {
            return include_once(MYFATOORAH_WOO_TEMPLATES_PATH . 'error.php');
        }

        if (isset($this->newDesign) && $this->newDesign == 'yes' && $this->listOptions === 'multigateways') {
            $userDefinedField = ($this->saveCard == 'yes' && get_current_user_id()) ? 'CK-' . get_current_user_id() : '';

            $myfatoorahPayment = new MyFatoorahPayment($this->myFatoorahConfig);
            $this->session     = $myfatoorahPayment->getEmbeddedSession($userDefinedField);

            $file = 'paymentFields.php';
        } else {
            $file = 'paymentFieldsV2.php';
        }

        $this->get_parent_payment_fields();
        include_once(MYFATOORAH_WOO_TEMPLATES_PATH . $file);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Return the gateway's title.
     *
     * @return string
     */
    public function get_title() {
        try {
            $this->setGateways();
        } catch (Exception $ex) {
            $this->mfError = $ex->getMessage();
        }

        if ($this->listOptions === 'multigateways' && $this->count == 1) {
            return ($this->lang == 'ar') ? $this->gateways['all'][0]->PaymentMethodAr : $this->gateways['all'][0]->PaymentMethodEn;
        } else {
            return apply_filters('woocommerce_gateway_title', $this->title, $this->id);
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Return the gateway's icon.
     *
     * @return string
     */
    public function get_icon() {
        if ($this->listOptions === 'multigateways' && $this->count == 1) {
            $icon = '<img src="' . $this->gateways['all'][0]->ImageUrl . '" alt="' . esc_attr($this->get_title()) . '" style="margin: 0px; width: 50px; height: 30px;"/>';
        } else {
            $icon = $this->icon ? '<img src="' . WC_HTTPS::force_https_url($this->icon) . '" alt="' . esc_attr($this->get_title()) . '" />' : '';
        }

        return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    protected function setGateways() {
        //to prevent multicalls
        if ($this->listOptions === 'myfatoorah' || count($this->gateways) != 0) {
            return;
        }


        if (isset($this->newDesign) && $this->newDesign == 'yes') {
            //to prevent calling getCheckoutGateways twice
            if (!is_ajax() || !isset($_SERVER['HTTP_REFERER']) || stripos($_SERVER['HTTP_REFERER'], get_permalink(wc_get_page_id('checkout'))) === false) {
                //if not it will be displayed in the pay for order page
                if (empty(MyFatoorah::filterInputField('pay_for_order'))) {
                    //to keep my fatoorah shown in the checkout payment list
                    //to prevent multicalls
                    $this->listOptions = 'myfatoorah';
                    return;
                }
            }

            $total = $this->get_order_total();

            $mfObj          = new MyFatoorahPaymentEmbedded($this->myFatoorahConfig);
            $this->gateways = $mfObj->getCheckoutGateways($total, get_woocommerce_currency(), ($this->registerApplePay == 'yes'));
        } else {
            $mfObj          = new MyFatoorahPayment($this->myFatoorahConfig);
            $this->gateways = $mfObj->getCachedCheckoutGateways();
        }

        $this->count = count($this->gateways['all']);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Don't enable this payment, if there is no API key
     * 
     * @param string $key
     * @param string $value
     * 
     * @return string
     */
    public function validate_enabled_field($key, $value) {

        //don't enable if mfConfig is worng
        $options = [
            'apiKey'      => $this->get_field_value('apiKey', $this->form_fields['apiKey']),
            'countryMode' => $this->get_field_value('countryMode', $this->form_fields['countryMode']),
            'testMode'    => $this->get_field_value('testMode', $this->form_fields['testMode']),
        ];

        if (!$this->isMfConfigDataValid($options)) {
            return 'no';
        }

        if (is_null($value)) {
            $this->disableShipping($this->code);
            return 'no';
        }

        return 'yes';
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Don't disable this invoiceItem, if shipping method is enabled
     * 
     * @param type $key
     * @param type $value
     * 
     * @return string
     */
    public function validate_invoiceItems_field($key, $value) {
        $active = is_null($value) ? 'no' : 'yes';

        $shippingOptions = get_option('woocommerce_myfatoorah_shipping_settings');
        if ($active == 'no' && isset($shippingOptions['enabled']) && $shippingOptions['enabled'] == 'yes') {
            WC_Admin_Settings::add_error(__('You can not disable invoice items option while MyFatoorah Shipping is enabled', 'myfatoorah-woocommerce'));
            $active = 'yes';
        }

        return $active;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Disable the embedded if the new design enabled
     * 
     * @param type $key
     * @param type $value
     * 
     * @return string
     */
    public function validate_newDesign_field($key, $value) {
        $active = is_null($value) ? 'no' : 'yes';

        if ($active == 'yes') {
            $embedOptions = get_option('woocommerce_myfatoorah_embedded_settings');

            $enableFieldValue = MyFatoorah::filterInputField($this->get_field_key('enabled'), 'POST'); //don't use get_field_value to avoid duplicate validation and error message
            $apiKey           = $this->get_field_value('apiKey', $this->form_fields['apiKey']); //don't disable if there is no API key
            $isEmbedEnabled   = isset($embedOptions['enabled']) && $embedOptions['enabled'] == 'yes';
            $listOptions      = $this->get_field_value('listOptions', $this->form_fields['listOptions']);

            if ($apiKey && $enableFieldValue && $isEmbedEnabled && $listOptions == 'multigateways') {
                $embedOptions['enabled'] = 'no';
                update_option('woocommerce_myfatoorah_embedded_settings', apply_filters('woocommerce_settings_api_sanitized_fields_' . 'myfatoorah_embedded', $embedOptions), 'yes');
                WC_Admin_Settings::add_error(__('MyFatoorah Embedded has been disabled.', 'myfatoorah-woocommerce'));
            }
        }

        return $active;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Keep register Apple Pay value
     * 
     * @param type $key
     * @param type $value
     * 
     * @return string
     */
    public function validate_registerApplePay_field($key, $value) {
        $active = is_null($value) ? 'no' : 'yes';
        //Also, check if plugin is enabled using newMfConfig, don't use get_field_value to avoid duplicate API request and validation errors
        if ($active == 'no' || !isset($this->newMfConfig)) {
            return $active;
        }

        $listOptions = $this->get_field_value('listOptions', $this->form_fields['listOptions']);
        $newDesign   = $this->get_field_value('newDesign', $this->form_fields['newDesign']);
        if ($listOptions == 'myfatoorah' || $newDesign == 'no') {
            WC_Admin_Settings::add_error(__('Please make sure to select New design and List all gateway option to enable Apple Pay Embedded.', 'myfatoorah-woocommerce'));
            return 'no';
        }

        try {
            $myfatoorahPayment = new MyFatoorahPayment($this->newMfConfig);

            $data = $myfatoorahPayment->registerApplePayDomain(get_site_url());
            //if ($data->Message == 'OK') {
            return 'yes';
            //}
            //$error = $data->Message;
        } catch (Exception $ex) {
            $error = $ex->getMessage();
        }

        WC_Admin_Settings::add_error(__('Error: ', 'myfatoorah-woocommerce') . $key . ': ' . $error);
        return 'no';
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Validate supplier code
     * 
     * @param type $key
     * @param type $value
     * 
     * @return string
     */
    public function validate_supplierCode_field($key, $value) {
        //Also, check if plugin is enabled using newMfConfig, don't use get_field_value to avoid duplicate API request and validation errors
        if (empty($value) || !isset($this->newMfConfig)) {
            return $value;
        }

        try {
            $myfatoorahSupplier = new MyFatoorahSupplier($this->newMfConfig);
            if ($myfatoorahSupplier->isSupplierApproved($value)) {
                return $value;
            }

            $error = __('Supplier code is not active in vendor account, please contact MyFatoorah team to activate it.', 'myfatoorah-woocommerce');
        } catch (Exception $ex) {
            $error = $ex->getMessage();
        }

        WC_Admin_Settings::add_error(__('Error: ', 'myfatoorah-woocommerce') . $key . ': ' . $error);
        return null;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Validate icon
     * 
     * @param type $key
     * @param type $value
     * 
     * @return string
     */
    public function validate_icon_field($key, $value) {

        $url = preg_replace('/^http:/i', 'https:', trim($value));
        if (is_callable('sanitize_url')) {
            return call_user_func('sanitize_url', $url);
        }

        return filter_var($url, FILTER_VALIDATE_URL);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function load_admin_css_js() {

        wp_enqueue_script('wp-color-picker', 'wp-admin/js/color-picker.min.js');
        wp_enqueue_script('myfatoorah-admin', MYFATOORAH_WOO_ASSETS_URL . '/js/admin.js', [], MYFATOORAH_WOO_PLUGIN_VERSION);
        wp_enqueue_style('myfatoorah-admin', MYFATOORAH_WOO_ASSETS_URL . '/css/admin.css', [], MYFATOORAH_WOO_PLUGIN_VERSION);
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

        if (parent::needs_setup()) {
            return true;
        }

        $embedOptions = get_option('woocommerce_myfatoorah_embedded_settings');

        $testEmbdedWithNewDesign = (isset($embedOptions['enabled']) && $embedOptions['enabled'] == 'yes') &&
                (isset($this->newDesign) && $this->newDesign == 'yes') &&
                (isset($this->listOptions) && $this->listOptions == 'multigateways');

        return ($testEmbdedWithNewDesign);
    }

    public function getGateways() {
        if (isset($this->listOptions) && $this->listOptions == 'myfatoorah') {
            return null;
        }
        $total = $this->get_order_total();

        $mfObj    = new MyFatoorahPaymentEmbedded($this->myFatoorahConfig);
        $gateways = $mfObj->getCheckoutGateways($total, get_woocommerce_currency(), ($this->registerApplePay == 'yes'));
        if (empty($gateways['all'])) {
            throw new Exception(__('There are no payment methods available on your account, please contact your account manager.', 'myfatoorah-woocommerce'));
        }

        return $gateways;
    }

    public function getSession() {
        $userDefinedField = ($this->saveCard == 'yes' && get_current_user_id()) ? 'CK-' . get_current_user_id() : '';

        $myfatoorahPayment = new MyFatoorahPayment($this->myFatoorahConfig);
        return $myfatoorahPayment->getEmbeddedSession($userDefinedField);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
}
