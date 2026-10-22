<?php
/**
 * Plugin Name: Bushido US redirect
 * Description: Sends visitors in the United States to the US store (us.bushidonepal.com) with a temporary redirect. Products go to the same product there, everything else to its home page. Search engines, link-preview bots and logged-in staff are never redirected.
 * Version: 1.0.0
 * Author: Saurav Dhakal
 *
 * Install as a must-use plugin on bushidonepal.com: wp-content/mu-plugins/bushido-us-redirect.php
 * Country comes from WooCommerce's geolocation, which caches each visitor's lookup for a day.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const BUSHIDO_US_STORE = 'https://us.bushidonepal.com';

/* Crawlers and link previews always see the Nepal site, so it stays in search results and shared links keep their previews. */
function bushido_us_redirect_is_bot() {
	$agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
	if ( '' === $agent ) {
		return true;
	}
	return (bool) preg_match( '/bot|crawl|spider|slurp|google|bing|yandex|baidu|duckduck|facebookexternalhit|facebot|twitterbot|linkedin|whatsapp|telegram|slack|discord|pinterest|embedly|preview|lighthouse|pagespeed|headless|monitor|uptime|ahrefs|semrush|petalbot|applebot/i', $agent );
}

add_action( 'template_redirect', function () {
	// Only ever runs on the Nepal site: on the US store itself this would loop.
	if ( wp_parse_url( home_url(), PHP_URL_HOST ) === wp_parse_url( BUSHIDO_US_STORE, PHP_URL_HOST ) ) {
		return;
	}
	if ( is_robots() || is_feed() || get_query_var( 'sitemap' ) || is_preview() || is_customize_preview() ) {
		return;
	}
	if ( current_user_can( 'edit_posts' ) || bushido_us_redirect_is_bot() || ! class_exists( 'WC_Geolocation' ) ) {
		return;
	}

	$location = WC_Geolocation::geolocate_ip( '', true, true );
	if ( 'US' !== ( $location['country'] ?? '' ) ) {
		return;
	}

	$target = BUSHIDO_US_STORE . '/';
	if ( is_singular( 'product' ) ) {
		$target = BUSHIDO_US_STORE . '/product/' . get_post_field( 'post_name', get_queried_object_id() ) . '/';
	}

	nocache_headers();
	header( 'Vary: User-Agent' );
	wp_redirect( $target, 302, 'Bushido US redirect' );
	exit;
}, 1 );
