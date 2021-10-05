<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Header Class
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		3.5
 * @version		5.0.7
 */

namespace WeCodeArt\Core;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Markup;
use WeCodeArt\Singleton;
use WeCodeArt\Support\Styles;
use function WeCodeArt\Functions\get_prop;

/**
 * Framework Header
 */
class Header {

	use Singleton;

	/**
	 * Send to Constructor
	 */
	public function init() {
		add_action( 'wecodeart/header',	[ __CLASS__, 'markup' ] );
		add_action( 'init', 			[ $this, 'clean_head' ] );
	}
	
	/**
	 * Output HEADER markup function
	 * Plugin PHP fallback
	 *
	 * @uses	WeCodeArt\Markup::wrap()
	 * @since 	unknown
	 * @version	5.0.0
	 *
	 * @return 	void 
	 */
	public static function markup( $args = [] ) {
		$args 	= wp_parse_args( $args, [
			'theme' 	=> wecodeart( 'name' ),
			'slug' 		=> 'header',
			'tagName' 	=> 'header',
		] );

		Markup::wrap( 'header', [
			[
				'tag' 	=> $args['tagName'],
				'attrs' => [
					'id'	=> $args['slug'],
					'class'	=> 'site-header sticky-top wp-block-template-part',
				]
			], 
			[ 
				'tag' 	=> 'div',
				'attrs' => [
					'class'	=> 'container',
				] 
			]
		], 'gutenberg_block_header_area' );
	}

	/**
	 * Clean WP_Head of unwanted of stuff
	 *
	 * This approach tricks the theme check plugin in WP.org but...
	 * I'm aware about plugin teritoriality so I will keep this disabled
	 * However, user can use this filter to reduce <head> bloat to minimum
	 *
	 * @return void
	 */
	public function clean_head() {
		if( apply_filters( 'wecodeart/filter/head/clean', false ) === false ) return;

		$actions = apply_filters( 'wecodeart/filter/head/clean/actions', [
			[ 'wp_head', 'wp_generator' ],
			[ 'wp_head', 'rsd_link' ],
			[ 'wp_head', 'feed_links', 2 ],
			[ 'wp_head', 'feed_links_extra', 3 ],
			[ 'wp_head', 'wlwmanifest_link' ],
			[ 'wp_head', 'index_rel_link' ],
			[ 'wp_head', 'parent_post_rel_link', 10, 0 ],
			[ 'wp_head', 'start_post_rel_link', 10, 0 ],
			[ 'wp_head', 'adjacent_posts_rel_link', 10, 0 ],
			[ 'wp_head', 'rest_output_link_wp_head' ],
			[ 'wp_head', 'wp_shortlink_wp_head' ],
			[ 'wp_head', 'wp_oembed_add_discovery_links' ],
			[ 'wp_head', 'print_emoji_detection_script', 7 ],
			[ 'wp_print_styles', 'print_emoji_styles', 7 ],
			[ 'template_redirect', 'rest_output_link_header', 11 ],
		] );

		if( empty( $actions ) ) return;
		
		foreach( $actions as $args ) call_user_func( 'remove_action', ...$args );
	}
}