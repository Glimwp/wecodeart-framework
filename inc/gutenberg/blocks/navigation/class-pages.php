<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg\Blocks
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		5.3.7
 * @version		5.3.7
 */

namespace WeCodeArt\Gutenberg\Blocks\Navigation;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Navigation Pages block.
 */
class Pages extends Dynamic {

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
	protected $block_name = 'page-list';

	/**
	 * Shortcircuit Register
	 */
	public function register() {
		add_filter( 'render_block_core/' . $this->block_name,	[ $this, 'render'	], 10, 2 );
	}

	/**
	 * Dynamically renders the `core/page-list` block.
	 *
	 * @param 	string 	$content 	The block markup.
	 * @param 	array 	$block 		The parsed block.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $content = '', $block = [], $data = null ) {
		
		$content = str_replace( 'wp-block-page-list', 'wp-block-page-list nav', $content );
		$content = str_replace( 'wp-block-navigation-item', 'wp-block-navigation-item nav-item', $content );
		$content = str_replace( 'wp-block-pages-list__item__link', 'wp-block-pages-list__item__link nav-link', $content );

		return $content;
	}
}