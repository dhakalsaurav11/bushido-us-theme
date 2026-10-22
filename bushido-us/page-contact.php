<?php
/**
 * Template Name: Contact
 *
 * Same layout as Story. Used automatically by a page with the slug "contact", or pick it from the
 * page's Template setting. An empty page shows the default contact details.
 */

get_template_part( 'template-parts/story-layout', null, array( 'default' => bushido_contact_default() ) );
