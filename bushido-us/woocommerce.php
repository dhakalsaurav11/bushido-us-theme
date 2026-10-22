<?php
/**
 * Product URLs and product categories.
 * A product has no page of its own: its URL shows Home with that product's dialog open.
 * The category grid comes from woocommerce/loop/loop-start.php and woocommerce/content-product.php.
 */

get_header();

if ( is_singular( 'product' ) ) {
	$bushido_open = wc_get_product( get_queried_object_id() );
	get_template_part( 'template-parts/home', null, array( 'open' => $bushido_open ) );
	if ( $bushido_open && isset( WC()->structured_data ) ) {
		WC()->structured_data->generate_product_data( $bushido_open );
	}
} else {
	echo '<h1 class="screen-reader-text">';
	woocommerce_page_title();
	echo '</h1>';
	woocommerce_content();
}

get_footer();
