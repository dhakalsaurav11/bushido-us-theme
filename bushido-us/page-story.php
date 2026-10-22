<?php
/**
 * Template Name: Story
 *
 * Used automatically by a page with the slug "story", or pick it from the page's Template setting.
 * The side figures show whole here, in front of the centre column, and the text runs down the
 * middle in hairline sections, one per heading. An empty page shows the default story text.
 */

get_header();

while ( have_posts() ) :
	the_post();
	$bushido_content = trim( get_the_content() ) ? apply_filters( 'the_content', get_the_content() ) : bushido_story_default();
	?>
	<h1 class="screen-reader-text"><?php the_title(); ?></h1>

	<div class="bh-story">
		<?php foreach ( bushido_story_sections( $bushido_content ) as $bushido_section ) : ?>
			<section class="bh-story-panel"><?php echo $bushido_section; // phpcs:ignore WordPress.Security.EscapeOutput -- post content. ?></section>
		<?php endforeach; ?>
	</div>
	<?php
endwhile;

get_footer();
