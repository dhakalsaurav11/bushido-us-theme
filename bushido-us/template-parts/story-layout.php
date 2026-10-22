<?php
/**
 * Layout shared by Story and Contact: large logo, side figures shown whole, and the text down the
 * middle in hairline sections, one per heading. $args['default'] is shown while the page is empty.
 */

get_header();

while ( have_posts() ) :
	the_post();
	$bushido_content = trim( get_the_content() ) ? apply_filters( 'the_content', get_the_content() ) : ( $args['default'] ?? '' );
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
