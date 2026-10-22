<?php
/**
 * Template Name: Story
 *
 * Used automatically by a page with the slug "story", or pick it from the page's Template setting.
 * An empty page shows the default story text.
 */

get_template_part( 'template-parts/story-layout', null, array( 'default' => bushido_story_default() ) );
