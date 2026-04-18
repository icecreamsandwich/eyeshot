<?php

namespace MyFatoorah\WooCommerce\Payments\Blocks;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use WC_Gateway_Myfatoorah_v2;
use Exception;

defined('ABSPATH') || exit;

include_once(MYFATOORAH_WOO_PLUGIN_PATH . 'includes/payments/class-wc-gateway-myfatoorah-v2.php');

/**
 * MyFatoorah V2 (myfatoorah_v2) payment method integration
 *
 */
final class MyFatoorahV2 extends AbstractPaymentMethodType {

    /**
     * Payment method name/id/slug (matches id in WC_Gateway_Myfatoorah_v2 in core).
     *
     * @var string
     */
    protected $name = 'myfatoorah_v2';

    /**
     * Initializes the payment method type.
     */
    public function initialize() {
        $this->settings = get_option('woocommerce_myfatoorah_v2_settings', []);
    }

    /**
     * Returns if this payment method should be active. If false, the scripts will not be enqueued.
     *
     * @return boolean
     */
    public function is_active() {
        return filter_var($this->get_setting('enabled', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Returns an array of scripts/handles to be registered for this payment method.
     *
     * @return array
     */
    public function get_payment_method_script_handles() {
        wp_register_script(
                'wc-myfatoorah-blocks-integration',
                plugins_url('build/index.js', MYFATOORAH_WOO_PLUGIN),
                [
                    'wc-blocks-registry',
                    'wc-settings',
                    'wp-element',
                    'wp-html-entities',
                    'wp-i18n',
                ],
                MYFATOORAH_WOO_PLUGIN_VERSION,
                true //the script is printed in the footer
        );

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations('wc-myfatoorah-blocks-integration', 'myfatoorah-woocommerce', MYFATOORAH_WOO_PLUGIN_PATH . 'languages/');
        }

        return ['wc-myfatoorah-blocks-integration'];
    }

    /**
     * Returns an array of key=>value pairs of data made available to the payment methods script.
     *
     * @return array
     */
    public function get_payment_method_data() {
        $gateways = $session  = $error    = null;
        try {
            if (!wc_checkout_is_https()) {
                throw new Exception(__('MyFatoorah forces SSL checkout Payment. Your checkout is not secure! Please, contact the site admin to enable SSL and ensure that the server has a valid SSL certificate.', 'myfatoorah-woocommerce'));
            }

            $mfWooGateway = new WC_Gateway_Myfatoorah_v2();
            $gateways     = is_admin() ? [] : $mfWooGateway->getGateways();
            $session      = is_admin() ? null : $mfWooGateway->getSession();
        } catch (Exception $ex) {
            $error = $ex->getMessage();
        }

        return [
            'title'       => __($this->get_setting('title'), 'myfatoorah-woocommerce'),
            'description' => __($this->get_setting('description'), 'myfatoorah-woocommerce'),
            'icon'        => $this->get_setting('icon'),
            'currency'    => get_woocommerce_currency(),
            'supports'    => $this->get_supported_features(),
            'gateways'    => $gateways,
            'session'     => $session,
            'isSaveCard'  => $this->get_setting('saveCard') == 'yes' && get_current_user_id(),
            'design'      => [
                'hideCardIcons'  => $this->get_setting('cardIcons') === 'yes',
                'designColor'    => $this->get_setting('designColor'),
                'themeColor'     => $this->get_setting('themeColor'),
                'designFont'     => $this->get_setting('designFont'),
                'designFontSize' => $this->get_setting('designFontSize')
            ],
            'mfLang'      => substr(determine_locale(), 0, 2),
            'mfVersion'   => MYFATOORAH_WOO_PLUGIN_VERSION,
            'error'       => $error
        ];
    }
}
