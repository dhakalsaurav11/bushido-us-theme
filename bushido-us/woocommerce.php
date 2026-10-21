<?php
/**
 * Shop, product categories and single products.
 * The grid markup itself comes from woocommerce/loop/loop-start.php and woocommerce/content-product.php.
 */

get_header();

if ( is_singular( 'product' ) ) {
	echo '<div class="bh-content">';
	woocommerce_content();
	echo '</div>';
} else {
	echo '<h1 class="screen-reader-text">';
	woocommerce_page_title();
	echo '</h1>';
	woocommerce_content();
}

get_footer();
