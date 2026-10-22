<?php
/**
 * Closes the centre column and draws the bottom navigation.
 */
?>
</main>

<nav class="bh-nav" aria-label="<?php esc_attr_e( 'Main', 'bushido-us' ); ?>">
	<div class="bh-nav-inner">
		<span class="bh-nav-spacer" aria-hidden="true"></span>
		<?php
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'bh-tabs',
			'depth'          => 1,
			'fallback_cb'    => 'bushido_nav_fallback',
		) );
		?>
		<?php echo bushido_bag_link(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?>
	</div>
</nav>

<?php if ( bushido_has_woo() ) : ?>
<dialog class="bh-dialog"></dialog>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
