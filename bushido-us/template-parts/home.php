<?php
/**
 * Home grid: in-stock products. Drag products into order under Products → Sorting.
 * $args['open'] (optional WC_Product) is the product whose URL was visited; its dialog opens on load.
 */

$open = $args['open'] ?? null;
?>
<h1 class="screen-reader-text"><?php bloginfo( 'name' ); ?></h1>
<?php
$bushido_products = bushido_products( 'instock', 48 );
if ( $bushido_products ) {
	bushido_grid( $bushido_products );
} elseif ( ! bushido_has_woo() && current_user_can( 'activate_plugins' ) ) {
	echo '<p class="bh-empty">' . esc_html__( 'Install and activate WooCommerce to fill this grid with products.', 'bushido-us' ) . '</p>';
} else {
	echo '<p class="bh-empty">' . esc_html__( 'New pieces are on the way.', 'bushido-us' ) . '</p>';
}

// A visited product that isn't in the grid (sold out, say) still needs its dialog.
if ( $open instanceof WC_Product && ! in_array( $open->get_id(), array_map( fn( $p ) => $p->get_id(), $bushido_products ), true ) ) {
	get_template_part( 'template-parts/product-dialog', null, array( 'product' => $open ) );
}
