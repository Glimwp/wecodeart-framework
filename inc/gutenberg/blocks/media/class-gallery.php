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
 * @version		6.2.3
 */

namespace WeCodeArt\Gutenberg\Blocks\Media;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Gallery block.
 */
class Gallery extends Dynamic {

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
	protected $block_name = 'gallery';

	/**
	 * Block args.
	 *
	 * @return 	array
	 */
	public function block_type_args( array $current ): array {
		$supports 	= get_prop( $current, [ 'supports' ], [] );

		return [
			'render_callback' => [ $this, 'render' ],
			'supports'		=> wp_parse_args( [
				'spacing'	=> [
					'margin'  	=> true,
					'padding' 	=> true,
					'blockGap' 	=> [
						'sides' => [ 'vertical', 'horizontal' ]
					],
				]
			], $supports )
		];
	}

	/**
	 * Dynamically renders the `core/gallery` block.
	 *
	 * @param 	array 	$attributes	The attributes.
	 * @param 	string 	$content 	The block markup.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( array $attributes = [], string $content = '' ): string {
		$columns = get_prop( $attributes, 'columns', 'default' );

		return preg_replace( '/(columns-)\w+/', 'grid columns-' . $columns, $content, 1 );
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		return <<<CSS
			.wp-block-gallery {
				max-width: initial;
			}
			.wp-block-gallery.alignleft,
			.wp-block-gallery.alignright {
				width: 100%;
			}
			.wp-block-gallery.aligncenter figure.wp-block-image {
				justify-content: center;
			}
			.wp-block-gallery:not(.is-cropped) figure.wp-block-image {
				margin-top: 0;
				margin-bottom: auto;
			}
			.wp-block-gallery.is-cropped figure.wp-block-image > a {
				display: flex;
			}
			.wp-block-gallery.is-cropped figure.wp-block-image :where(a,img,svg) {
				flex: 1 0 0%;
				width: 100%;
				height: 100%;
				object-fit: cover;
			}
			.wp-block-gallery figure.wp-block-image.is-style-rounded figcaption {
				position: relative;
				background: none;
				color: inherit;
				margin: 0;
			}
			.wp-block-gallery figure.wp-block-image {
				position: relative;
				display: flex;
				justify-content: center;
				flex-direction: column;
				flex-grow: 1;
				margin: 0;
				max-width: 100%;
			}
			.wp-block-gallery figure.wp-block-image > a {
				flex-direction: column;
				margin: 0;
			}
			.wp-block-gallery figure.wp-block-image img {
				display: block;
				max-width: 100%;
			}
			.wp-block-gallery figure.wp-block-image .wp-element-caption {
				position: absolute;
				width: 100%;
				bottom: 0;
				max-height: 100%;
				overflow: auto;
				padding: 1rem;
				color: var(--wp--preset--color--white);
				text-align: center;
				border-radius: inherit;
				background: linear-gradient(0deg, rgba(0, 0, 0, 0.7) 0, rgba(0, 0, 0, 0.3) 70%, transparent);
			}
			.wp-block-gallery > figcaption {
				grid-column: auto/span var(--wp--columns);
				text-align: center;
			}
		CSS;
	}
}
