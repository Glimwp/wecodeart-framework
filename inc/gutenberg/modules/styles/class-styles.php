<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg CSS Module
 * @copyright   Copyright (c) 2024, WeCodeArt Framework
 * @since		4.0.3
 * @version		6.3.3
 */

namespace WeCodeArt\Gutenberg\Modules;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Integration;
use WeCodeArt\Gutenberg;
use WeCodeArt\Config\Traits\Asset;
use function WeCodeArt\Functions\get_prop;
use function WeCodeArt\Functions\get_json_color;

/**
 * Handles Gutenberg Theme CSS Functionality.
 */
class Styles implements Integration {

	use Singleton;
	use Asset;

	/**
	 * The CSS context for style engine.
	 *
	 * @var string
	 */
	const CONTEXT 	= 'block-supports';

	/**
	 * The blocks duotone
	 *
	 * @access 	public
	 * @var 	array
	 */
	public $filters	= [];

	/**
	 * Get Conditionals
	 *
	 * @return void
	 */
	public static function get_conditionals() {
		wecodeart( 'ifso' )->set( 'with_blocks_styles', Styles\Condition::class );

		return [ 'with_blocks_styles' ];
	}

	/**
	 * Register Hooks - into styles processor action if enabled
	 *
	 * @since 	5.0.0
	 *
	 * @return 	void
	 */
	public function register_hooks() {
		// Custom Style Attributes support
		\WP_Block_Supports::get_instance()->register( 'customStyle', [
			'register_attribute' 	=> [ $this, 'register_attribute' ],
		] );

		// Hooks
		add_filter( 'should_load_separate_core_block_assets', '__return_true', PHP_INT_MAX );
		add_action( 'enqueue_block_editor_assets',	[ $this, 'block_editor_assets' 	], 20, 1 );
		add_filter( 'render_block',					[ $this, 'filter_render' 		], 20, 2 );
		add_filter( 'wp_theme_json_data_theme',  	[ $this, 'theme_json'			], 20, 1 );
		
		// Eventually it will be removed - 1 check since they are all from GB.
		if( function_exists( 'gutenberg_render_duotone_support' ) ) {
			// Gutenberg plugin removes styles and adds them in footer - we dont want that.
			add_action( 'wp_enqueue_scripts', 'wp_enqueue_stored_styles', PHP_INT_MAX ); // Temporary
			add_action( 'wp_footer', 'wp_enqueue_stored_styles', 1 ); // Temporary.
		}
	}

	/**
	 * Editor only.
	 *
	 * @return  void
	 */
	public function block_editor_assets() {
		// Javascript
		wp_enqueue_script( $this->make_handle(), $this->get_asset( 'js', 'gutenberg/ext-styles' ), [
			'wecodeart-gutenberg-inline'
		], wecodeart( 'version' ) );
	}

	/**
	 * Link hover brightness.
	 *
	 * @param	object  WP_Theme_JSON object
	 *
	 * @return 	object
	 */
	public function theme_json( object $object ): object {
		$link 		= get_json_color( [ 'styles', 'elements', 'link', 'color', 'text' ] );

		if( $link ) {
			$data = $object->get_data();

			// Adjust hover brightness.
			$data['styles']['elements']['link'][':hover'] = wp_parse_args( [
				'color' => [
					'text' => wecodeart( 'styles' )::hex_brightness( $link, -25 )
				]
			], get_prop( $data, [ 'styles', 'elements', 'link', ':hover' ], [] ) );

			// Update object.
			$object->update_with( $data );
		}

		return $object;
	}

	/**
	 * Adds the `customStyle` attributes to all blocks, to avoid `Invalid parameter(s): attributes` error.
	 *
	 * @since   5.2.x
	 *
	 * @return 	void
	 */
	public function register_attribute( $block ) {
		$has_support = block_has_support( $block, '__experimentalStyles', false );

		if ( ! $has_support ) {
			return;
		}
		
		if ( ! $block->attributes ) {
			$block->attributes = [];
		}

		$block->attributes['customStyle'] = [
			'type'    => 'string',
			'default' => null,
		];
	}

	/**
	 * Filter render_block
	 *
	 * @param	string 	$block_content
	 * @param	array 	$block
	 *
	 * @return 	string 	HTML
	 */
	public function filter_render( $content, $block ) {
		$block_name	= get_prop( $block, 'blockName' );

		// Skip nulls
		if( ! $block_name ) {
			return $content;
		}

		$block_type		= \WP_Block_Type_Registry::get_instance()->get_registered( $block_name );
		$has_support 	= block_has_support( $block_type, '__experimentalStyles', false );

		// To be implemented when we have dimensions / other styles
		// $has_styles_1	= get_prop( $block, [ 'attrs', 'style' ] );
		// $has_styles_2	= get_prop( $block, [ 'attrs', 'customStyle' ] );

		// Remove styles, where needed.
		if ( $has_support ) {
			// Process a block
			$processed 	= self::process_block( $block );
			$block_id	= $processed->get_id();
			$content 	= new \WP_HTML_Tag_Processor( $content );

			$content->next_tag();

			// Inject class name to block tag.
			$content->add_class( $block_id );
			
			// Remove inline style attribute.
			$content->remove_attribute( 'style' );
			
			// Remove inline style attribute for extra wrappers.
			if( ! empty( $selectors = $processed->remove_style() ) ) {				
				foreach( $selectors as $selector ) {
					if( $content->next_tag( $selector ) ) {
						$content->remove_attribute( 'style' );
					}
				}
			}
		}

		return (string) $content;
	}

	/**
	 * Process a block.
	 *
	 * @param 	array 	$block 	The block data.
	 *
	 * @return 	object
	 */
	public static function process_block( array $block = [] ) {
		// Find the class that will handle the output for this block.
		$classname	= Styles\Processor::class;
		$defaults   = [
			'core/button' 			=> Styles\Blocks\Button::class,
			'core/column' 			=> Styles\Blocks\Column::class,
			'core/cover' 			=> Styles\Blocks\Cover::class,
			'core/table' 			=> Styles\Blocks\Table::class,
			'core/quote' 			=> Styles\Blocks\Quote::class,
			'core/group' 			=> Styles\Blocks\Group::class,
			'core/gallery' 			=> Styles\Blocks\Gallery::class,
			'core/media-text' 		=> Styles\Blocks\Media::class,
			'core/navigation' 		=> Styles\Blocks\Navigation::class,
			'core/search' 			=> Styles\Blocks\Search::class,
			'core/separator' 		=> Styles\Blocks\Separator::class,
			'core/social-links'		=> Styles\Blocks\Social::class,
			'core/image' 			=> Styles\Blocks\Image::class,
			'core/spacer' 			=> Styles\Blocks\Spacer::class,
			'core/post-template'	=> Styles\Blocks\Template::class,
			'core/post-featured-image'	=> Styles\Blocks\Featured::class,
		];

		$output_classes = apply_filters( 'wecodeart/filter/gutenberg/styles/processor', $defaults );

		if ( array_key_exists( $block['blockName'], $output_classes ) ) {
			$classname = $output_classes[ $block['blockName'] ];
		}
		
		if( class_exists( $classname ) ) {
			return ( new $classname( $block ) );
		};
	}
}