<?php
/**
 * One cell of the product grid. Expects $args['product'], a WC_Product.
 */

$product = $args['product'] ?? null;
if ( ! $product instanceof WC_Product ) {
	return;
}
$sold = ! $product->is_in_stock();
?>
<li class="bh-card<?php echo $sold ? ' bh-card--sold' : ''; ?>">
	<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
		<figure><?php echo $product->get_image( 'woocommerce_single', array( 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></figure>
		<div class="bh-meta">
			<span class="bh-name"><?php echo esc_html( $product->get_name() ); ?></span>
			<span class="bh-price">
				<?php
				if ( $sold ) {
					esc_html_e( 'Sold out', 'bushido-us' );
				} else {
					echo wp_kses_post( $product->get_price_html() );
				}
				?>
			</span>
		</div>
	</a>
</li>
