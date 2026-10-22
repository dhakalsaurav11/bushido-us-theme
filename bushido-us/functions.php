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

	register_nav_menus( array(
		'primary' => __( 'Bottom navigation', 'bushido-us' ),
	) );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'bushido-us-font', 'https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&display=swap', array(), null );

	$deps = bushido_has_woo() ? array( 'woocommerce-general' ) : array();
	$path = get_stylesheet_directory() . '/style.css';
	wp_enqueue_style( 'bushido-us', get_stylesheet_uri(), $deps, file_exists( $path ) ? (string) filemtime( $path ) : null );

	if ( bushido_has_woo() ) {
		// Keeps the Bag count right after add-to-cart and on cached pages.
		wp_enqueue_script( 'wc-cart-fragments' );

		$js = get_template_directory() . '/assets/js/product-dialog.js';
		wp_enqueue_script( 'bushido-product-dialog', get_template_directory_uri() . '/assets/js/product-dialog.js', array( 'wc-cart-fragments' ), file_exists( $js ) ? (string) filemtime( $js ) : null, array( 'in_footer' => true ) );
		wp_localize_script( 'bushido-product-dialog', 'bushidoDialog', array(
			'addUrl'   => WC_AJAX::get_endpoint( 'add_to_cart' ),
			'home'     => home_url( '/' ),
			'siteName' => get_bloginfo( 'name' ),
			'open'     => is_singular( 'product' ) ? get_queried_object_id() : 0,
		) );
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

/* Used when no menu is assigned to the bottom navigation yet: Home, then any of the
   Story, Archive, About and Contact pages that exist. */
function bushido_nav_fallback() {
	$items = array( array( home_url( '/' ), __( 'Home', 'bushido-us' ), is_front_page() ) );
	foreach ( array( 'story', 'archive', 'about', 'contact' ) as $slug ) {
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

/* Story, Contact (page-story.php, page-contact.php) and the Cart share a layout: large logo, side figures shown whole. */
function bushido_is_story() {
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		return true;
	}
	return is_page() && ( in_array( get_page_template_slug(), array( 'page-story.php', 'page-contact.php' ), true ) || is_page( array( 'story', 'contact' ) ) );
}

add_filter( 'body_class', function ( $classes ) {
	if ( bushido_is_story() ) {
		$classes[] = 'bh-story-page';
	}
	return $classes;
} );

/* One panel per heading: content before the first <h2> is its own panel. */
function bushido_story_sections( $html ) {
	$parts = preg_split( '/(?=<h2[\s>])/i', (string) $html, -1, PREG_SPLIT_NO_EMPTY );
	return array_values( array_filter( array_map( 'trim', $parts ), fn( $part ) => '' !== trim( wp_strip_all_tags( $part ) ) ) );
}

/* Shown while the Story page has no content of its own. Text from bushidonepal.com/about-us. */
function bushido_story_default() {
	ob_start();
	?>
	<h2>Built in Kathmandu. Worn your way.</h2>
	<p>Streetwear shaped by Japanese folklore and the energy of the city we call home.</p>
	<h2>A Kathmandu point of view.</h2>
	<p>Our point of view is local and outward-looking. The streets around us, the people who wear Bushido and the visual worlds that keep pulling us back all shape the work.</p>
	<p>We make clothing with a clear identity: expressive enough to be noticed, considered enough to become part of everyday life.</p>
	<h2>Folklore, translated.</h2>
	<p>Dragons, sakura, Ryujin and the idea of mushin run through our collections. We translate those references through silhouette, layering, colour and graphic placement rather than treating them as costume.</p>
	<p>The result is clothing that carries a story without telling you how to wear it.</p>
	<h2>Made for repeat wear.</h2>
	<p>A garment still has a job to do. We focus on silhouettes that layer easily, graphics that reward a closer look and pieces that feel personal long after the first wear.</p>
	<p>Each drop adds another chapter, while the people wearing it decide what that chapter becomes.</p>
	<h2>The next chapter is worn.</h2>
	<p>Explore the current collection and find the piece that becomes part of your own story.</p>
	<p><a class="bh-story-cta" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Shop the current drop', 'bushido-us' ); ?></a></p>
	<?php
	return ob_get_clean();
}

/* Shown while the Contact page has no content of its own. */
function bushido_contact_default() {
	ob_start();
	?>
	<h2>All sales are final.</h2>
	<h2>Email</h2>
	<p><a href="mailto:admin@bushidonepal.com">admin@bushidonepal.com</a></p>
	<h2>Instagram</h2>
	<p><a href="https://www.instagram.com/bushidonepal/" target="_blank" rel="noopener">@bushidonepal</a></p>
	<?php
	return ob_get_clean();
}

/* Product grid used by Home, Archive and product categories. */
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

	// Home is the shop. WooCommerce still needs its Shop page, so keep it but send visitors home,
	// along with WooCommerce's own "Return to shop" and "Browse store" links.
	add_action( 'template_redirect', function () {
		if ( is_shop() && ! is_front_page() && ! is_search() ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	} );
	add_filter( 'woocommerce_return_to_shop_redirect', fn() => home_url( '/' ) );

	// The empty cart shows only its message and the link home: skip the divider, the
	// "New in store" heading and the product row WooCommerce puts under it.
	add_filter( 'pre_render_block', function ( $pre, $block, $parent ) {
		if ( ! $parent instanceof WP_Block || 'woocommerce/empty-cart-block' !== $parent->name ) {
			return $pre;
		}
		$name = $block['blockName'] ?? '';
		$slug = $block['attrs']['slug'] ?? '';
		if ( in_array( $name, array( 'core/separator', 'woocommerce/product-new', 'woocommerce/product-collection' ), true )
			|| ( 'core/pattern' === $name && 'woocommerce/cart-new-in-store-message' === $slug ) ) {
			return '';
		}
		return $pre;
	}, 10, 3 );
	add_filter( 'woocommerce_get_shop_page_permalink', fn() => home_url( '/' ) );
}
