<?php
/**
 * Template Name: Sold-out archive
 *
 * Used automatically by a page with the slug "archive", or pick it from the page's Template setting.
 * Shows every sold-out product in the same grid as Home.
 */

get_header();
?>
<h1 class="screen-reader-text"><?php the_title(); ?></h1>
<?php
$bushido_products = bushido_products( 'outofstock', -1 );
if ( $bushido_products ) {
	bushido_grid( $bushido_products );
} else {
	echo '<p class="bh-empty">' . esc_html__( 'Nothing in the archive yet.', 'bushido-us' ) . '</p>';
}

get_footer();
