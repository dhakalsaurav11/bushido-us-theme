<?php
/**
 * Opens the page: logo bar, the two side figures, and the start of the centre column.
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'bushido-us' ); ?></a>

<header class="bh-top">
	<a class="bh-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Bushido home', 'bushido-us' ); ?>">
		<?php bushido_logo(); ?>
	</a>
</header>

<?php // The Story page draws these figures in its centre column instead. ?>
<?php foreach ( bushido_is_story() ? array() : array( 'left', 'right' ) as $bushido_side ) : $bushido_img = bushido_side_image( $bushido_side ); ?>
<aside class="bh-side bh-side--<?php echo esc_attr( $bushido_side ); ?>" aria-hidden="true">
	<img src="<?php echo esc_url( $bushido_img['src'] ); ?>" alt=""<?php if ( $bushido_img['width'] ) : ?> width="<?php echo (int) $bushido_img['width']; ?>" height="<?php echo (int) $bushido_img['height']; ?>"<?php endif; ?>>
</aside>
<?php endforeach; ?>

<main class="bh-main" id="main">
