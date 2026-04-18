<?php

if (!defined('ABSPATH')) {
    exit;
}

if (class_exists('WC_Shipping_Myfatoorah')) {
    return;
}

/**
 * WC_Shipping_Myfatoorah class.
 *
 * handle shipping.
 *
 * @extends     WC_Shipping_Method
 */
class WC_Shipping_Myfatoorah extends WC_Shipping_Method {

//-----------------------------------------------------------------------------------------------------------------------------
    public $id               = 'myfatoorah_shipping';
    public $lang;
    public $pluginlog;
    public $enabled, $title, $countryMode      = 'KWT', $testMode, $apiKey, $debug, $shipping, $exe_ship_countries;
    public $mfCountries      = [];
    public $myFatoorahConfig = [];

    /**
     * Constructor for your shipping class
     *
     * @access public
     * @return void
     */
    public function __construct() {
        $this->id = 'myfatoorah_shipping';

        /* translators: %s: version number */
        $this->method_description = sprintf(__('Shipping via MyFatoorah using DHL/Aramex Shipping Methods version %s.', 'myfatoorah-woocommerce'), MYFATOORAH_WOO_PLUGIN_VERSION);
        $this->method_title       = __('MyFatoorah Shipping', 'myfatoorah-woocommerce');

        $this->pluginlog = WC_LOG_DIR . $this->id . '.log';

        //to stop taxes rate
        $this->tax_status = false;

        //Get setting values
        $this->init_settings();

        //enabled, title, countryMode, testMode, apiKey, debug, shipping, exe_ship_countries
        foreach ($this->settings as $key => $val) {
            $this->$key = $val;
        }


        $v2Options = get_option('woocommerce_myfatoorah_v2_settings');
        if ($v2Options) {
            $this->apiKey      = isset($v2Options['apiKey']) ? $v2Options['apiKey'] : '';
            $this->countryMode = isset($v2Options['countryMode']) ? $v2Options['countryMode'] : 'KWT';
            $this->testMode    = isset($v2Options['testMode']) ? $v2Options['testMode'] : 'no';
            $this->debug       = isset($v2Options['debug']) ? $v2Options['debug'] : 'yes';
        }

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
        }

        $this->myFatoorahConfig = [
            'apiKey'      => $this->apiKey,
            'countryCode' => $this->countryMode,
            'isTest'      => ($this->testMode === 'yes') ? true : false,
            'loggerObj'   => ($this->debug === 'yes') ? $this->pluginlog : false
        ];

        //Create plugin admin fields
        $this->init_form_fields();

        //save admin setting action
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Define Settings Form Fields
     * @return void 
     */
    function init_form_fields() {
        $this->form_fields = include(MYFATOORAH_WOO_PLUGIN_PATH . 'includes/admin/shipping.php');
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * 
     * @param type $countryCode
     * @return type
     * @throws Exception
     */
    public function getCities($countryCode, $searchValue = '') {
        $exe_ship_countries = $this->get_option('exe_ship_countries');

        if (!empty($exe_ship_countries) && (false !== array_search($countryCode, $exe_ship_countries))) {
            return array();
        }
        if (!isset($this->enabled) || $this->enabled === 'no') {
            return array();
        }

        if (empty($this->shipping) || empty($this->apiKey)) {
            throw new Exception(__('Kindly, review your MyFatoorah admin configuration due to a wrong entry to get MyFatoorah shipping cities.', 'myfatoorah-woocommerce'));
        }

        $cities = [];
        foreach ($this->shipping as $value) {
            $mfShipObj      = new MyFatoorahShipping($this->myFatoorahConfig);
            $shippingCities = $mfShipObj->getShippingCities($value, $countryCode, $searchValue);
            $cities         = array_merge($cities, $shippingCities);
        }

        if (empty($cities)) {
            return $cities;
        }

        return array_combine($cities, $cities);
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
     *
     * @access public
     * @param mixed $package
     * @return void
     */
    public function calculate_shipping($package = array()) {
        if ($this->enabled == 'no' || empty($this->shipping)) {
            return [];
        }

        $exe_ship_countries = $this->get_option('exe_ship_countries');
        if (!empty($exe_ship_countries) && (false !== array_search($package['destination']['country'], $exe_ship_countries))) {
            return [];
        }

        if (empty($package['destination']['city'])) {
            return [];
        }

        MyFatoorah::$loggerObj = $this->pluginlog;

        try {
            $weightRate      = MyFatoorah::getWeightRate(get_option('woocommerce_weight_unit'));
            $dimensionRate   = MyFatoorah::getDimensionRate(get_option('woocommerce_dimension_unit'));
            wc_clear_notices();
            $invoiceItemsArr = array();
            foreach ($package['contents'] as $item) {
                $product = $item['variation_id'] > 0 ? wc_get_product_object('variation', $item['variation_id']) : wc_get_product($item['product_id']);
                if (!$product->get_weight() || !$product->get_width() || !$product->get_height() || !$product->get_length()) {
                    $err = __('Please make sure products have dimensions and weight as well to get right MyFatoorah Shipping rates.', 'myfatoorah-woocommerce');

                    if ($this->debug === 'yes') {
                        MyFatoorah::log($err);
                    }

                    wc_add_notice($err, 'notice');
                    return [];
                }

                $invoiceItemsArr[] = array(
                    'ProductName' => strip_tags($product->get_title()),
                    "Description" => ($product->get_description()) ?: $product->get_title(),
                    'weight'      => (float) $product->get_weight() * $weightRate,
                    'Width'       => (float) $product->get_width() * $dimensionRate,
                    'Height'      => (float) $product->get_height() * $dimensionRate,
                    'Depth'       => (float) $product->get_length() * $dimensionRate,
                    'Quantity'    => $item['quantity'],
                    'UnitPrice'   => $product->get_price(),
                );
            }

            $wooCurrency    = get_woocommerce_currency();
            $myfatoorahList = new MyFatoorahList($this->myFatoorahConfig);
            $currencyRate   = $myfatoorahList->getCurrencyRate($wooCurrency);

            $mfObj = new MyFatoorahShipping($this->myFatoorahConfig);
            foreach ($this->shipping as $sh_method) {

                $shippingData = array(
                    'ShippingMethod' => $sh_method,
                    'Items'          => $invoiceItemsArr,
                    'CountryCode'    => $package['destination']['country'],
                    'CityName'       => $package['destination']['city'],
                    'PostalCode'     => $package['destination']['postcode'],
                );

                $data = $mfObj->calculateShippingCharge($shippingData);

                $methodName = $data->Fees == 0 ? __('MyFatoorah Free Shipping', 'myfatoorah-woocommerce') :
                        (($sh_method == 2) ? __($this->title, 'myfatoorah-woocommerce') . ' ' . __('Aramex') : __($this->title, 'myfatoorah-woocommerce') . ' ' . __('DHL'));

                $shipAmount = floor((int) ($data->Fees * $currencyRate * 1000)) / 1000;

                $rate = array(
                    'id'             => $this->id . ':' . $sh_method,
                    'label'          => trim($methodName),
                    'cost'           => $shipAmount,
                    'meta_data'      => array(),
                    'price_decimals' => 3,
                );
                $this->add_rate($rate);
            }
        } catch (Exception $ex) {
            $err = __('MyFatoorah shipping can not be calculated due to: ', 'myfatoorah-woocommerce') . $ex->getMessage();
            if ($this->debug === 'yes') {
                MyFatoorah::log($err);
            }
            wc_add_notice($err, 'notice');

            return [];
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Don't enable MyFatoorah Plugin, if there is no API key and one Shipping Method 
     * @param type $key
     * @param type $value
     * @return string
     */
    public function validate_enabled_field($key, $value) {
        if (is_null($value)) {
            return 'no';
        }

        $v2Options = get_option('woocommerce_myfatoorah_v2_settings');

        //check for API key
        if (empty($v2Options['apiKey'])) {
            WC_Admin_Settings::add_error(__('You should add the API key in the "MyFatoorah - Cards" payment Settings first, to enable MyFatoorah Shipping', 'myfatoorah-woocommerce'));
            return 'no';
        }

        //check for invoice Items
        if (empty($v2Options['invoiceItems']) || $v2Options['invoiceItems'] == 'no') {
            WC_Admin_Settings::add_error(__('You should enable invoice items option in the "MyFatoorah - Cards" payment Settings first, to enable MyFatoorah Shipping', 'myfatoorah-woocommerce'));
            return 'no';
        }

        //check for API shipping methods
        $shipping = $this->get_field_value('shipping', $this->form_fields['shipping']);
        if (empty($shipping)) {
            WC_Admin_Settings::add_error(__('You should select at least one Shipping Method, to enable MyFatoorah Shipping', 'myfatoorah-woocommerce'));
            return 'no';
        }

        //check for enabled gateways
        $gateways = apply_filters('myfatoorah_woocommerce_payment_gateways', []);
        foreach ($gateways as $key => $title) {
            $codeOptions = get_option('woocommerce_myfatoorah_' . $key . '_settings');
            $isCoEnabled = (isset($codeOptions['enabled']) && $codeOptions['enabled'] == 'yes');

            if ($isCoEnabled) {
                return 'yes';
            }
        }

        WC_Admin_Settings::add_error(__('You should enable at least one of MyFatoorah payment methods, to enable MyFatoorah Shipping', 'myfatoorah-woocommerce'));
        return 'no';
    }

//-----------------------------------------------------------------------------------------------------------------------------
}
