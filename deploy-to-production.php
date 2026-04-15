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
// 4.  Homepage content — apply all content fixes to the front-page post
// ─────────────────────────────────────────────────────────────────────────────
$front_page_id = (int) get_option( 'page_on_front' );
if ( ! $front_page_id ) {
    // Fallback: find page with slug 'home' or title 'Home'
    $home = get_page_by_path( 'home' ) ?: get_page_by_title( 'Home' );
    if ( $home ) $front_page_id = (int) $home->ID;
}

if ( $front_page_id ) {
    $post    = get_post( $front_page_id );
    $content = $post->post_content;
    $changed = false;

    // 4a. Strip <mark style="background-color:..."> wrappers (keep inner text)
    $before  = $content;
    $content = preg_replace(
        '/<mark\s+style="[^"]*"[^>]*>(.*?)<\/mark>/is',
        '$1',
        $content
    );
    if ( $content !== $before ) {
        echo "✔ Removed <mark> background-color wrappers from slider headings\n";
        $changed = true;
    }

    // 4b. Remove the "Contact Details" group block (name:Contact, bg:#dddddd)
    $search_from = 0;
    while ( ( $candidate = strpos( $content, '<!-- wp:group', $search_from ) ) !== false ) {
        $end     = strpos( $content, '-->', $candidate );
        if ( $end === false ) break;
        $comment = substr( $content, $candidate, $end - $candidate + 3 );

        if ( strpos( $comment, '"name":"Contact"' ) !== false
             && strpos( $comment, '#dddddd' ) !== false ) {

            // Nesting-aware removal
            $cursor    = $candidate + strlen( $comment );
            $depth     = 1;
            $block_end = false;

            while ( $depth > 0 ) {
                $nxt_open  = strpos( $content, '<!-- wp:group',  $cursor );
                $nxt_close = strpos( $content, '<!-- /wp:group', $cursor );
                if ( $nxt_close === false ) break;
                if ( $nxt_open !== false && $nxt_open < $nxt_close ) {
                    $depth++;
                    $cursor = $nxt_open + 13;
                } else {
                    $depth--;
                    if ( $depth === 0 ) {
                        $ce = strpos( $content, '-->', $nxt_close );
                        if ( $ce !== false ) $block_end = $ce + 3;
                    }
                    $cursor = $nxt_close + 14;
                }
            }

            if ( $block_end ) {
                $len     = $block_end - $candidate;
                $content = substr( $content, 0, $candidate ) . substr( $content, $block_end );
                echo "✔ Removed Contact Details group block ({$len} chars)\n";
                $changed = true;
                // Don't advance — re-check from same position for a second block
                continue;
            }
        }
        $search_from = $candidate + 13;
    }

    if ( $changed ) {
        wp_update_post( [ 'ID' => $front_page_id, 'post_content' => $content ] );
        echo "✔ Homepage (post ID {$front_page_id}) saved\n";
    } else {
        echo "✔ Homepage content already clean — no changes needed\n";
    }
} else {
    echo "⚠ Front page not found — skipping content fixes\n";
}

// ─────────────────────────────────────────────────────────────────────────────
// 5.  Fix any remaining local URLs in post content / options
// ─────────────────────────────────────────────────────────────────────────────
echo "\n--- Running URL search-replace ({$local_domain} → {$production_domain}) ---\n";
// WP-CLI search-replace is the safest way; call it as a system command
$cmd    = "wp search-replace '{$local_domain}' '{$production_domain}' --skip-columns=guid 2>&1";
$output = shell_exec( $cmd );
echo $output ?: "✔ search-replace complete\n";

// ─────────────────────────────────────────────────────────────────────────────
// 6.  Flush everything
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
echo "  4. Delete this file from the server: rm deploy-to-production.php\n\n";
