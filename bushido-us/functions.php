<?php
/**
 * Bushido US theme setup.
 *
 * Every WooCommerce call is guarded, so the theme still renders (with an empty grid)
 * if it is activated before WooCommerce is installed.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function bushido_has_woo() {
	return class_exists( 'WooCommerce' );
}

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array( 'height' => 228, 'width' => 286, 'flex-width' => true, 'flex-height' => true ) );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );

	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 600,
		'single_image_width'    => 900,
	) );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus( array(
		'primary' => __( 'Bottom navigation', 'bushido-us' ),
	) );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'bushido-us-font', 'https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&display=swap', array(), null );

	$deps = bushido_has_woo() ? array( 'woocommerce-general' ) : array();
	$path = get_stylesheet_directory() . '/style.css';
	wp_enqueue_style( 'bushido-us', get_stylesheet_uri(), $deps, file_exists( $path ) ? (string) filemtime( $path ) : null );

	// Keeps the Bag count right after AJAX add-to-cart and on cached pages.
	if ( bushido_has_woo() ) {
		wp_enqueue_script( 'wc-cart-fragments' );
	}
} );

add_filter( 'wp_resource_hints', function ( $urls, $relation ) {
	if ( 'preconnect' === $relation ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}, 10, 2 );

/* Side figures, swappable under Appearance → Customize → Side figures. */
add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_section( 'bushido_sides', array(
		'title'       => __( 'Side figures', 'bushido-us' ),
		'description' => __( 'The two figures fixed in the side panels on wide screens. Transparent PNG or WebP cut-outs work best.', 'bushido-us' ),
		'priority'    => 30,
	) );
	foreach ( array( 'left' => __( 'Left figure', 'bushido-us' ), 'right' => __( 'Right figure', 'bushido-us' ) ) as $side => $label ) {
		$wp_customize->add_setting( "bushido_side_{$side}", array( 'sanitize_callback' => 'esc_url_raw' ) );
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, "bushido_side_{$side}", array(
			'label'   => $label,
			'section' => 'bushido_sides',
		) ) );
	}
} );

function bushido_side_image( $side ) {
	$custom = get_theme_mod( "bushido_side_{$side}" );
	if ( $custom ) {
		return array( 'src' => $custom, 'width' => null, 'height' => null );
	}
	$sizes = array( 'left' => array( 458, 871 ), 'right' => array( 495, 800 ) );
	return array(
		'src'    => get_template_directory_uri() . "/assets/img/side-{$side}.webp",
		'width'  => $sizes[ $side ][0],
		'height' => $sizes[ $side ][1],
	);
}

function bushido_logo() {
	$logo_id = get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		echo wp_get_attachment_image( $logo_id, 'full', false, array( 'alt' => get_bloginfo( 'name' ) ) );
		return;
	}
	printf(
		'<img src="%s" alt="%s" width="286" height="228">',
		esc_url( get_template_directory_uri() . '/assets/img/logo.webp' ),
		esc_attr__( 'Bushido', 'bushido-us' )
	);
}

/* Bag link in the bottom bar. Also returned as a cart fragment, so it updates without a reload. */
function bushido_bag_link() {
	if ( ! bushido_has_woo() || ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '<span class="bh-bag" aria-hidden="true"></span>';
	}
	$count = WC()->cart->get_cart_contents_count();
	return sprintf(
		'<a class="bh-bag" href="%s">%s (%d)</a>',
		esc_url( wc_get_cart_url() ),
		esc_html__( 'Bag', 'bushido-us' ),
		$count
	);
}

add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	$fragments['.bh-bag'] = bushido_bag_link();
	return $fragments;
} );

/* Used when no menu is assigned to the bottom navigation yet: Home, Shop, then any of the
   Archive, About and Contact pages that exist. */
function bushido_nav_fallback() {
	$items = array( array( home_url( '/' ), __( 'Home', 'bushido-us' ), is_front_page() ) );
	if ( bushido_has_woo() ) {
		$items[] = array( get_permalink( wc_get_page_id( 'shop' ) ), __( 'Shop', 'bushido-us' ), is_shop() );
	}
	foreach ( array( 'archive', 'about', 'contact' ) as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			$items[] = array( get_permalink( $page ), get_the_title( $page ), is_page( $page->ID ) );
		}
	}
	echo '<ul class="bh-tabs">';
	foreach ( $items as $item ) {
		printf(
			'<li><a href="%s"%s>%s</a></li>',
			esc_url( $item[0] ),
			$item[2] ? ' aria-current="page"' : '',
			esc_html( $item[1] )
		);
	}
	echo '</ul>';
}

/* Product grid used by Home, Shop and Archive. */
function bushido_grid( $products ) {
	echo '<div class="bh-grid-clip"><ul class="bh-grid">';
	foreach ( $products as $product ) {
		get_template_part( 'template-parts/product-card', null, array( 'product' => $product ) );
	}
	echo '</ul></div>';
}

function bushido_products( $stock_status, $limit ) {
	if ( ! bushido_has_woo() ) {
		return array();
	}
	return wc_get_products( array(
		'status'       => 'publish',
		'visibility'   => 'catalog',
		'stock_status' => $stock_status,
		'limit'        => $limit,
		'orderby'      => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	) );
}

if ( bushido_has_woo() ) {
	// The design has no page title, result count or sort dropdown above the grid.
	add_filter( 'woocommerce_show_page_title', '__return_false' );
	add_action( 'init', function () {
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
	} );

	// Sold-out pieces live on the Archive page, not in the shop.
	add_action( 'woocommerce_product_query', function ( $q ) {
		$tax   = (array) $q->get( 'tax_query' );
		$tax[] = array(
			'taxonomy' => 'product_visibility',
			'field'    => 'name',
			'terms'    => array( 'outofstock' ),
			'operator' => 'NOT IN',
		);
		$q->set( 'tax_query', $tax );
	} );

	add_filter( 'woocommerce_output_related_products_args', function ( $args ) {
		$args['posts_per_page'] = 3;
		$args['columns']        = 3;
		return $args;
	} );
}
