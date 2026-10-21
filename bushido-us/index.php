<?php
/**
 * Fallback for anything without its own template: blog posts, search results and 404s.
 */

get_header();
?>
<div class="bh-content">
	<?php if ( is_404() ) : ?>
		<h1 class="bh-page-title"><?php esc_html_e( 'Page not found', 'bushido-us' ); ?></h1>
		<p class="bh-prose"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to the shop', 'bushido-us' ); ?></a></p>
	<?php elseif ( have_posts() ) : ?>
		<?php if ( is_singular() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class(); ?>>
					<h1 class="bh-page-title"><?php the_title(); ?></h1>
					<div class="bh-prose"><?php the_content(); ?></div>
				</article>
			<?php endwhile; ?>
		<?php else : ?>
			<h1 class="bh-page-title"><?php echo is_search() ? esc_html( sprintf( __( 'Results for “%s”', 'bushido-us' ), get_search_query() ) ) : wp_kses_post( get_the_archive_title() ?: get_bloginfo( 'name' ) ); ?></h1>
			<ul class="bh-prose" style="list-style:none;padding:0">
				<?php while ( have_posts() ) : the_post(); ?>
					<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
				<?php endwhile; ?>
			</ul>
			<div class="bh-pagination"><?php echo wp_kses_post( paginate_links() ?? '' ); ?></div>
		<?php endif; ?>
	<?php else : ?>
		<h1 class="bh-page-title"><?php esc_html_e( 'Nothing here yet', 'bushido-us' ); ?></h1>
	<?php endif; ?>
</div>
<?php
get_footer();
