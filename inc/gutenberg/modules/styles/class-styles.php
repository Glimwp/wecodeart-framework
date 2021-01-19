<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg CSS Module
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		4.0.3
 * @version		4.2.0
 */

namespace WeCodeArt\Gutenberg\Modules;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg;
use WeCodeArt\Core\Scripts;

/**
 * Handles Gutenberg Theme CSS Functionality.
 */
class Styles {

	use Singleton;

	/**
	 * The Styles Processor
	 *
	 * @access 	public
	 * @var 	null|object
	 */
	public $styles = null;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	public function init() {
		// Bail if styles are disabled
		if( ! wecodeart( 'integrations' )->has( 'styles' ) ) return;

		$this->styles = wecodeart( 'integrations' )->get( 'styles' );

		// CSS Handler
		Styles\Handler::get_instance();
		// Enqueue Styles
		Styles\Embed::get_instance();
	}

	/**
	 * Get Blocks CSS
	 *
	 * @since   4.2.0
	 * @param 	int 	$post_id Post id.
	 *
	 * @return 	string
	 */
	public function get_blocks_css( $post_id ) {
		$content = get_post_field( 'post_content', $post_id );
		$blocks  = Gutenberg::parse_blocks( $content );

		if ( ! is_array( $blocks ) || empty( $blocks ) ) {
			return;
		}

		return $this->cycle_through_static_blocks( $blocks );
	}

	/**
	 * Get Reusable Blocks CSS
	 *
	 * @since   4.2.0
	 * @param 	int 	$post_id Post id.
	 *
	 * @return 	string
	 */
	public function get_reusable_block_css( $block_id ) {
		$reusable_block = get_post( $block_id );

		if ( ! $reusable_block || 'wp_block' !== $reusable_block->post_type ) {
			return;
		}

		if ( 'publish' !== $reusable_block->post_status || ! empty( $reusable_block->post_password ) ) {
			return;
		}

		$blocks = Gutenberg::parse_blocks( $reusable_block->post_content );

		return $this->cycle_through_static_blocks( $blocks );
	}

	/**
	 * Cycle thorugh Static Blocks
	 *
	 * @since   4.2.0
	 * @param 	array 	$blocks List of blocks.
	 *
	 * @return 	string 	Style.
	 */
	public function cycle_through_static_blocks( $blocks ) {
		$style = '';
		foreach ( $blocks as $block ) {
			$style .= $this->styles::parse( $this->styles::add_prefixes( self::process_block( $block ) ) );

			if ( isset( $block['innerBlocks'] ) && ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$style .= $this->cycle_through_static_blocks( $block['innerBlocks'] );
			}
		}

		return $style;
	}

	/**
	 * Cycle thorugh Reusable Blocks
	 *
	 * @since   4.2.0
	 * @param 	array 	$blocks List of blocks.
	 *
	 * @return 	string 	Style.
	 */
	public function cycle_through_reusable_blocks( $blocks ) {
		$style = '';
		foreach ( $blocks as $block ) {
			if ( 'core/block' === $block['blockName'] && ! empty( $block['attrs']['ref'] ) ) {
				$style .= $this->get_reusable_block_css( $block['attrs']['ref'] );
			}

			if ( isset( $block['innerBlocks'] ) && ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$style .= $this->cycle_through_reusable_blocks( $block['innerBlocks'] );
			}
		}

		return $style;
	}

	/**
	 * Get the CSS for a block.
	 *
	 * @param 	array 	$args The block.
	 *
	 * @return 	array
	 */
	public static function process_block( $block = [] ) {
		// Find the class that will handle the output for this block.
		$classname	= Styles\Blocks::class;
		$defaults   = [
			'core/cover' 		=> Styles\Blocks\Cover::class,
			'core/media-text' 	=> Styles\Blocks\Media::class
		];

		$output_classes = apply_filters( 'wecodeart/filter/gutenberg/styles/blocks', $defaults );

		if ( array_key_exists( $block['blockName'], $output_classes ) ) {
			$classname = $output_classes[ $block['blockName'] ];
		}
		
		if( class_exists( $classname ) ) {
			$obj = new $classname( $block );
	
			return $obj->get_styles();
		};
	}
}
