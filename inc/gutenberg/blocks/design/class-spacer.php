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

namespace WeCodeArt\Gutenberg\Blocks\Design;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Spacer block.
 */
class Spacer extends Dynamic {

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
	protected $block_name = 'spacer';

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		$breaks 	= wecodeart_json( [ 'settings', 'custom', 'breakpoints' ], [] );
		$tablet		= get_prop( $breaks, 'md', '768px' );
		$desktop	= get_prop( $breaks, 'lg', '992px' );

		return "
			.wp-block-spacer {
				height: calc(var(--wp--spacer-height, 100px) * var(--wp--spacer-mobile, .5));
				width: var(--wp--spacer-width, 100%);
				clear: both;
			}
			.wp-block-navigation .wp-block-spacer {
				width: 100%;
				height: var(--wp--spacer-width, 1px);
				min-height: initial;
			}
			@media (min-width: $tablet) {
				.wp-block-spacer {
					height: calc(var(--wp--spacer-height, 100px) * var(--wp--spacer-tablet, .7));
				}
			}
			@media (min-width: $desktop) {
				.wp-block-spacer {
					height: var(--wp--spacer-height, 100px);
				}
			}
		";
	}
}
