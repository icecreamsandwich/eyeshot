<div class="notice notice-warning is-dismissible">
	<p>
		<strong>⚠️<?php esc_html_e( 'The plugin does not work with the default WooCommerce checkout.', 'flexible-checkout-fields' ); ?></strong>
	</p>
	<p>
		<?php
		printf(
			/* translators: %1$s: Open link tag, %2$s: Close link tag */
			esc_html__( 'Pssst… Did you know that we support block checkout via our Checkout Fields For Blocks plugin? It allows you to add, edit and personalize fields in the WooCommerce block checkout - quickly, conveniently and without coding. It is free - find it %1$shere%2$s', 'flexible-checkout-fields' ),
			'<a href="' . esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=checkout-fields-for-blocks&TB_iframe=true&width=772&height=550' ) ) . '" class="thickbox open-plugin-details-modal">',
			'</a>'
		);
		?>
	</p>
</div>
