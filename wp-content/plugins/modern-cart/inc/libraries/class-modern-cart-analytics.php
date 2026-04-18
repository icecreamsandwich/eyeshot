<?php
/**
 * Modern Cart Analytics.
 *
 * Registers the free plugin with BSF Analytics and injects free-feature
 * usage stats into the BSF stats payload. The Pro plugin appends its own
 * Pro-specific stats via the `modern_cart_get_specific_stats` filter, making
 * this class the single source of truth for all Modern Cart analytics.
 *
 * All KPI values are read directly from existing plugin options — no event
 * counters or transients are created.
 *
 * @package modern-cart
 * @since   1.0.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Modern_Cart_Analytics
 *
 * Singleton that owns all BSF Analytics integration for the free plugin.
 * The Pro plugin extends the payload via the `modern_cart_get_specific_stats`
 * filter rather than registering a second BSF Analytics entity.
 *
 * @since 1.0.7
 */
class Modern_Cart_Analytics {

	/**
	 * Singleton instance.
	 *
	 * @since 1.0.7
	 * @var Modern_Cart_Analytics|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @since 1.0.7
	 * @return Modern_Cart_Analytics
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor — registers hooks.
	 *
	 * @since 1.0.7
	 */
	private function __construct() {
		add_filter( 'bsf_core_stats', array( $this, 'get_specific_stats' ), 20 );
	}

	/**
	 * Inject free-plugin stats into the BSF Analytics payload.
	 *
	 * Collects free-feature stats via helper methods, then fires the
	 * `modern_cart_get_specific_stats` filter so the Pro plugin can append
	 * Pro-specific data without registering a duplicate analytics entity.
	 *
	 * @since 1.0.7
	 *
	 * @param array<string, mixed> $stats_data Existing BSF stats array.
	 * @return array<string, mixed> Stats array with Modern Cart data added.
	 */
	public function get_specific_stats( $stats_data ) {
		// Combine all features to make a full list.
		$theme_data = $this->get_active_theme();

		if ( ! isset( $stats_data['plugin_data'] ) || ! is_array( $stats_data['plugin_data'] ) ) {
			$stats_data['plugin_data'] = [];
		}

		// Prepare default data to be tracked.
		$stats_data['plugin_data']['modern_cart']                   = $this->get_default_stats( $theme_data );
		$stats_data['plugin_data']['modern_cart']['kpi_records']    = $this->get_kpi_tracking_data();
		$stats_data['plugin_data']['modern_cart']['boolean_values'] = $this->get_boolean_values();

		/**
		 * Filter to allow the Pro plugin (or other extensions) to append
		 * additional stats to the Modern Cart analytics payload.
		 *
		 * @since 1.0.7
		 *
		 * @param array<string, mixed> $stats_data Stats array with free data already set.
		 */
		$stats_data = apply_filters( 'modern_cart_get_specific_stats', $stats_data );

		return $stats_data;
	}

	/**
	 * Get default stats for the free plugin.
	 *
	 * Reads values directly from existing plugin options. No counters or
	 * event tracking — values reflect the user's current configuration.
	 *
	 * @since 1.0.7
	 *
	 * @param array<string, mixed> $theme_data Active theme data from get_active_theme().
	 * @return array<string, mixed> Default stats array.
	 */
	public function get_default_stats( $theme_data ) {
		$main_settings     = (array) get_option( MODERNCART_MAIN_SETTINGS, array() );
		$cart_settings     = (array) get_option( MODERNCART_SETTINGS, array() );
		$floating_settings = (array) get_option( MODERNCART_FLOATING_SETTINGS, array() );

		return array(
			'website-domain'         => str_ireplace( array( 'http://', 'https://' ), '', home_url() ),
			'moderncart-version'     => defined( 'MODERNCART_VER' ) ? MODERNCART_VER : '',
			'moderncart-pro-version' => defined( 'MODERNCART_PRO_VER' ) ? MODERNCART_PRO_VER : '',
			'locale'                 => get_locale(), // User's site current language.
			'active-theme'           => $theme_data['parent_theme'], // Currently active theme.
			'enable_moderncart'      => ! empty( $main_settings['enable_moderncart'] ) ? $main_settings['enable_moderncart'] : '', // Modern Cart enabled [disabled, wc_pages, all].
			'cart_style'             => ! empty( $cart_settings['cart_theme_style'] ) ? $cart_settings['cart_theme_style'] : '', // Cart theme style [style1, style2].
			'product_image_size'     => ! empty( $cart_settings['product_image_size'] ) ? $cart_settings['product_image_size'] : '', // Product image size [thumbnail, medium, large].
			'floating_cart_position' => ! empty( $floating_settings['floating_cart_position'] ) ? $floating_settings['floating_cart_position'] : '', // Floating cart position [bottom-left, bottom-right, etc.].
		);
	}

	/**
	 * Get setting stats as boolean values for free features.
	 *
	 * Values are read directly from existing plugin options and compared
	 * inline — no separate counters are written.
	 *
	 * @since 1.0.7
	 *
	 * @return array<string, bool> Boolean feature flag stats.
	 */
	public function get_boolean_values() {
		$main_settings = (array) get_option( MODERNCART_MAIN_SETTINGS, array() );
		$cart_settings = (array) get_option( MODERNCART_SETTINGS, array() );

		return array(
			'enabled_free_shipping_bar' => ! empty( $main_settings['enable_free_shipping_bar'] ) && true === boolval( $main_settings['enable_free_shipping_bar'] ), // Track if free shipping bar is enabled.
			'enabled_coupon_field'      => ! empty( $cart_settings['enable_coupon_field'] ) && 'disabled' !== $cart_settings['enable_coupon_field'], // Track if coupon field is enabled.
			'enabled_ajax_add_to_cart'  => ! empty( $main_settings['enable_ajax_add_to_cart'] ) && true === boolval( $main_settings['enable_ajax_add_to_cart'] ), // Track if AJAX add-to-cart is enabled.
			'enabled_express_checkout'  => ! empty( $main_settings['enable_express_checkout'] ) && true === boolval( $main_settings['enable_express_checkout'] ), // Track if express checkout is enabled.
			'enabled_powered_by'        => ! empty( $main_settings['enable_powered_by'] ) && true === boolval( $main_settings['enable_powered_by'] ), // Track if "Powered by" branding is enabled.
		);
	}

	/**
	 * Get KPI tracking data for the last 2 days (excluding today).
	 *
	 * Loops over yesterday and the day before, fetching the count of orders
	 * attributed to Modern Cart for each date. Matches the structure used by
	 * other BSF plugins so the analytics pipeline can aggregate it consistently.
	 *
	 * @since 1.0.8
	 *
	 * @return array<string, array<string, array<string, int>>> KPI data keyed by date.
	 */
	private function get_kpi_tracking_data() {
		$kpi_data = array();
		$today    = current_time( 'Y-m-d' );

		// Collect data for yesterday and the day before yesterday.
		for ( $i = 1; $i <= 2; $i++ ) {
			$date      = gmdate( 'Y-m-d', (int) strtotime( $today . ' -' . $i . ' days' ) );
			$kpi_data[ $date ] = array(
				'numeric_values' => array(
					'order_count' => $this->get_daily_orders_count( $date ),
				),
			);
		}

		return $kpi_data;
	}

	/**
	 * Count Modern Cart–attributed orders for a given date.
	 *
	 * Queries orders that carry the `_moderncart_source` meta flag set by
	 * Order_Tracking::flag_session() at checkout. Supports both the legacy
	 * post-meta storage and WooCommerce HPOS (custom order tables).
	 *
	 * @since 1.0.8
	 *
	 * @param string $date Date in Y-m-d format.
	 * @return int Number of completed/processing orders attributed to Modern Cart.
	 */
	private function get_daily_orders_count( $date ) {
		global $wpdb;

		$start_date = $date . ' 00:00:00';
		$end_date   = $date . ' 23:59:59';

		// HPOS compatibility: choose the correct tables and column names.
		if (
			class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) &&
			\Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()
		) {
			$order_date_key   = 'date_created_gmt';
			$order_status_key = 'status';
			$order_id_key     = 'order_id';
			$order_table      = $wpdb->prefix . 'wc_orders';
			$order_meta_table = $wpdb->prefix . 'wc_orders_meta';
			$order_type_key   = 'type';
			$order_table_id   = 'id';
		} else {
			$order_date_key   = 'post_date';
			$order_status_key = 'post_status';
			$order_id_key     = 'post_id';
			$order_table      = $wpdb->prefix . 'posts';
			$order_meta_table = $wpdb->prefix . 'postmeta';
			$order_type_key   = 'post_type';
			$order_table_id   = 'ID';
		}

		//phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM $order_table o
				INNER JOIN $order_meta_table om
					ON o.$order_table_id = om.$order_id_key
					AND om.meta_key = '_moderncart_source'
					AND om.meta_value = '1'
				WHERE o.$order_type_key = 'shop_order'
					AND o.$order_status_key IN ('wc-completed', 'wc-processing')
					AND o.$order_date_key >= %s
					AND o.$order_date_key <= %s",
				$start_date,
				$end_date
			)
		);
		//phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return absint( $count );
	}

	/**
	 * Retrieve the active theme names.
	 *
	 * @since 1.0.7
	 *
	 * @return array{parent_theme: string, child_theme: bool}
	 */
	public function get_active_theme() {
		$theme             = wp_get_theme();
		$parent_theme_name = '';
		$child_theme_name  = '';

		if ( isset( $theme->parent_theme ) && ! empty( $theme->parent_theme ) ) {
			$parent_theme_name = $theme->parent_theme;
			$child_theme_name  = $theme->name ? $theme->name : '';
		} else {
			$parent_theme_name = $theme->name;
		}

		return array(
			'parent_theme' => ! empty( $child_theme_name ) ? $child_theme_name : $parent_theme_name,
			'child_theme'  => ! empty( $child_theme_name ) ? true : false,
		);
	}
}

Modern_Cart_Analytics::get_instance();