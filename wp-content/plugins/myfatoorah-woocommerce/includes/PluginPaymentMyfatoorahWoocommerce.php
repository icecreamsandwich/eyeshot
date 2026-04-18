<?php

class PluginPaymentMyfatoorahWoocommerce {

    //-----------------------------------------------------------------------------------------------------------------------------

    private $code;
    private $plugin;

    /**
     * Constructor
     */
    public function __construct($code, $plugin = MYFATOORAH_WOO_PLUGIN) {

        $this->code   = $code;
        $this->plugin = $plugin;

        //filters
        add_filter('woocommerce_payment_gateways', array($this, 'register'), 0);
        add_filter('myfatoorah_woocommerce_payment_gateways', [$this, 'myfatoorah_woocommerce_payment_gateways']);

        add_filter('plugin_action_links_' . $this->plugin, array($this, 'plugin_action_links'));
        add_filter('wc_get_price_decimals', array($this, 'wc_get_price_decimals'), 99);
        add_action('woocommerce_api_myfatoorah_process', array($this, 'initLoader'));
        add_action('woocommerce_api_myfatoorah_complete', array($this, 'getPaymentStatus'));
    }

    //-----------------------------------------------------------------------------------------------------------------------------
    public function wc_get_price_decimals($decimals) {
        $shippingOptions = get_option('woocommerce_myfatoorah_shipping_settings');
        if (!isset($shippingOptions['enabled']) || $shippingOptions['enabled'] == 'no' || wc_get_page_id('checkout') <= 0) {
            return $decimals;
        }

        $chosen_methods = filter_input(INPUT_POST, 'shipping_method', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if (isset($chosen_methods[0])) {
            if ($chosen_methods[0] == 'myfatoorah_shipping:1' || $chosen_methods[0] == 'myfatoorah_shipping:2') {
                return 3;
            }
        } else {
            //select diff country with no other shipping methods
            $payment_method = MyFatoorah::filterInputField('payment_method', 'POST');
            if ($payment_method && str_contains($payment_method, 'myfatoorah_')) {
                return 3;
            }
        }
        return $decimals;
    }

    //-----------------------------------------------------------------------------------------------------------------------------
    public function myfatoorah_woocommerce_payment_gateways($gateways) {

        $gateways[$this->code] = __(ucwords($this->code), dirname($this->plugin));
        return $gateways;
    }

    //-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Register the gateway to WooCommerce
     */
    public function register($gateways) {

        $path = WP_PLUGIN_DIR . '/' . dirname($this->plugin);
        include_once("$path/includes/payments/class-wc-gateway-myfatoorah-$this->code.php");

        $gateways[] = 'WC_Gateway_Myfatoorah_' . $this->code;
        return $gateways;
    }

    //-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Show action links on the plugin screen.
     * Action link will redirect to the payment settings page
     * http://wordpress-5.4.2.com/wp-admin/admin.php?page=wc-settings&tab=checkout&section=myfatoorah_$code
     *
     * @param mixed $links Plugin Action links.
     *
     * @return array
     */
    public function plugin_action_links($links) {

        $gateways = apply_filters('myfatoorah_woocommerce_payment_gateways', []);
        if (!isset($gateways[$this->code])) {
            return $links;
        }

        $newlink = ['myfatoorah_' . $this->code => '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=wc_gateway_myfatoorah_' . $this->code) . '">' . $gateways[$this->code] . '</a>'];
        return array_merge($links, $newlink);
    }

    //-----------------------------------------------------------------------------------------------------------------------------
    private function getTxtOrderNotFound() {
        return __('The Order is not found. Please, contact the store admin.', 'myfatoorah-woocommerce');
    }

    public function getPaymentStatus() {

        $orderId = base64_decode(MyFatoorah::filterInputField('oid'));
        $order   = wc_get_order($orderId);
        if (!$order) {
            wp_die($this->getTxtOrderNotFound());
        }

        $paymentMethod = $order->get_payment_method();

        //get Payment Id
        $KeyType = 'PaymentId';
        $key     = preg_replace('/[^0-9]/', '', (string) MyFatoorah::filterInputField('paymentId')); //To avoid other strings for app as example
        if (!$key) {
            wp_die($this->getTxtOrderNotFound());
        }

        $this->validateCallback($orderId, $key, $paymentMethod);

        //get MyFatoorah object
        $calss   = 'WC_Gateway_' . ucfirst($paymentMethod);
        $gateway = new $calss;

        try {
            $error = $gateway->checkStatus($key, $KeyType, $order);
        } catch (Exception $ex) {
            $error = $ex->getMessage();
        }
        if ($error) {
            $this->redirectToFailURL($gateway, $order, $error, MyFatoorah::filterInputField('pay_for_order'));
            exit();
        }

        $this->redirectToSuccessURL($gateway, $order, $orderId);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function validateCallback($orderId, $key, $paymentMethod) {
        if (!$orderId) {
            wp_die($this->getTxtOrderNotFound());
        }

        if (!$key) {
            wp_die($this->getTxtOrderNotFound());
        }

        if (!str_contains($paymentMethod, 'myfatoorah_')) {
            wp_die($this->getTxtOrderNotFound());
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function redirectToFailURL($gateway, $order, $error, $isPayForOrderPage) {
        if ($gateway->fail_url) {
            wp_redirect($gateway->fail_url . '?error=' . urlencode($error));
        } else {
            $trError = __($error, 'myfatoorah-woocommerce');
            wc_add_notice($trError, 'error');
            if ($isPayForOrderPage == 'true') {
                wp_redirect($order->get_checkout_payment_url());
            } else if (WC_Blocks_Utils::has_block_in_page(wc_get_page_id('checkout'), 'woocommerce/checkout')) {
                wp_redirect(wc_get_cart_url());
            } else {
                wp_redirect(wc_get_checkout_url());
            }
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function redirectToSuccessURL($gateway, $order, $orderId) {
        if ($gateway->success_url) {
            wp_redirect($gateway->success_url . '/' . $orderId . '/?key=' . $order->get_order_key());
        } else {
            //When "thankyou" order-received page is reached …
            wp_redirect($order->get_checkout_order_received_url());
        }
        exit();
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function initLoader() {
        $orderId   = MyFatoorah::filterInputField('oid');
        $paymentId = preg_replace('/[^0-9]/', '', (string) MyFatoorah::filterInputField('paymentId'));

        if (!$orderId || !$paymentId) {
            wp_die($this->getTxtOrderNotFound());
        }

        $args = [
            'wc-api'    => 'myfatoorah_complete',
            'oid'       => $orderId,
            'paymentId' => $paymentId,
        ];

        if (MyFatoorah::filterInputField('pay_for_order') == 'true') {
            $args['pay_for_order'] = 'true';
        }

        include_once(MYFATOORAH_WOO_TEMPLATES_PATH . 'loader.php');
    }

    //-----------------------------------------------------------------------------------------------------------------------------
}
