<?php
/**
 * Plain pages: About, Contact, and WooCommerce's Cart, Checkout and My account pages.
 */

get_header();

while ( have_posts() ) :
	the_post();
	$bushido_wide = function_exists( 'is_woocommerce' ) && ( is_cart() || is_checkout() || is_account_page() );
	?>
	<article <?php post_class( 'bh-content' ); ?>>
		<h1 class="bh-page-title"><?php the_title(); ?></h1>
		<div class="bh-prose<?php echo $bushido_wide ? ' bh-wide' : ''; ?>">
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
