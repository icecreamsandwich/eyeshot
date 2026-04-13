<?php
/**
 * Plugin Name: Eyeshot Tourism – Custom Styles
 * Description: Site-wide UI improvements, padding fixes, and layout overrides.
 */

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'eyeshot-custom',
		content_url( 'mu-plugins/eyeshot-custom/custom.css' ),
		[],
		filemtime( __DIR__ . '/eyeshot-custom/custom.css' )
	);
} );
