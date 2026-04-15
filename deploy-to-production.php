<?php
/**
 * Eyeshot Tourism — Production Deploy Script
 * ============================================
 * Run ONCE on the live server after files are deployed:
 *
 *   wp eval-file deploy-to-production.php --url=https://eyeshottourism.com
 *
 * Safe to re-run (idempotent — same result every time).
 */

defined( 'ABSPATH' ) || die( 'Run via WP-CLI only.' );

$production_domain = 'eyeshottourism.com';
$local_domain      = 'eyeshottourism.ddev.site';

echo "\n=== Eyeshot Tourism — Production Deploy ===\n\n";

// ─────────────────────────────────────────────────────────────────────────────
// 1.  Site identity
// ─────────────────────────────────────────────────────────────────────────────
update_option( 'blogname',        'Eyeshot Tourism' );
update_option( 'blogdescription', 'Elite Qatar Escapes' );
echo "✔ Site name / tagline updated\n";

// ─────────────────────────────────────────────────────────────────────────────
// 2.  Astra settings
// ─────────────────────────────────────────────────────────────────────────────
$astra = get_option( 'astra-settings', [] );

// Brand colours
$astra['theme-color']            = '#1A56DB';
$astra['link-color']             = '#1A56DB';
$astra['link-h-color']           = '#0E3FA5';
$astra['text-color']             = '#111827';
$astra['site-layout-outside-bg-color'] = '#FFFFFF';
$astra['content-bg-color']       = '#FFFFFF';

// Buttons
$astra['button-color']           = '#FFFFFF';
$astra['button-h-color']         = '#FFFFFF';
$astra['button-bg-color']        = '#1A56DB';
$astra['button-bg-h-color']      = '#0E3FA5';
$astra['button-radius-fields']   = [
    'desktop'      => [ 'top' => 6, 'right' => 6, 'bottom' => 6, 'left' => 6 ],
    'tablet'       => [ 'top' => 6, 'right' => 6, 'bottom' => 6, 'left' => 6 ],
    'mobile'       => [ 'top' => 6, 'right' => 6, 'bottom' => 6, 'left' => 6 ],
    'desktop-unit' => 'px', 'tablet-unit' => 'px', 'mobile-unit' => 'px',
];
$astra['theme-button-padding']   = [
    'desktop'      => [ 'top' => 13, 'right' => 28, 'bottom' => 13, 'left' => 28 ],
    'tablet'       => [ 'top' => 12, 'right' => 24, 'bottom' => 12, 'left' => 24 ],
    'mobile'       => [ 'top' => 11, 'right' => 20, 'bottom' => 11, 'left' => 20 ],
    'desktop-unit' => 'px', 'tablet-unit' => 'px', 'mobile-unit' => 'px',
];
$astra['font-family-button']     = 'Inter';
$astra['font-weight-button']     = '600';

// Typography
$astra['body-font-family']       = 'Inter';
$astra['body-font-weight']       = '400';
$astra['body-font-size']         = 16;
$astra['body-line-height']       = 1.75;
$astra['headings-font-family']   = 'Inter';
$astra['headings-font-weight']   = '700';

// Footer
$astra['footer-bg-color']        = '#0E3FA5';
$astra['footer-color']           = '#DBEAFE';
$astra['footer-link-color']      = '#93C5FD';
$astra['footer-link-h-color']    = '#FFFFFF';

// Header
$astra['sticky-header']          = 1;
$astra['display-site-title']     = 0;
$astra['display-site-tagline']   = 0;
$astra['woo-header-cart-icon']   = 'bag';
$astra['header-logo-width']      = [
    'desktop' => 140, 'tablet' => 120, 'mobile' => 100,
];

// Global colour palette (plain hex strings — avoids Array-to-string warning)
$astra['global-color-palette']   = [
    'palette' => [
        '#1A56DB', '#0E3FA5', '#60A5FA', '#FFFFFF',
        '#EFF6FF', '#111827', '#6B7280', '#DBEAFE',
    ],
];

update_option( 'astra-settings', $astra );
echo "✔ Astra settings applied\n";

// ─────────────────────────────────────────────────────────────────────────────
// 3.  Navigation — ensure Cart is in the primary menu
// ─────────────────────────────────────────────────────────────────────────────
$locations = get_nav_menu_locations();
$menu_id   = isset( $locations['primary'] ) ? (int) $locations['primary'] : 0;

if ( ! $menu_id ) {
    // Fallback: find menu by name
    $menu = get_term_by( 'name', 'Main Navigation', 'nav_menu' );
    if ( $menu ) $menu_id = (int) $menu->term_id;
}

if ( $menu_id ) {
    // Find the Cart page by slug
    $cart_page = get_page_by_path( 'cart' );
    if ( ! $cart_page ) {
        $cart_page = wc_get_page_id( 'cart' ) ? get_post( wc_get_page_id( 'cart' ) ) : null;
    }

    if ( $cart_page ) {
        $existing = wp_get_nav_menu_items( $menu_id );
        $already  = false;
        foreach ( (array) $existing as $item ) {
            if ( (int) $item->object_id === (int) $cart_page->ID ) {
                $already = true;
                break;
            }
        }
        if ( ! $already ) {
            wp_update_nav_menu_item( $menu_id, 0, [
                'menu-item-title'     => 'Cart',
                'menu-item-object'    => 'page',
                'menu-item-object-id' => $cart_page->ID,
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
            ] );
            echo "✔ Cart added to primary navigation (page ID {$cart_page->ID})\n";
        } else {
            echo "✔ Cart already in navigation — skipped\n";
        }
    } else {
        echo "⚠ Cart page not found — skipping nav item\n";
    }
} else {
    echo "⚠ Primary menu not found — skipping cart nav item\n";
}

// ─────────────────────────────────────────────────────────────────────────────
// 4.  Homepage content — import from homepage-export.xml
//     The XML was exported from local DDEV after all Gutenberg/block edits.
//     Images in the content already reference eyeshottourism.com, so no
//     URL replacement is needed for the content itself.
// ─────────────────────────────────────────────────────────────────────────────
$xml_file = __DIR__ . '/homepage-export.xml';

if ( file_exists( $xml_file ) ) {
    echo "\n--- Importing homepage content from homepage-export.xml ---\n";

    // Use WordPress importer via WP-CLI as a shell call (most reliable on shared hosting)
    $cmd    = "wp import " . escapeshellarg( $xml_file ) . " --authors=skip 2>&1";
    $output = shell_exec( $cmd );
    echo $output ?: "✔ Import command sent\n";

    // Verify: check the front page has content now
    $front_id = (int) get_option( 'page_on_front' );
    if ( $front_id ) {
        $p = get_post( $front_id );
        if ( ! empty( $p->post_content ) ) {
            echo "✔ Homepage content imported (post ID {$front_id}, " . strlen( $p->post_content ) . " chars)\n";
        } else {
            echo "⚠ Homepage post exists but content is empty — check import manually\n";
        }
    }
    echo "⚠ NOTE: Delete homepage-export.xml from the server after this script runs.\n";
} else {
    echo "⚠ homepage-export.xml not found — skipping homepage import.\n";
    echo "  Upload homepage-export.xml to the same folder as this script, or\n";
    echo "  import it manually via WP Admin → Tools → Import → WordPress.\n";
}

// ─────────────────────────────────────────────────────────────────────────────
// 6.  Payment gateways — activate MyFatoorah, disable PayPal
// ─────────────────────────────────────────────────────────────────────────────

// Activate MyFatoorah plugin if not already active
$plugin_slug = 'myfatoorah-woocommerce/myfatoorah-woocommerce.php';
if ( ! is_plugin_active( $plugin_slug ) ) {
    $result = activate_plugin( $plugin_slug );
    if ( is_wp_error( $result ) ) {
        echo "✗ Could not activate MyFatoorah: " . $result->get_error_message() . "\n";
    } else {
        echo "✔ MyFatoorah plugin activated\n";
    }
} else {
    echo "✔ MyFatoorah already active\n";
}

// Disable PayPal gateway (set enabled = no in its settings)
$paypal_settings            = get_option( 'woocommerce_paypal_settings', [] );
$paypal_settings['enabled'] = 'no';
update_option( 'woocommerce_paypal_settings', $paypal_settings );

$paypal_ppcp                = get_option( 'woocommerce-ppcp-settings', [] );
$paypal_ppcp['enabled']     = 'no';
update_option( 'woocommerce-ppcp-settings', $paypal_ppcp );
echo "✔ PayPal gateway disabled\n";

// Set MyFatoorah UI preference (non-sensitive setting)
$mf = get_option( 'woocommerce_myfatoorah_v2_settings', [] );
$mf['newDesign'] = 'yes';
update_option( 'woocommerce_myfatoorah_v2_settings', $mf );
echo "✔ MyFatoorah settings applied\n";
echo "⚠ ACTION REQUIRED: Go to WooCommerce → Settings → Payments → MyFatoorah\n";
echo "  and enter your LIVE API key before accepting payments.\n";

// ─────────────────────────────────────────────────────────────────────────────
// 7.  Flush everything
// ─────────────────────────────────────────────────────────────────────────────
wp_cache_flush();
if ( function_exists( 'rocket_clean_domain' ) ) rocket_clean_domain();   // WP Rocket
if ( function_exists( 'w3tc_flush_all' )      ) w3tc_flush_all();        // W3 Total Cache
if ( function_exists( 'wpfc_clear_all_cache' ) ) wpfc_clear_all_cache(); // WP Fastest Cache

// Also flush rewrite rules
global $wp_rewrite;
$wp_rewrite->flush_rules( true );

echo "\n✔ Cache flushed\n";
echo "\n=== Deploy complete ===\n\n";
echo "Next steps:\n";
echo "  1. Visit the live site and verify the homepage looks correct\n";
echo "  2. Check the navigation menu includes Cart\n";
echo "  3. Verify the logo size and footer copyright colour\n";
echo "  4. ⚠ Enter your MyFatoorah LIVE API key: WooCommerce → Settings → Payments → MyFatoorah\n";
echo "  5. Test a payment end-to-end before announcing the site is live\n";
echo "  6. Delete this file from the server: rm deploy-to-production.php\n\n";
