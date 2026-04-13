<?php

namespace WPDesk\FCF\Free\Blocks;

use FcfVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FcfVendor\WPDesk\PluginBuilder\Plugin\HookablePluginDependant;
use FcfVendor\WPDesk\PluginBuilder\Plugin\PluginAccess;

class Editor implements Hookable, HookablePluginDependant {

	use PluginAccess;

	private const HANDLE_CSS = 'fcf-block-editor-css';
	private const HANDLE_JS  = 'fcf-block-editor-js';

	public function hooks() {
		add_action( 'enqueue_block_editor_assets', [ $this, 'load_assets_for_blocks' ] );
	}

	public function load_assets_for_blocks(): void {
		$is_debug = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
		$version  = $is_debug ? time() : $this->plugin->get_script_version();
		$assets   = trailingslashit( $this->plugin->get_plugin_assets_url() );
		$suffix   = $is_debug ? '.js' : '.min.js';

		add_thickbox();

		wp_register_style(
			self::HANDLE_CSS,
			$assets . 'css/admin-blocks.css',
			[ 'wp-edit-blocks' ],
			$version
		);
		wp_enqueue_style( self::HANDLE_CSS );

		wp_register_script(
			self::HANDLE_JS,
			$assets . 'js/admin-blocks' . $suffix,
			[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-compose', 'wp-hooks', 'wp-i18n', 'wp-block-editor' ],
			$version,
			true
		);

		wp_localize_script(
			self::HANDLE_JS,
			'fcf_block_data',
			[
				'message'     => __( 'The plugin does not work with the default WooCommerce checkout. Install Checkout Fields for Blocks to handle blocks.', 'flexible-checkout-fields' ),
				'button_text' => __( 'Plugin Details', 'flexible-checkout-fields' ),

				'plugin_slug' => 'checkout-fields-for-blocks',
				'admin_url'   => admin_url( 'plugin-install.php' ),
			]
		);

		wp_enqueue_script( self::HANDLE_JS );
	}
}
