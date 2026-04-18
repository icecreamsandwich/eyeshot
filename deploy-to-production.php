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
$astra['footer-copyright-editor'] = 'Copyright [copyright] [current_year] [site_title] | Powered by Eyeshot';

// WooCommerce shop
$astra['product-sale-notification'] = 'default'; // ensures sale badge is always rendered

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
// 3.  Navigation — rebuild primary menu with exactly 7 items
//     Home | About | Book Your Tour | Services | Gallery | Contact | Cart
// ─────────────────────────────────────────────────────────────────────────────
$locations = get_nav_menu_locations();
$menu_id   = isset( $locations['primary'] ) ? (int) $locations['primary'] : 0;

if ( ! $menu_id ) {
    $menu = get_term_by( 'name', 'Main Navigation', 'nav_menu' );
    if ( $menu ) {
        $menu_id = (int) $menu->term_id;
    } else {
        $menu_id = wp_create_nav_menu( 'Main Navigation' );
        $locs             = get_nav_menu_locations();
        $locs['primary']  = $menu_id;
        set_theme_mod( 'nav_menu_locations', $locs );
        echo "✔ Created new 'Main Navigation' menu\n";
    }
}

// Wipe all existing items so we start fresh (removes any auto-added extras)
$old_items = wp_get_nav_menu_items( $menu_id );
foreach ( (array) $old_items as $old_item ) {
    wp_delete_post( (int) $old_item->ID, true );
}

// Helper: find a page by slug, fall back to title search
function eyeshot_find_page( $slug, $title = '' ) {
    $page = get_page_by_path( $slug );
    if ( ! $page && $title ) {
        $results = get_posts( [
            'post_type'   => 'page',
            'title'       => $title,
            'numberposts' => 1,
            'post_status' => 'publish',
        ] );
        $page = $results ? $results[0] : null;
    }
    return $page;
}

// Helper: append a page item to the menu
function eyeshot_add_menu_page( $menu_id, $label, $page, $position ) {
    if ( ! $page ) { return; }
    wp_update_nav_menu_item( $menu_id, 0, [
        'menu-item-title'     => $label,
        'menu-item-object'    => 'page',
        'menu-item-object-id' => (int) $page->ID,
        'menu-item-type'      => 'post_type',
        'menu-item-status'    => 'publish',
        'menu-item-position'  => $position,
    ] );
}

$pos = 1;

// 1. Home — custom link to site root
wp_update_nav_menu_item( $menu_id, 0, [
    'menu-item-title'    => 'Home',
    'menu-item-url'      => home_url( '/' ),
    'menu-item-type'     => 'custom',
    'menu-item-status'   => 'publish',
    'menu-item-position' => $pos++,
] );

// 2. About
eyeshot_add_menu_page( $menu_id, 'About', eyeshot_find_page( 'about', 'About' ) ?? eyeshot_find_page( 'about-us', 'About Us' ), $pos++ );

// 3. Book Your Tour — prefer dedicated page, fall back to WooCommerce shop
$book_page = eyeshot_find_page( 'book-your-tour', 'Book Your Tour' )
          ?? eyeshot_find_page( 'book-tour', 'Book Tour' )
          ?? ( ( $shop_id = wc_get_page_id( 'shop' ) ) > 0 ? get_post( $shop_id ) : null );
eyeshot_add_menu_page( $menu_id, 'Book Your Tour', $book_page, $pos++ );

// 4. Services
eyeshot_add_menu_page( $menu_id, 'Services', eyeshot_find_page( 'services', 'Services' ), $pos++ );

// 5. Gallery
eyeshot_add_menu_page( $menu_id, 'Gallery', eyeshot_find_page( 'gallery', 'Gallery' ), $pos++ );

// 6. Contact
eyeshot_add_menu_page( $menu_id, 'Contact', eyeshot_find_page( 'contact', 'Contact' ), $pos++ );

// 7. Cart
$cart_wc_id = wc_get_page_id( 'cart' );
if ( $cart_wc_id > 0 ) {
    eyeshot_add_menu_page( $menu_id, 'Cart', get_post( $cart_wc_id ), $pos++ );
}

echo "✔ Primary menu rebuilt: Home → About → Book Your Tour → Services → Gallery → Contact → Cart\n";

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
// 5.  Contact page — update map to correct address
//     Street 69, Zone 55, Building 9, Doha, Bin Al Ishaq, Al Aziziya
// ─────────────────────────────────────────────────────────────────────────────
$contact_page = get_page_by_path( 'contact' );
if ( ! $contact_page ) {
    $pages = get_posts( [
        'post_type'   => 'page',
        'title'       => 'Contact',
        'numberposts' => 1,
        'post_status' => 'publish',
    ] );
    $contact_page = $pages ? $pages[0] : null;
}

if ( $contact_page ) {
    $new_map_url = 'https://maps.google.com/maps?q=Street+69%2C+Zone+55%2C+Building+9%2C+Al+Aziziya%2C+Doha%2C+Qatar&output=embed';
    $content     = $contact_page->post_content;
    $updated     = preg_replace(
        '/(<iframe[^>]+src=")[^"]*google[^"]*(")/i',
        '$1' . $new_map_url . '$2',
        $content
    );
    if ( $updated && $updated !== $content ) {
        wp_update_post( [ 'ID' => $contact_page->ID, 'post_content' => $updated ] );
        echo "✔ Contact page map updated to Al Aziziya address\n";
    } else {
        $iframe = '<iframe src="' . $new_map_url . '" width="100%" height="400" '
                . 'style="border:0;width:100%;" allowfullscreen="" loading="lazy" '
                . 'referrerpolicy="no-referrer-when-downgrade"></iframe>';
        echo "⚠ No existing Google Maps iframe found on contact page.\n";
        echo "  Insert this block manually into the Contact page Custom HTML block:\n";
        echo "  " . $iframe . "\n";
    }
} else {
    echo "⚠ Contact page not found — skipping map update\n";
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
// 7.  Activate required plugins (slider, etc.)
// ─────────────────────────────────────────────────────────────────────────────
$plugins_to_activate = [
    'super-block-slider/super-block-slider.php' => 'Super Block Slider',
    'modern-cart/modern-cart.php'               => 'Modern Cart',
];
foreach ( $plugins_to_activate as $slug => $label ) {
    if ( ! is_plugin_active( $slug ) ) {
        $result = activate_plugin( $slug );
        if ( is_wp_error( $result ) ) {
            echo "✗ Could not activate {$label}: " . $result->get_error_message() . "\n";
        } else {
            echo "✔ {$label} plugin activated\n";
        }
    } else {
        echo "✔ {$label} already active\n";
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// 8.  Flush everything
// ─────────────────────────────────────────────────────────────────────────────
wp_cache_flush();
if ( function_exists( 'rocket_clean_domain' ) )  rocket_clean_domain();   // WP Rocket
if ( function_exists( 'w3tc_flush_all' )      )  w3tc_flush_all();        // W3 Total Cache
if ( function_exists( 'wpfc_clear_all_cache' ) ) wpfc_clear_all_cache();  // WP Fastest Cache
do_action( 'litespeed_purge_all' );                                       // LiteSpeed Cache

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
