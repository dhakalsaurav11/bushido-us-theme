<?php
/**
 * Template Name: Story
 *
 * Used automatically by a page with the slug "story", or pick it from the page's Template setting.
 * The two side figures move into the centre column as one fixed backdrop and the text scrolls
 * over them in white panels, one per heading. An empty page shows the default story text.
 */

get_header();

while ( have_posts() ) :
	the_post();
	$bushido_content = trim( get_the_content() ) ? apply_filters( 'the_content', get_the_content() ) : bushido_story_default();
	?>
	<h1 class="screen-reader-text"><?php the_title(); ?></h1>

	<div class="bh-story-figures" aria-hidden="true">
		<?php foreach ( array( 'left', 'right' ) as $bushido_side ) : $bushido_img = bushido_side_image( $bushido_side ); ?>
			<img class="bh-story-figure bh-story-figure--<?php echo esc_attr( $bushido_side ); ?>" src="<?php echo esc_url( $bushido_img['src'] ); ?>" alt="">
		<?php endforeach; ?>
	</div>

	<div class="bh-story">
		<?php foreach ( bushido_story_sections( $bushido_content ) as $bushido_section ) : ?>
			<section class="bh-story-panel"><?php echo $bushido_section; // phpcs:ignore WordPress.Security.EscapeOutput -- post content. ?></section>
		<?php endforeach; ?>
	</div>
	<?php
endwhile;

get_footer();
