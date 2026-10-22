<?php
/**
 * Plain pages, and WooCommerce's Cart, Checkout and My account pages.
 * The Cart uses the Story layout (large logo, side figures shown whole) with the cart in the text lane.
 */

get_header();

while ( have_posts() ) :
	the_post();
	$bushido_wide = function_exists( 'is_woocommerce' ) && ( is_cart() || is_checkout() || is_account_page() );

	if ( function_exists( 'is_cart' ) && is_cart() ) :
		?>
		<h1 class="screen-reader-text"><?php the_title(); ?></h1>
		<div class="bh-story bh-story--cart">
			<div class="bh-prose bh-wide"><?php the_content(); ?></div>
		</div>
		<?php
		continue;
	endif;
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
