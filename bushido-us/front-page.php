<?php
/**
 * Home: in-stock products in the hairline grid. Drag products into order under
 * Products → Sorting; sold-out pieces are on the Archive page instead.
 */

get_header();
?>
<h1 class="screen-reader-text"><?php bloginfo( 'name' ); ?></h1>
<?php
$bushido_products = bushido_products( 'instock', 24 );
if ( $bushido_products ) {
	bushido_grid( $bushido_products );
} elseif ( ! bushido_has_woo() && current_user_can( 'activate_plugins' ) ) {
	echo '<p class="bh-empty">' . esc_html__( 'Install and activate WooCommerce to fill this grid with products.', 'bushido-us' ) . '</p>';
} else {
	echo '<p class="bh-empty">' . esc_html__( 'New pieces are on the way.', 'bushido-us' ) . '</p>';
}

get_footer();
