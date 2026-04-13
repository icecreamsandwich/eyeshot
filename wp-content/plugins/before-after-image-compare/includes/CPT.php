<?php

class CPT {

    public $post_type = 'image-compare';

    public function __construct() {
        add_action( 'init', [$this, 'registerIcbPostType'] );
        add_action( 'admin_menu', [$this, 'adminMenu'] );
        add_action( 'admin_enqueue_scripts', [$this, 'adminEnqueueScripts'] );

        add_shortcode( 'baicb', [$this, 'shortcodeCallback'] );

        add_filter( "manage_image-compare_posts_columns", [$this, 'manageColumns'] );
        add_action( "manage_image-compare_posts_custom_column", [$this, 'manageCustomColumns'], 10, 2 );

        // add_filter( 'custom_menu_order', [$this, 'orderSubMenu'] );
    }


    public function registerIcbPostType() {
        register_post_type( $this->post_type, [
            'labels' => [
                'name'               => __( 'Image Compare', 'image-compare' ),
                'singular_name'      => __( 'Image Compare', 'image-compare' ),
                'add_new'            => __( 'Add New', 'image-compare' ),
                'add_new_item'       => __( 'Add New Image Compare', 'image-compare' ),
                'edit_item'          => __( 'Edit Image Compare', 'image-compare' ),
                'all_items'          => __( 'All Comparisons', 'image-compare' ),
                'not_found'          => __( 'No comparisons found.', 'image-compare' ),
            ],
            'public'              => false,
            'show_ui'             => true,
            'show_in_rest'        => true,
            'menu_position'       => 20,
            'menu_icon'           => 'data:image/svg+xml;base64,' .base64_encode('<svg id="Layer_1" enable-background="new 0 0 48 48" height="512" viewBox="0 0 48 48" width="512" fill="#fff" xmlns="http://www.w3.org/2000/svg"><path d="m20 6h-10c-2.2 0-4 1.8-4 4v28c0 2.2 1.8 4 4 4h10v4h4v-44h-4zm0 30h-10l10-12zm18-30h-10v4h10v26l-10-12v18h10c2.2 0 4-1.8 4-4v-28c0-2.2-1.8-4-4-4z"/></svg>'),
            'supports'            => [ 'title', 'editor' ],
            'template'            => [ ['icb/image-compare'] ], 
            'template_lock'       => 'all',
        ]);
    }

    public function adminMenu() {
        $parent_slug = 'edit.php?post_type=' . $this->post_type;

        add_submenu_page(
            $parent_slug,
            __( 'Help & Demos', 'image-compare' ),
            __( 'Help & Demos', 'image-compare' ),
            'manage_options',
            'image-compare-help',
            [$this, 'renderPage']
        );
    }


    public function manageColumns( $columns ) {
        $new_columns = [];
        foreach ( $columns as $key => $value ) {
            $new_columns[$key] = $value;
            if ( $key === 'title' ) {
                $new_columns['shortcode'] = __( 'Shortcode', 'image-compare' );
            }
        }
        return $new_columns;
    }

    public function manageCustomColumns( $column_name, $post_ID ) {
        if ( $column_name === 'shortcode' ) {
			echo '<div class="bPlAdminShortcode" id="bPlAdminShortcode-' . esc_attr( $post_ID ) . '">
				<input value="[baicb id=' . esc_attr( $post_ID ) . ']" onclick="copyBPlAdminShortcode(\'' . esc_attr( $post_ID ) . '\')">
				<span class="tooltip">' . esc_html__( 'Copy To Clipboard', 'image-compare' ) . '</span>
			</div>';
        }
    }
    public function shortcodeCallback( $atts ) {
        $atts = shortcode_atts( ['id' => null], $atts, 'baicb' );
        
        if ( ! $atts['id'] ) return '';

        $post = get_post( $atts['id'] );
        if ( ! $post || post_password_required( $post ) ) return '';

        wp_enqueue_style('icb-view-css', BAICB_DIR_URL . 'build/view.css', [], BAICB_PLUGIN_VERSION);
        wp_enqueue_script('icb-view-js', BAICB_DIR_URL . 'build/view.js', ['react', 'react-dom'], BAICB_PLUGIN_VERSION, true);

        $blocks = parse_blocks( $post->post_content );
        return ( ! empty( $blocks ) ) ? render_block( $blocks[0] ) : '';
    }


    public function adminEnqueueScripts( $hook ) {

        if ( strpos( $hook, 'image-compare-help') ) {
            wp_enqueue_style( 'icb-admin', BAICB_DIR_URL . 'build/admin.css', [], BAICB_PLUGIN_VERSION );
            wp_enqueue_script( 'icb-admin', BAICB_DIR_URL . 'build/admin.js', ['react', 'react-dom', 'wp-util'], BAICB_PLUGIN_VERSION, true );
        }
				if ('edit.php' === $hook || 'post.php' === $hook) {
						wp_enqueue_style( 'icb-admin-post', BAICB_DIR_URL . 'build/clipboard.css', [], BAICB_PLUGIN_VERSION );
						wp_enqueue_script( 'icb-admin-post', BAICB_DIR_URL . 'build/clipboard.js', [], BAICB_PLUGIN_VERSION, true );
						wp_set_script_translations( 'icb-admin-post', 'image-compare', BAICB_DIR_PATH . 'languages' );
				}
    }

			function renderPage() {
				$dashboardData = [
					"version" => BAICB_PLUGIN_VERSION,
					"logo"	=> 'https://ps.w.org/before-after-image-compare/assets/icon-128x128.png?rev=3193735',
					"isPremium" => icbImageCompareChecker(),
					"nonce" => wp_create_nonce("icbLicenseActivation"),
					"hasPro"=> BAICB_HAS_PRO,
					'licenseActiveNonce' => wp_create_nonce("icbLicenseActivation")
				];

				?>
				<div id="icbAdminDashboard"  data-dashboard="<?php echo esc_attr( wp_json_encode( $dashboardData )  ); ?>">
				</div>
				<?php
			}

      
//   function orderSubMenu( $menu_ord ){
//         global $submenu;

// 		if ( !icbImageCompareChecker() && isset( $submenu['edit.php?post_type=' . $this->post_type] ) ) {
// 			unset( $submenu['edit.php?post_type=' . $this->post_type][5] );
// 			unset( $submenu['edit.php?post_type=' . $this->post_type][10] );
// 		}

// 		return $menu_ord;
// 	}

}

new CPT();