<?php

namespace WPDesk\FCF\Free\Notice;

use Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils;

class BlockCompatibility implements Notice {

	private const NOTICE_NAME = 'notice_flexible_wishlist_compatibility';

	public function get_notice_name(): string {
		return self::NOTICE_NAME;
	}

	public function is_active(): bool {
		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'wpdesk_checkout_fields_settings' ) {
			return false;
		}

		if ( ! CartCheckoutUtils::is_checkout_block_default() ) {
			return false;
		}

		return true;
	}

	public function get_template_path(): string {
		return 'notices/compatibility-notice';
	}

	public function get_vars_for_view(): array {
		return [];
	}

	public function set_notice_as_hidden( bool $is_permanently ) {
	}

	public function add_notice_scripts(): void {
		add_thickbox();
	}
}
