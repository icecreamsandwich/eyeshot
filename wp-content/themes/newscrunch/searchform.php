<?php   
/*search form for product search*/
?>
<form role="search" autocomplete="off" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>"> 
	<input type="hidden" name="post_type" value="product" />
	<input type="text" id="product-search-input" name="s" placeholder="<?php esc_attr_e( 'Search product...', 'newscrunch') ?>" />
 	<input type="hidden" name="product_cat" id="selected-product-cat" value="" />
 	<button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button> 
</form> 
<div class="product-search-results-container"></div>