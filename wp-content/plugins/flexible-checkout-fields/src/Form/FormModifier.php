<?php

namespace WPDesk\FCF\Free\Form;

use FcfVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Default checkout form modifications.
 */
class FormModifier implements Hookable {

	public function hooks() {
		add_action( 'woocommerce_before_order_notes', [ $this, 'maybe_hide_order_section' ] );
	}

	/**
	 * Hides checkout order section ("Additional information")
	 * when there are no checkout fields to display.
	 *
	 * @param \WC_Checkout $checkout.
	 */
	public function maybe_hide_order_section( $checkout ): void {
		if ( ! $checkout instanceof \WC_Checkout ) {
			return;
		}

		$order_fields = $checkout->get_checkout_fields( 'order' );

		if ( is_array( $order_fields ) && 0 === count( $order_fields ) ) {
			add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
		}
	}
}
