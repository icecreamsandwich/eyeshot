<?php

/**
 * 
 * @link              https://www.myfatoorah.com/
 * @package           myfatoorah-woocommerce/myfatoorah-woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       MyFatoorah - WooCommerce
 * Plugin URI:        https://myfatoorah.readme.io/docs/woocommerce/
 * Description:       MyFatoorah Payment Gateway for WooCommerce. Integrated with MyFatoorah DHL/Aramex Shipping Methods.
 * Version:           2.2.11
 * Author:            MyFatoorah
 * Author URI:        https://www.myfatoorah.com/
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       myfatoorah-woocommerce
 * Domain Path:       /languages
 * 
 * Requires at least: 6.0
 * Tested up to: 6.9
 * 
 * Requires PHP: 7.4
 *
 * WC requires at least: 9.5
 * WC tested up to: 10.4
 * Requires Plugins: woocommerce
 */
if (!defined('ABSPATH')) {
    exit;
}
if (!defined('WPINC')) {
    die;
}

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use MyFatoorah\WooCommerce\Payments\Blocks\MyFatoorahV2;

//MFWOO_PLUGIN
define('MYFATOORAH_WOO_PLUGIN_VERSION', '2.2.11');
define('MYFATOORAH_WOO_PLUGIN', plugin_basename(__FILE__));
define('MYFATOORAH_WOO_PLUGIN_NAME', dirname(MYFATOORAH_WOO_PLUGIN));
define('MYFATOORAH_WOO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MYFATOORAH_WOO_TEMPLATES_PATH', plugin_dir_path(__FILE__) . 'includes/templates/');
define('MYFATOORAH_WOO_ASSETS_URL', plugins_url('public', MYFATOORAH_WOO_PLUGIN));

require_once MYFATOORAH_WOO_PLUGIN_PATH . 'includes/libraries/MyfatoorahLoader.php';
require_once MYFATOORAH_WOO_PLUGIN_PATH . 'includes/libraries/MyfatoorahLibrary.php';

/**
 * MyFatoorah WooCommerce Class
 */
class MyfatoorahWoocommerce {
//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Static property to hold our singleton instance
     *
     */
    static $instance = false;

    /**
     * Constructor
     */
    public function __construct() {
        add_filter('plugin_row_meta', array($this, 'plugin_row_meta'), 10, 2);

        //actions
        add_action('init', [$this, 'init'], 100);
        add_action('plugins_loaded', [$this, 'plugins_loaded'], 100);

        add_action('activate_plugin', [$this, 'activate_plugin'], 0);
        add_action('deactivate_plugin', [$this, 'deactivate_plugin']);
        add_action('in_plugin_update_message-' . MYFATOORAH_WOO_PLUGIN, [$this, 'prefix_plugin_update_message'], 10, 2);
        add_action('upgrader_process_complete', [$this, 'upgrader_process_complete'], 10, 2);

        //to show that MyFatoorah is supported with the woo features
        //http://wordpress-6.2.2.com/wp-admin/plugins.php?plugin_status=incompatible_with_feature
        add_action('before_woocommerce_init', [$this, 'before_woocommerce_init']);

        //Bloks
        add_action('woocommerce_blocks_loaded', [$this, 'woocommerce_blocks_loaded']);
        add_action('rest_api_init', [$this, 'rest_api_init']);
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * If an instance exists, this returns it. If not, it creates one and returns it.
     *
     * @return self object
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Show row meta on the plugin screen.
     *
     * @param mixed $links Plugin Row Meta.
     * @param mixed $file  Plugin Base file.
     *
     * @return array
     */
    public static function plugin_row_meta($links, $file) {
        if (MYFATOORAH_WOO_PLUGIN === $file) {
            $row_meta = array(
                'docs'    => '<a href="' . esc_url('https://myfatoorah.readme.io/docs/woocommerce') . '" aria-label="' . esc_attr__('View MyFatoorah documentation', 'myfatoorah-woocommerce') . '">' . esc_html__('Docs', 'woocommerce') . '</a>',
                'apidocs' => '<a href="' . esc_url('https://myfatoorah.readme.io/docs') . '" aria-label="' . esc_attr__('View MyFatoorah API docs', 'myfatoorah-woocommerce') . '">' . esc_html__('API docs', 'woocommerce') . '</a>',
                'support' => '<a href="' . esc_url('https://myfatoorah.com/contact.html') . '" aria-label="' . esc_attr__('Visit premium customer support', 'myfatoorah-woocommerce') . '">' . esc_html__('Premium support', 'woocommerce') . '</a>',
            );

            //unset($links[2]);
            return array_merge($links, $row_meta);
        }

        return (array) $links;
    }

//-----------------------------------------------------------------------------------------------------------------------------

    function deactivate_plugin($plugin) {
        if ($plugin == MYFATOORAH_WOO_PLUGIN) {
            $this->updateTransFile();
        }
    }

    function activate_plugin($plugin) {
        // Localisation
        if ($plugin == MYFATOORAH_WOO_PLUGIN) {
            $this->updateIconFile();
            $this->updateToNewDesign();
        }

        //nice code but give graceful failure in
        //https://plugintests.com/plugins/wporg/myfatoorah-woocommerce/latest
        //it is very important to say that the plugin is MyFatoorah
        /*
          $pluginsArr  = apply_filters('active_plugins', get_option('active_plugins'));
          $siteWideArr = apply_filters('active_plugins', get_site_option('active_sitewide_plugins'));

          $isWooPlugActive  = is_array($pluginsArr) && in_array('woocommerce/woocommerce.php', $pluginsArr);
          $isSiteWideActive = is_array($siteWideArr) && array_key_exists('woocommerce/woocommerce.php', $siteWideArr);

          if ($plugin == MYFATOORAH_WOO_PLUGIN && !$isWooPlugActive && !$isSiteWideActive) {
          $msg = __('WooCommerce plugin needs to be activated first to activate MyFatoorah plugin.', 'myfatoorah-woocommerce');
          wp_die($msg, 403);
          }

         */
    }

//-----------------------------------------------------------------------------------------------------------------------------
    function upgrader_process_complete($upgraderObject, $options) {
        // If an update has taken place and the updated type is plugins and the plugins element exists
        if ($options['action'] == 'update' && $options['type'] == 'plugin' && isset($options['plugins'])) {
            foreach ($options['plugins'] as $plugin) {
                // Check to ensure it's my plugin
                if ($plugin == MYFATOORAH_WOO_PLUGIN) {
                    $this->updateTransFile();
                    $this->updateIconFile();
                    $this->updateToNewDesign();
                }
            }
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------
    function updateTransFile() {
        $path  = WP_LANG_DIR . '/plugins/myfatoorah-woocommerce';
        $files = glob($path . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    function updateIconFile() {
        $v2Options = get_option('woocommerce_myfatoorah_v2_settings');
        if (isset($v2Options['icon']) && str_contains($v2Options['icon'], 'plugins/myfatoorah-woocommerce/assets/images/v2.png')) {
            $v2Options['icon'] = str_replace('assets/images/v2.png', 'public/images/myfatoorah.png', $v2Options['icon']);
            update_option('woocommerce_myfatoorah_v2_settings', apply_filters('woocommerce_settings_api_sanitized_fields_' . 'myfatoorah_v2', $v2Options), 'yes');
        }

        $emOptions = get_option('woocommerce_myfatoorah_embedded_settings');
        if (isset($emOptions['icon']) && str_contains($emOptions['icon'], 'plugins/myfatoorah-woocommerce/assets/images/embedded.png')) {
            $emOptions['icon'] = str_replace('assets/images/embedded.png', 'public/images/myfatoorah.png', $emOptions['icon']);
            update_option('woocommerce_myfatoorah_embedded_settings', apply_filters('woocommerce_settings_api_sanitized_fields_' . 'myfatoorah_embedded', $emOptions), 'yes');
        }
    }

    function updateToNewDesign() {
        $v2Options = get_option('woocommerce_myfatoorah_v2_settings');

        $v2Options['newDesign'] = 'yes';
        update_option('woocommerce_myfatoorah_v2_settings', apply_filters('woocommerce_settings_api_sanitized_fields_' . 'myfatoorah_v2', $v2Options), 'yes');
    }

//-----------------------------------------------------------------------------------------------------------------------------
    function admin_notices() {
        $msg = __('MyFatoorah - WooCommerce plugin needs WooCommerce plugin to be installed and active.', 'myfatoorah-woocommerce');
        echo '<div class="error"><p><strong>' . $msg . '</strong></p></div>';
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Init localizations and files
     */
    public function init() {
        // Localisation
        load_plugin_textdomain('myfatoorah-woocommerce', false, MYFATOORAH_WOO_PLUGIN_NAME . '/languages');

        //load cron
        //https://www.codesmade.com/wordpress-add-cron-job-programmatically/
        add_action('myfatoorah_backup_log_files', [$this, 'myfatoorah_backup_log_files']);
        if (!wp_next_scheduled('myfatoorah_backup_log_files')) {
            wp_schedule_event(time(), 'weekly', 'myfatoorah_backup_log_files');
        }
    }

    public function plugins_loaded() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'admin_notices']);
            return;
        }

        //load payment
        require_once dirname(__FILE__) . '/includes/PluginPaymentMyfatoorahWoocommerce.php';
        new PluginPaymentMyfatoorahWoocommerce('v2');

        //load shipping
        require_once dirname(__FILE__) . '/includes/PluginShippingMyfatoorahWoocommerce.php';
        new PluginShippingMyfatoorahWoocommerce();

        //load webhook
        require_once dirname(__FILE__) . '/includes/PluginWebhookMyfatoorahWoocommerce.php';
        new PluginWebhookMyfatoorahWoocommerce();
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Show important release note
     * @param type $data
     * @param type $response
     */
    function prefix_plugin_update_message($data, $response) {
        $notice = null;
        if (!empty($data['upgrade_notice'])) {
            $notice = trim(strip_tags($data['upgrade_notice']));
        } else if (!empty($response->upgrade_notice)) {
            $notice = trim(strip_tags($response->upgrade_notice));
        }

        if (!empty($notice)) {
            printf(
                    '<div class="update-message notice-error"><p style="background-color: #d54e21; padding: 10px; color: #f9f9f9; margin-top: 10px"><strong>Important Upgrade Notice: </strong>%s',
                    __($notice, 'myfatoorah-woocommerce')
            );
        }
        //https://andidittrich.com/2015/05/howto-upgrade-notice-for-wordpress-plugins.html
    }

    //-----------------------------------------------------------------------------------------------------------------------------
    function myfatoorah_backup_log_files() {
        $codes   = array_keys(apply_filters('myfatoorah_woocommerce_payment_gateways', []));
        $codes[] = 'shipping';
        $codes[] = 'webHook';

        foreach ($codes as $code) {
            $this->myfatoorah_backup_log_file($code);
        }
    }

    function myfatoorah_backup_log_file($code) {
        $myfatoorahLogFile = WC_LOG_DIR . 'myfatoorah_' . $code . '.log';
        if (file_exists($myfatoorahLogFile)) {
            $mfLogFolder = WC_LOG_DIR . 'mfOldLog';
            if (!file_exists($mfLogFolder)) {
                mkdir($mfLogFolder);
            }

            $mfLogFolder .= '/' . $code;
            if (!file_exists($mfLogFolder)) {
                mkdir($mfLogFolder);
            }

            rename($myfatoorahLogFile, $mfLogFolder . '/' . date('Y-m-d') . '_myfatoorah_' . $code . '.log');
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------
    function before_woocommerce_init() {
        if (class_exists(FeaturesUtil::class)) {
            //to remove mf from feature_id=custom_order_tables list
            //to disable waring message for High-Performance Order Storage features
            //http://wordpress-6.2.2.com/wp-admin/plugins.php?plugin_status=incompatible_with_feature&feature_id=custom_order_tables
            //https://github.com/woocommerce/woocommerce/wiki/High-Performance-Order-Storage-Upgrade-Recipe-Book
            FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);

            //to remove mf from feature_id=cart_checkout_blocks
            //http://wordpress-6.2.2.com/wp-admin/plugins.php?plugin_status=incompatible_with_feature&feature_id=cart_checkout_blocks
            //https://woocommerce.com/document/cart-checkout-blocks-support-status/
            //https://developer.woocommerce.com/2021/03/15/integrating-your-payment-method-with-cart-and-checkout-blocks/
            //follow instruction here b4 enable it
            //https://developer.woo.com/2023/11/06/faq-extending-cart-and-checkout-blocks/
            //https://github.com/woocommerce/woocommerce-blocks/blob/trunk/docs/third-party-developers/extensibility/checkout-payment-methods/payment-method-integration.md#registering-assets
            FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
        }
    }

    function woocommerce_blocks_loaded() {
        if (!class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
            return;
        }

//        if (!class_exists('WC_Gateway_Myfatoorah_v2')) {
//            require_once dirname(__FILE__) . '/includes/payments/class-wc-gateway-myfatoorah-v2.php';
//        }

        require_once dirname(__FILE__) . '/includes/payments/blocks/MyFatoorahV2.php';
        add_action(
                'woocommerce_blocks_payment_method_type_registration',
                function (PaymentMethodRegistry $payment_method_registry) {
                    $payment_method_registry->register(new MyFatoorahV2());
                }
        );
    }

    function rest_api_init() {
        register_rest_route(
                'myfatoorah/v2',
                '/refresh',
                [
                    'methods'             => 'POST',
                    'permission_callback' => '__return_true',
                    'callback'            => [$this, 'mf_wc_blocks_refresh_cb'],
                ]
        );
    }

    function mf_wc_blocks_refresh_cb(\WP_REST_Request $request) {
        try {
            if (function_exists('wc_load_cart')) {
                wc_load_cart();
            }
            if (WC()->cart instanceof \WC_Cart) {
                WC()->cart->calculate_totals();
            }

            $gw = new \WC_Gateway_Myfatoorah_v2();
            return rest_ensure_response([
                'gateways' => $gw->getGateways(),
                'session'  => $gw->getSession(),
            ]);
        } catch (\Throwable $e) {
            return new \WP_Error('mf_refresh_failed', $e->getMessage(), ['status' => 400]);
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------
}

// Instantiate our class
//new MyfatoorahWoocommerce();
MyfatoorahWoocommerce::getInstance();
