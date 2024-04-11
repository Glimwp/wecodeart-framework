<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg\Blocks
 * @copyright   Copyright (c) 2024, WeCodeArt Framework
 * @since		5.0.0
 * @version		6.0.0
 */

namespace WeCodeArt\Gutenberg\Blocks\Post;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Entry Title block.
 */
class Title extends Dynamic {

	use Singleton;

	/**
	 * Block namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'core';

	/**
	 * Block name.
	 *
	 * @var string
	 */
	protected $block_name = 'post-title';

	/**
	 * Block args.
	 *
	 * @return 	array
	 */
	public function block_type_args(): array {
		return [
			'render_callback' => [ $this, 'render' ]
		];
	}

	/**
	 * Dynamically renders the `core/post-title` block.
	 *
	 * @param 	array 	$attributes	The attributes.
	 * @param 	string 	$content 	The block markup.
	 * @param 	string 	$block 		The block data.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( array $attributes = [], string $content = '', $block = null ): string {
		if ( ! isset( $block->context['postId'] ) ) {
			return '';
		}

		$tag_name	= 'h2';
		if ( $value = get_prop( $attributes, 'level', false ) ) {
			$tag_name = 0 === $value ? 'p' : 'h' . $value;
		}

		$classnames = [];

		// Fix Post Title text align
		if( $value = get_prop( $attributes, 'textAlign', false ) ) {
			$classnames[] = 'has-text-align-' . $value;
		}

		$wrappers = [
			[
				'tag' 	=> $tag_name,
				'attrs' => $this->get_block_wrapper_attributes( [
					'class' => join( ' ', $classnames )
				] )
			]
		];

		if( get_prop( $attributes, 'isLink', false ) ) {
			$wrappers[] = [
				'tag' 	=> 'a',
				'attrs' => [
					'class'		=> 'wp-block-post-title__link',
					'target' 	=> get_prop( $attributes, 'linkTarget' ),
					'href'		=> get_permalink( $block->context['postId'] )
				]
			];
		}

		return wecodeart( 'dom' )::wrap( 'wp-block-post-title', $wrappers, function( $id ) {
			echo get_the_title( $id );
		}, [ $block->context['postId'] ], false );
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		return <<<CSS
			.wp-block-post-title {
				word-break: break-word;
			}
			.wp-block-post-title a {
				display: inline-block;
			}
		CSS;
	}
}
