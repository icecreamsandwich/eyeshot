<header class="nc-shop-header header-sidebar header-13" itemscope itemtype="http://schema.org/WPHeader">

	<?php get_template_part( 'partials/header/top-header' );?>
	<nav class="spnc spnc-custom <?php if(get_theme_mod('hide_show_sticky_header',false) != false):?>header-sticky<?php endif; ?> trsprnt-menu spnc-pro" role="navigation" itemscope itemtype="http://schema.org/SiteNavigationElement">
		
	<div class="spnc-header-logo">
		<div class="spnc-container">
			<div class="header-left">
			
				<?php do_action('newscrunch_header_logo');?>
			</div>
			<div class="header-center"> 
				<aside class="shop-product-search">
					<div class="shop-product-cat-search" id="shop-product-cat-search" tabindex="0">
		 				<span class="shop-product-cat-selected">
		 					<?php esc_html_e('All Categories', 'newscrunch');?>
		 				</span><i class="fa-solid fa-chevron-down"></i>
		  				<div class="shop-category-list" role="menu" aria-hidden="true">
		    				<div class="category-list-titl"> 
	    					    <span><?php esc_html_e('Select Category', 'newscrunch');?></span>
	    	 					<span class="shop-cat-close" id="shop-close-cat-list" tabindex="0" role="button"> 
	    	 						<i class="fa-solid fa-xmark"></i>
	    	 					</span>
		    	 	 		</div>
		    	 	 	 	<ul> 
		    	 	  		<?php $newscrunch_product_categories = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, ) ); if ( ! empty( $newscrunch_product_categories ) && ! is_wp_error( $newscrunch_product_categories ) ) { foreach ( $newscrunch_product_categories as $category ) { echo '<li role="menuitem" tabindex="-1"><a href="#" cat-value="' . esc_attr( $category->slug ) . '">' . esc_html( $category->name ) . '</a></li>'; } } ?>
		    	 	  	 	</ul> 
		    	 	  	</div>
		    	 	</div> 
        			<?php get_search_form(); ?>
    	 	  	</aside> 
		    </div>
									
			<div class="header-right">
				<div class="uti-btns">
					<?php
					if( get_theme_mod('hide_show_dark_light_icon',true ) == true ):?>
						<div class="spnc-dark-layout">
							<a class="spnc-dark-icon" id="spnc-layout-icon" href="#" title="<?php esc_attr_e('Dark Light Layout','newscrunch'); ?>"><i class="fas fa-solid fa-moon"></i></a>
						</div>
					<?php endif; ?>
				<?php if ( class_exists( 'WooCommerce' ) ) :?>
					<!-- User Account -->
					<div class="shop-user">
					    <a href="<?php echo esc_url( get_permalink( get_option('woocommerce_myaccount_page_id') ) ); ?>" class="header-icon">
					        <span><i class="fa-regular fa-user"></i></span>
					    </a>
					</div>
	                <div class="shop-cart header-cart nav spnc-nav spnc-right">
	                    <li class="menu-item">
	                        	<a class="cart-icon" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
	                            <i class="fas fa-shopping-cart"></i>
	                            <span class="cart-count"><?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?></span>
	    	                 </a>
		                     <ul class="dropdown-menu">
		                     	<div class="cart-dropdown">
			                   <?php woocommerce_mini_cart(); ?>
			                   </div>
	                         </ul>
	                    </li>
           			 </div>
				<?php endif; ?>
				</div>
			</div>
		</div>
			<div class="overlay"></div>
		</div>

		<div class="spnc-navbar">
			<div class="spnc-container">
				<div class="spnc-row">
					<aside class="shop-cat-menu">
						<div class="shop-cat-menu-head">
							<span class="shop-cat-menu-head-icon bars">
								<i class="fa-solid fa-bars-staggered"></i></span>
							<span class="shop-cat-menu-head-title"><?php esc_html_e('Shop By Category', 'newscrunch')?></span>
							<span class="shop-cat-menu-head-icon arrow"><i
									class="fa-solid fa-angle-down"></i></span>
						</div>
						<div class="shop-cat-card">
							<nav class="category-menu">
						        <?php
						        if ( has_nav_menu('shop_categories_menu') ) {
						            // Use assigned menu
						            wp_nav_menu(array(
						                'theme_location' => 'shop_categories_menu',
						                'container'      => false,
						                'menu_class'     => 'shop-cat-card-list',
						            ));
						        } else {
						            // Fallback: show WooCommerce product categories
						            echo '<ul class="shop-cat-card-list">';
						            wp_list_categories(array(
						                'taxonomy'     => 'product_cat',
						                'title_li'     => '',
						                'hide_empty'   => false, // show empty categories too
						                'hierarchical' => true,
						            ));
						            echo '</ul>';
						        }
						        ?>
						   </nav>
						</div>
					</aside>
					
					<button class="spnc-menu-open spnc-toggle" type="button" aria-controls="menu" aria-expanded="false" onclick="openNav()" aria-label="<?php esc_attr_e('Menu','newscrunch'); ?>">
						<i class="fas fa-bars"></i>
					</button>
					<!-- /.spnc-collapse -->
					<div class="collapse spnc-collapse" id="spnc-menu-open" >
						<a class="spnc-menu-close" onclick="closeNav()" href="#" title="<?php esc_attr_e('Close Off-Canvas','newscrunch'); ?>"><i class="fa-solid fa-xmark"></i></a>
						<?php do_action('newscrunch_header_logo');?>
						

						<div class="ml-0">
							<?php
							$newscrunch_nav = '<ul class="nav spnc-nav">%3$s';
							$newscrunch_nav .= '<li class="menu-item dropdown search_exists">'; 
				       		$newscrunch_nav .= '</li>';
							$newscrunch_nav .= '</ul>'; 
							$newscrunch_menu_class='';
							wp_nav_menu( array (
								'theme_location'	=>	'primary', 
								'menu_class'    	=>	'nav spnc-nav '.$newscrunch_menu_class.'',
								'items_wrap'    	=>	$newscrunch_nav,
								'fallback_cb'   	=>	'newscrunch_fallback_page_menu',
								'walker'        	=>	new Newscrunch_Nav_Walker()
							));
							?>
						</div>
					</div>
					
					<!-- /.spnc-collapse -->
					<div class=spnc-head-wrap>
						<div class="spnc-header-right">	
							<?php
							if( get_theme_mod('hide_show_toggle_icon',true ) == true ):?>
								<div class="spnc-widget-toggle">
									<a class="spnc-toggle-icon" onclick="spncOpenPanel()" href="#" title="<?php esc_attr_e('Toggle Icon','newscrunch'); ?>"><i class="fas fa-bars"></i></a>
								</div>
							<?php endif;?>
						</div>
					</div>
				</div>
			</div>
			<div class="spnc-nav-menu-overlay"></div>
		</div>
	</nav>
</header>
<?php if( get_theme_mod('hide_show_toggle_icon',true ) == true ):?>	
<div id="spnc_panelSidebar" class="spnc_sidebar_panel">
	<a href="javascript:void(0)" class="spnc_closebtn" onclick="spncClosePanel()" title="<?php esc_attr_e('Close Icon','newscrunch'); ?>">×</a>
	<div class="spnc-right-sidebar">
		<div class="spnc-sidebar" id="spnc-sidebar-panel-fixed">
	    	<div class="right-sidebar">      
				<?php newscrunch_side_panel_widget_area( 'menu-widget-area' );?>        
			</div>
		</div>
	</div>
</div>
<?php endif;?>
<div class="clrfix"></div>