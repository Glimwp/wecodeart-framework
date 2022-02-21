<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg\Blocks
 * @copyright   Copyright (c) 2022, WeCodeArt Framework
 * @since		5.5.1
 * @version		5.5.1
 */

namespace WeCodeArt\Gutenberg\Blocks\Widgets;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Cloud block.
 */
class Cloud extends Dynamic {

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
	protected $block_name = 'tag-cloud';

	/**
	 * Shortcircuit Register
	 */
	public function register() {}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public static function styles() {
		return "
		.wp-block-tag-cloud.aligncenter {
			text-align: center;
		}
	
		.wp-block-tag-cloud.alignfull {
			padding-left: 1em;
			padding-right: 1em;
		}
	
		.wp-block-tag-cloud a {
			display: inline-block;
			margin-right: 5px;
		}
	
		.wp-block-tag-cloud span {
			display: inline-block;
			margin-left: 5px;
			text-decoration: none;
		}
	
		.wp-block-tag-cloud.is-style-outline a {
			border: 1px solid currentColor;
			font-size: unset !important;
			margin-bottom: 1ch;
			margin-right: 1ch;
			padding: 1ch 2ch;
			text-decoration: none !important;
		}
		";
	}
}
