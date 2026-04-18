<?php

if (!class_exists('WC_Gateway_Myfatoorah')) {
    include_once(MYFATOORAH_WOO_PLUGIN_PATH . 'includes/payments/class-wc-gateway-myfatoorah.php');
}

/**
 * Myfatoorah_Embedded Payment Gateway class.
 *
 * Extended by individual payment gateways to handle payments.
 *
 * @class       WC_Gateway_Myfatoorah_embedded
 * @extends     WC_Payment_Gateway
 */
class WC_Gateway_Myfatoorah_embedded extends WC_Gateway_Myfatoorah {

    protected $code;
    public $session;
    public $testEmbdedWithNewDesign;

    /**
     * Constructor
     */
    public function __construct() {
        $this->code = 'embedded';

        //Translate the gateway code
        __('Embedded', 'myfatoorah-woocommerce');

        /* translators: %s: version number */
//        $this->method_description = sprintf(__('MyFatoorah Embedded payment version %s.', 'myfatoorah-woocommerce'), MYFATOORAH_WOO_PLUGIN_VERSION);
        $this->method_description = '<font color=darkgoldenrod>' . __('Deprecated and it will be removed soon, use MyFatoorah Card New Design', 'myfatoorah-woocommerce') . '</font>';
        $this->method_title       = __('MyFatoorah - Embedded', 'myfatoorah-woocommerce');

        parent::__construct();

        $this->has_fields = true;

        if ($this->enabled == 'yes') {
            add_action('wp_enqueue_scripts', array($this, 'load_css_js'));
        }

        $v2Options = get_option('woocommerce_myfatoorah_v2_settings');

        $this->testEmbdedWithNewDesign = (isset($v2Options['enabled']) && $v2Options['enabled'] == 'yes') &&
                (isset($v2Options['newDesign']) && $v2Options['newDesign'] == 'yes') &&
                (isset($v2Options['listOptions']) && $v2Options['listOptions'] == 'multigateways');
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

        $sessionId = MyFatoorah::filterInputField('mfData', 'POST');

        $mfObj = new MyFatoorahPayment($this->myFatoorahConfig);
        $data  = $mfObj->getInvoiceURL($curlData, null, $orderId, $sessionId);

        $order = wc_get_order($orderId);
        $order->update_meta_data('InvoiceId', $data['invoiceId']);
        $order->save();

        return array(
            'result'   => 'success',
            'redirect' => $data['invoiceURL'],
        );
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    function payment_fields_embedded() {
        $userDefinedField = ($this->saveCard == 'yes' && get_current_user_id()) ? 'CK-' . get_current_user_id() : '';

        $myfatoorahPayment = new MyFatoorahPayment($this->myFatoorahConfig);
        $this->session     = $myfatoorahPayment->getEmbeddedSession($userDefinedField);
        $this->gateways    = $myfatoorahPayment->getCachedCheckoutGateways();

        $this->get_parent_payment_fields();
        include_once(MYFATOORAH_WOO_TEMPLATES_PATH . 'paymentFieldsEmbedded.php');
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Return the gateway's title.
     *
     * @return string
     */
    public function get_title() {
        return apply_filters('woocommerce_gateway_title', $this->title, $this->id);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Return the gateway's icon.
     *
     * @return string
     */
    public function get_icon() {
        $icon = $this->icon ? '<img src="' . WC_HTTPS::force_https_url($this->icon) . '" alt="' . esc_attr($this->get_title()) . '" />' : '';
        return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Don't enable this payment, if there is no API key in "MyFatoorah - Cards" payment settings or newDesign is enabled
     * 
     * @param string $key
     * @param string $value
     * 
     * @return string
     */
    public function validate_enabled_field($key, $value) {

        if (is_null($value)) {
            $this->disableShipping($this->code);
            return 'no';
        }

        //don't enable if newDesign is enabled 
        if ($this->testEmbdedWithNewDesign) {
            WC_Admin_Settings::add_error(__('You should disable the new design option in the "MyFatoorah - Cards" payment Settings first, to enable this payment method', 'myfatoorah-woocommerce'));
            $this->disableShipping($this->code);
            return 'no';
        }

        //don't enable if mfConfig is worng
        $v2Options = get_option('woocommerce_myfatoorah_v2_settings');
        return $this->isMfConfigDataValid($v2Options) ? 'yes' : 'no';
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

        return ($this->testEmbdedWithNewDesign);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------    
}
