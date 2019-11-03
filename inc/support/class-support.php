<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage  Theme Support
 * @copyright   Copyright (c) 2019, WeCodeArt Framework
 * @since		3.5
 * @version		3.9.7
 */

namespace WeCodeArt;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Support\ANR;
use WeCodeArt\Support\WooCommerce;
use WeCodeArt\Support\WPSeo;
use WeCodeArt\Utilities\Helpers;

/**
 * Support for various plugins and features.
 */
class Support {

	use Singleton; 

	/**
	 * Send to Constructor
	 * @since 3.6.2
	 */
	public function init() {
		
		// Theme Support
		add_action( 'after_setup_theme', [ $this, 'after_setup_theme'	], 2 );
		add_action( 'after_setup_theme', [ $this, 'register_menus' 		], 10 );

		// Integrations/Compatability/Plugin Support
		if( Helpers::detect_plugin( [ 'classes'		=> [ 'woocommerce' ] ] ) )			WooCommerce::get_instance();
		if( Helpers::detect_plugin( [ 'constants' 	=> [ 'WPSEO_VERSION' ] ] ) ) 		WPSeo::get_instance();
		if( Helpers::detect_plugin( [ 'constants' 	=> [ 'ANR_PLUGIN_VERSION' ] ] ) ) 	ANR::get_instance();
	}

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * @since 	1.0
	 * @version	3.9.7
	 */
	function after_setup_theme() {
		// Add support for Meta info for posts other than Page Type 
		foreach( wecodeart( 'public_post_types' ) as $type ) { 
			add_post_type_support( $type, 'wecodeart-post-info' );
		}

		// Content width
		$GLOBALS['content_width'] = apply_filters( 'wecodeart/filter/content_width', 1280 );

		// Translation
		load_theme_textdomain( 
			wecodeart_config( 'textdomain' ), 
			wecodeart_config( 'paths' )['uri'] . '/' . wecodeart_config( 'directories' )['languages'] 
		);

		// Meta Modules
		add_theme_support( 'meta-modules', [ 'author', 'date', 'categories', 'tags', 'comments', 'edit' ] );

		// Automatic Feed Links
		add_theme_support( 'automatic-feed-links' );

		// Title Tag
		add_theme_support( 'title-tag' );

		// Selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		// Thumbnails
		add_theme_support( 'post-thumbnails' );

		// Add support for Block Styles.
		add_theme_support( 'wp-block-styles' );

		// Add support for full and wide align images.
		add_theme_support( 'align-wide' );

		// Editor Style
		add_theme_support( 'editor-style' );

		// Custom Logo
		add_theme_support( 'custom-logo', [
			'height'      => 50,
			'width'       => 100,
		] );

		// HTML5
		add_theme_support( 'html5', [ 'gallery', 'caption' ] );

		// Responsive Embeds
		add_theme_support( 'responsive-embeds' );

		// AMP Support
		add_theme_support( 'amp', apply_filters( 'wecodeart/filter/support/amp', [ 
			'paired' => true 
		] ) );

		// Add custom editor font sizes.
		add_theme_support(
			'editor-font-sizes',
			array(
				array(
					'name'      => esc_html__( 'Small', wecodeart_config( 'textdomain' ) ),
					'shortName' => esc_html__( 'S', wecodeart_config( 'textdomain' ) ),
					'size'      => 14,
					'slug'      => 'small',
				),
				array(
					'name'      => esc_html__( 'Normal', wecodeart_config( 'textdomain' ) ),
					'shortName' => esc_html__( 'M', wecodeart_config( 'textdomain' ) ),
					'size'      => 16,
					'slug'      => 'normal',
				),

				array(
					'name'      => esc_html__( 'Large', wecodeart_config( 'textdomain' ) ),
					'shortName' => esc_html__( 'L', wecodeart_config( 'textdomain' ) ),
					'size'      => 26,
					'slug'      => 'large',
				),
				array(
					'name'      => esc_html__( 'Huge', wecodeart_config( 'textdomain' ) ),
					'shortName' => esc_html__( 'XL', wecodeart_config( 'textdomain' ) ),
					'size'      => 49.5,
					'slug'      => 'huge',
				),
			)
		);

		// Editor color palette.
		$wecodeart_colors = apply_filters( 'wecodeart/filter/support/editor-color-palette', [
			'primary' 	=> [ 'color' => '#2388ed', 'label' => 'Primary' 	],
			'secondary' => [ 'color' => '#6c757d', 'label' => 'Secondary' 	],
			'danger' 	=> [ 'color' => '#dc3545', 'label' => 'Danger' 		],
			'success' 	=> [ 'color' => '#7dc855', 'label' => 'Success' 	],
			'info' 		=> [ 'color' => '#17a2b8', 'label' => 'Info' 		],
			'warning' 	=> [ 'color' => '#ffc107', 'label' => 'Warning'		],
			'dark' 		=> [ 'color' => '#343a40', 'label' => 'Dark' 		],
			'light' 	=> [ 'color' => '#f1f3f7', 'label' => 'Light' 		],
			'white' 	=> [ 'color' => '#ffffff', 'label' => 'White' 		],
		] );

		$colors = [];
		foreach( $wecodeart_colors as $slug => $color ) {
			$colors[] = [
				'name' 	=> esc_html__( $color['label'], wecodeart_config( 'textdomain' ) ),
				'slug'	=> esc_attr( strtolower( $slug ) ),
				'color'	=> sanitize_hex_color( $color['color'] ),
			];
		}

		add_theme_support( 'editor-color-palette', $colors );
	}

	/**
	 * Register Menus
	 *
	 * @since	3.9.5
	 * @version	3.9.9
	 *
	 * @return 	void
	 */
	public function register_menus() {
		$header_modules = get_theme_mod( 'header-bar-modules', [] );

		if( in_array( 'menu', $header_modules ) ) {
			// Register New Menu
			register_nav_menus( [ 
				'primary' => esc_html__( 'Primary Menu', wecodeart_config( 'textdomain' ) ) 
			] );
		}
	}
}