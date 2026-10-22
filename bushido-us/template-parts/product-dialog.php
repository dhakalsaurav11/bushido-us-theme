<?php
/**
 * A product's dialog, kept in an inert <template> next to its card until someone opens it.
 * Expects $args['product'], a WC_Product. assets/js/product-dialog.js clones it into the <dialog>.
 */

$product = $args['product'] ?? null;
if ( ! $product instanceof WC_Product ) {
	return;
}

$pid  = $product->get_id();
$name = $product->get_name();

// Main image first, then the gallery.
$images = array();
foreach ( array_unique( array_filter( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) ) ) as $image_id ) {
	$full = wp_get_attachment_image_src( $image_id, 'woocommerce_single' );
	if ( ! $full ) {
		continue;
	}
	$thumb    = wp_get_attachment_image_src( $image_id, 'woocommerce_gallery_thumbnail' );
	$images[] = array(
		'src'    => $full[0],
		'srcset' => (string) wp_get_attachment_image_srcset( $image_id, 'woocommerce_single' ),
		'thumb'  => $thumb ? $thumb[0] : $full[0],
		'alt'    => get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ?: $name,
	);
}
if ( ! $images ) {
	$images[] = array( 'src' => wc_placeholder_img_src( 'woocommerce_single' ), 'srcset' => '', 'thumb' => '', 'alt' => $name );
}

// Option groups (Size, Color…) and the variations they map to. Values are compared as slugs.
$groups     = array();
$variations = array();
if ( $product->is_type( 'variable' ) ) {
	foreach ( $product->get_attributes() as $attribute ) {
		if ( ! $attribute->get_variation() ) {
			continue;
		}
		$attr_name = $attribute->get_name();
		$options   = array();
		if ( $attribute->is_taxonomy() ) {
			foreach ( wc_get_product_terms( $pid, $attr_name, array( 'fields' => 'all' ) ) as $term ) {
				$options[] = array( sanitize_title( $term->slug ), $term->name );
			}
		} else {
			foreach ( $attribute->get_options() as $option ) {
				$options[] = array( sanitize_title( $option ), $option );
			}
		}
		$groups[] = array(
			'key'     => 'attribute_' . sanitize_title( $attr_name ),
			'label'   => wc_attribute_label( $attr_name, $product ),
			'options' => $options,
		);
	}

	foreach ( $product->get_children() as $variation_id ) {
		$variation = wc_get_product( $variation_id );
		if ( ! $variation || ! $variation->exists() || 'publish' !== $variation->get_status() ) {
			continue;
		}
		$attrs = array();
		foreach ( $variation->get_attributes() as $key => $value ) {
			$attrs[ 'attribute_' . $key ] = sanitize_title( (string) $value );
		}
		$variations[] = array(
			'id'    => $variation_id,
			'attrs' => $attrs,
			'stock' => $variation->is_in_stock() && $variation->is_purchasable(),
		);
	}
}

// Sizes can be picked several at a time; any other option (Color) is one choice.
$multi = 0;
foreach ( $groups as $i => $group ) {
	if ( false !== strpos( $group['key'], 'size' ) ) {
		$multi = $i;
		break;
	}
}

$can_buy = $product->is_purchasable() && $product->is_in_stock();
if ( $product->is_type( 'variable' ) ) {
	$can_buy = $can_buy && $groups && wp_list_filter( $variations, array( 'stock' => true ) );
}

// An option is greyed out when no in-stock variation offers it.
$offered = function ( $key, $value ) use ( $variations ) {
	foreach ( $variations as $variation ) {
		$v = $variation['attrs'][ $key ] ?? '';
		if ( $variation['stock'] && ( '' === $v || $value === $v ) ) {
			return true;
		}
	}
	return false;
};

$description = $product->get_description() ?: $product->get_short_description();
$data        = array( 'id' => $pid, 'type' => $product->is_type( 'variable' ) ? 'variable' : 'simple', 'vars' => $variations );
?>
<template id="bh-product-<?php echo (int) $pid; ?>" data-url="<?php echo esc_url( $product->get_permalink() ); ?>" data-title="<?php echo esc_attr( $name ); ?>">
	<div class="bh-pd" data-vars="<?php echo esc_attr( wp_json_encode( $data ) ); ?>">
		<button type="button" class="bh-pd-close" data-bh-close><?php esc_html_e( 'Close', 'bushido-us' ); ?></button>

		<div class="bh-pd-media">
			<div class="bh-pd-stage">
				<img class="bh-pd-main" src="<?php echo esc_url( $images[0]['src'] ); ?>"<?php if ( $images[0]['srcset'] ) : ?> srcset="<?php echo esc_attr( $images[0]['srcset'] ); ?>" sizes="(max-width: 760px) 92vw, 540px"<?php endif; ?> alt="<?php echo esc_attr( $images[0]['alt'] ); ?>">
			</div>
			<?php if ( count( $images ) > 1 ) : ?>
				<div class="bh-pd-nav">
					<button type="button" class="bh-pd-arrow" data-bh-step="-1" aria-label="<?php esc_attr_e( 'Previous image', 'bushido-us' ); ?>">&lt;</button>
					<span class="bh-pd-count"><span data-bh-index>1</span> / <?php echo count( $images ); ?></span>
					<button type="button" class="bh-pd-arrow" data-bh-step="1" aria-label="<?php esc_attr_e( 'Next image', 'bushido-us' ); ?>">&gt;</button>
				</div>
				<ul class="bh-pd-thumbs">
					<?php foreach ( $images as $i => $image ) : ?>
						<li>
							<button type="button" data-bh-image="<?php echo (int) $i; ?>" data-src="<?php echo esc_url( $image['src'] ); ?>" data-srcset="<?php echo esc_attr( $image['srcset'] ); ?>" data-alt="<?php echo esc_attr( $image['alt'] ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Image %d', 'bushido-us' ), $i + 1 ) ); ?>" aria-current="<?php echo 0 === $i ? 'true' : 'false'; ?>">
								<img src="<?php echo esc_url( $image['thumb'] ); ?>" alt="" loading="lazy">
							</button>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="bh-pd-info">
			<h2 class="bh-pd-name" id="bh-pd-title-<?php echo (int) $pid; ?>"><?php echo esc_html( $name ); ?></h2>
			<p class="bh-pd-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
			<?php if ( $description ) : ?>
				<div class="bh-pd-desc"><?php echo wp_kses_post( wpautop( $description ) ); ?></div>
			<?php endif; ?>

			<?php if ( $can_buy ) : ?>
				<?php foreach ( $groups as $i => $group ) : ?>
					<div class="bh-pd-opts" role="group" aria-label="<?php echo esc_attr( $group['label'] ); ?>" data-key="<?php echo esc_attr( $group['key'] ); ?>" data-label="<?php echo esc_attr( $group['label'] ); ?>" data-multi="<?php echo $i === $multi ? '1' : '0'; ?>">
						<span class="bh-pd-label"><?php echo esc_html( $group['label'] ); ?></span>
						<?php foreach ( $group['options'] as $option ) : ?>
							<button type="button" class="bh-pd-opt" data-v="<?php echo esc_attr( $option[0] ); ?>" aria-pressed="false"<?php disabled( ! $offered( $group['key'], $option[0] ) ); ?>><?php echo esc_html( $option[1] ); ?></button>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
				<button type="button" class="bh-pd-add" data-bh-add<?php disabled( (bool) $groups ); ?>>
					<?php echo $groups ? esc_html( sprintf( __( 'Select a %s', 'bushido-us' ), strtolower( $groups[ $multi ]['label'] ) ) ) : esc_html__( 'Add to bag', 'bushido-us' ); ?>
				</button>
				<p class="bh-pd-status" role="status"></p>
			<?php else : ?>
				<p class="bh-pd-sold"><?php esc_html_e( 'Sold out', 'bushido-us' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</template>
