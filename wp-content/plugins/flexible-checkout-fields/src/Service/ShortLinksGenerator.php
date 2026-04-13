<?php

namespace WPDesk\FCF\Free\Service;

use FcfVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FcfVendor\WPDesk\PluginBuilder\Plugin\HookablePluginDependant;
use FcfVendor\WPDesk\PluginBuilder\Plugin\PluginAccess;

/**
 * Creates helpers for short URLs.
 */
class ShortLinksGenerator implements Hookable, HookablePluginDependant {

	use PluginAccess;

	const SHORTENER_DOMAIN_PL = 'https://wpdesk.pl/sk/';

	const SHORTENER_DOMAIN_EN = 'https://wpdesk.net/sk/';

	/**
	 * {@inheritdoc}
	 */
	public function hooks() {
		add_filter( 'flexible_checkout_fields/short_url', [ $this, 'generate_short_url' ], 10, 2 );
	}

	/**
	 * Generates short URL for link.
	 *
	 * @param string $default_value Default value for filter.
	 * @param string $short_path    Path for short URL.
	 *
	 * @return string Short URL.
	 * @internal
	 */
	public function generate_short_url( string $default_value, string $short_path ): string {
		if ( ! preg_match( '/^[a-z-]+$/i', $short_path ) ) {
			return '#';
		}

		$locale     = get_user_locale();
		$locale_url = $locale === 'pl_PL' ? self::SHORTENER_DOMAIN_PL : self::SHORTENER_DOMAIN_EN;
		$short_url  = $locale_url . $short_path;
		switch ( $locale ) {
			case 'pl_PL':
				$short_url .= '-pl';
				break;
		}
		return $short_url;
	}
}
