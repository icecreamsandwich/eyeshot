<?php
/**
 * Plugin Name: Eyeshot — One-Click Deploy (DELETE AFTER USE)
 * Description: Applies all DB settings to production. Activate it, then immediately delete it.
 * Version:     1.0
 */

// Only runs when plugin is activated
register_activation_hook( __FILE__, 'eyeshot_deploy_run' );

function eyeshot_deploy_run() {

    // 1. Site identity
    update_option( 'blogname',        'Eyeshot Tourism' );
    update_option( 'blogdescription', 'Elite Qatar Escapes' );

    // 2. Astra settings
    $astra = get_option( 'astra-settings', [] );

    $astra['theme-color']            = '#1A56DB';
    $astra['link-color']             = '#1A56DB';
    $astra['link-h-color']           = '#0E3FA5';
    $astra['text-color']             = '#111827';
    $astra['site-layout-outside-bg-color'] = '#FFFFFF';
    $astra['content-bg-color']       = '#FFFFFF';
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
    $astra['body-font-family']       = 'Inter';
    $astra['body-font-weight']       = '400';
    $astra['body-font-size']         = 16;
    $astra['body-line-height']       = 1.75;
    $astra['headings-font-family']   = 'Inter';
    $astra['headings-font-weight']   = '700';
    $astra['footer-bg-color']        = '#0E3FA5';
    $astra['footer-color']           = '#DBEAFE';
    $astra['footer-link-color']      = '#93C5FD';
    $astra['footer-link-h-color']    = '#FFFFFF';
    $astra['sticky-header']          = 1;
    $astra['display-site-title']     = 0;
    $astra['display-site-tagline']   = 0;
    $astra['woo-header-cart-icon']   = 'bag';
    $astra['header-logo-width']      = [
        'desktop' => 140, 'tablet' => 120, 'mobile' => 100,
    ];
    $astra['global-color-palette']   = [
        'palette' => [
            '#1A56DB', '#0E3FA5', '#60A5FA', '#FFFFFF',
            '#EFF6FF', '#111827', '#6B7280', '#DBEAFE',
        ],
    ];

    update_option( 'astra-settings', $astra );

    // 3. Add Cart to primary navigation
    $locations = get_nav_menu_locations();
    $menu_id   = isset( $locations['primary'] ) ? (int) $locations['primary'] : 0;
    if ( ! $menu_id ) {
        $menu = get_term_by( 'name', 'Main Navigation', 'nav_menu' );
        if ( $menu ) $menu_id = (int) $menu->term_id;
    }

    if ( $menu_id ) {
        $cart_id  = function_exists( 'wc_get_page_id' ) ? (int) wc_get_page_id( 'cart' ) : 0;
        if ( ! $cart_id ) {
            $cart_page = get_page_by_path( 'cart' );
            if ( $cart_page ) $cart_id = (int) $cart_page->ID;
        }
        if ( $cart_id ) {
            $existing = (array) wp_get_nav_menu_items( $menu_id );
            $already  = false;
            foreach ( $existing as $item ) {
                if ( (int) $item->object_id === $cart_id ) { $already = true; break; }
            }
            if ( ! $already ) {
                wp_update_nav_menu_item( $menu_id, 0, [
                    'menu-item-title'     => 'Cart',
                    'menu-item-object'    => 'page',
                    'menu-item-object-id' => $cart_id,
                    'menu-item-type'      => 'post_type',
                    'menu-item-status'    => 'publish',
                ] );
            }
        }
    }

    // 4. Homepage content fixes
    $front_id = (int) get_option( 'page_on_front' );
    if ( ! $front_id ) {
        $p = get_page_by_path( 'home' ) ?: get_page_by_title( 'Home' );
        if ( $p ) $front_id = (int) $p->ID;
    }
    if ( $front_id ) {
        $post    = get_post( $front_id );
        $content = $post->post_content;

        // Strip <mark style="..."> wrappers
        $content = preg_replace(
            '/<mark\s+style="[^"]*"[^>]*>(.*?)<\/mark>/is',
            '$1', $content
        );

        // Remove Contact Details group block
        $search_from = 0;
        while ( ( $pos = strpos( $content, '<!-- wp:group', $search_from ) ) !== false ) {
            $end = strpos( $content, '-->', $pos );
            if ( $end === false ) break;
            $comment = substr( $content, $pos, $end - $pos + 3 );
            if ( strpos( $comment, '"name":"Contact"' ) !== false
                 && strpos( $comment, '#dddddd' ) !== false ) {
                $cursor = $pos + strlen( $comment );
                $depth  = 1;
                $blkend = false;
                while ( $depth > 0 ) {
                    $no = strpos( $content, '<!-- wp:group',  $cursor );
                    $nc = strpos( $content, '<!-- /wp:group', $cursor );
                    if ( $nc === false ) break;
                    if ( $no !== false && $no < $nc ) { $depth++; $cursor = $no + 13; }
                    else {
                        $depth--;
                        if ( $depth === 0 ) {
                            $ce = strpos( $content, '-->', $nc );
                            if ( $ce !== false ) $blkend = $ce + 3;
                        }
                        $cursor = $nc + 14;
                    }
                }
                if ( $blkend ) {
                    $content = substr( $content, 0, $pos ) . substr( $content, $blkend );
                    continue;
                }
            }
            $search_from = $pos + 13;
        }

        wp_update_post( [ 'ID' => $front_id, 'post_content' => $content ] );
    }

    // 5. Payment gateways
    // Activate MyFatoorah if not already active
    $mf_slug = 'myfatoorah-woocommerce/myfatoorah-woocommerce.php';
    if ( ! is_plugin_active( $mf_slug ) ) {
        activate_plugin( $mf_slug );
    }

    // Disable PayPal
    $paypal = get_option( 'woocommerce_paypal_settings', [] );
    $paypal['enabled'] = 'no';
    update_option( 'woocommerce_paypal_settings', $paypal );

    $ppcp = get_option( 'woocommerce-ppcp-settings', [] );
    $ppcp['enabled'] = 'no';
    update_option( 'woocommerce-ppcp-settings', $ppcp );

    // MyFatoorah non-sensitive setting
    $mf = get_option( 'woocommerce_myfatoorah_v2_settings', [] );
    $mf['newDesign'] = 'yes';
    update_option( 'woocommerce_myfatoorah_v2_settings', $mf );

    // 6. Flush rewrite rules & cache
    flush_rewrite_rules( true );
    wp_cache_flush();

    // 7. Show success notice in WP Admin
    add_action( 'admin_notices', function () {
        echo '<div class="notice notice-success"><p>'
           . '<strong>✅ Eyeshot deploy complete!</strong> All settings applied. '
           . '<strong style="color:red">⚠ Next: Go to WooCommerce → Settings → Payments → MyFatoorah and enter your LIVE API key before accepting payments.</strong> '
           . 'Then deactivate and delete this plugin.'
           . '</p></div>';
    } );
}
